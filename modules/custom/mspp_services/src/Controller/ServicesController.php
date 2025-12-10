<?php

namespace Drupal\mspp_services\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormState;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the Services page.
 */
class ServicesController extends ControllerBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  public function content() {

    // -------------------------
    // 1. Handle GET parameters safely
    // -------------------------
    $request = \Drupal::request()->query;


    \Drupal::logger('mspp_services_debug')->notice(
      'RAW QUERY: <pre>@data</pre>',
      ['@data' => print_r($request->all(), TRUE)]
    );
        

    $raw_search = $request->get('search', '');
    $search = is_array($raw_search) ? '' : trim($raw_search);
    if ($search !== '') {
      $search = mb_strtolower($search);
    } else {
      $search = NULL;
    }

    $query_all = $request->all();
    $raw_categories = isset($query_all['categories']) ? $query_all['categories'] : [];
    $selected_categories = is_array($raw_categories) ? array_filter($raw_categories) : [];
    $selected_categories = array_map('intval', $selected_categories);

    // -------------------------
    // 2. Load taxonomy terms
    // -------------------------
    $categories = [];
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'service_categories']);

    foreach ($terms as $term) {
      $categories[] = [
        'tid' => (int) $term->id(),
        'name' => $term->label(),
      ];
    }

    // -------------------------
    // 3. Build node query
    // -------------------------
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'services')
      ->condition('status', 1)
      ->sort('title', 'ASC')
      ->accessCheck(TRUE);

    if (!empty($selected_categories)) {
      $query->condition('field_service_category', $selected_categories, 'IN');
    }

    $nids = $query->execute();
    $services = [];

    // -------------------------
    // 4. Prepare service data
    // -------------------------
    if ($nids) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $node) {

        // Search filter
        if ($search !== NULL) {
          $title = mb_strtolower($node->getTitle());
          $desc_field = $node->get('field_description_courte');
          $description = $desc_field && !$desc_field->isEmpty() ? mb_strtolower($desc_field->value) : '';

          if (!str_contains($title, $search) && !str_contains($description, $search)) {
            continue;
          }
        }

        // Node categories
        $categories_for_node = [];
        if ($node->hasField('field_service_category')) {
          foreach ($node->get('field_service_category') as $ref) {
            if ($term = $ref->entity) {
              $categories_for_node[] = [
                'tid' => (int) $term->id(),
                'name' => $term->label(),
              ];
            }
          }
        }

        // Link field clean extraction
        $link = '';
        if ($node->hasField('field_lien') && !$node->get('field_lien')->isEmpty()) {
          try {
            $link = $node->get('field_lien')->first()->getUrl()->toString();
          } catch (\Exception $e) {
            $raw = $node->get('field_lien')->first()->getValue();
            $link = $raw['uri'] ?? '';
            if (str_starts_with($link, 'internal:')) {
              $link = substr($link, 9);
            }
          }
        }

        // Icon Media Reference
        $icon = NULL;
        if ($node->hasField('field_icon') && !$node->get('field_icon')->isEmpty()) {
          $media = $node->get('field_icon')->entity;
          if ($media) {
            $file = $media->get('field_media_file')->entity;
            if ($file) {
              $icon = [
                'uri' => $file->getFileUri(),
                'url' => $file->createFileUrl(),
                'svg' => file_get_contents($file->getFileUri())
              ];
            }
          }
        }

        // Description
        $short = '';
        if ($node->hasField('field_description_courte') && !$node->get('field_description_courte')->isEmpty()) {
          $short = $node->get('field_description_courte')->value;
        }

        $services[] = [
          'nid' => $node->id(),
          'title' => $node->getTitle(),
          'field_lien' => $link,
          'field_description_courte' => $short,
          'field_icon' => $icon,
          'categories' => $categories_for_node,
        ];
      }
    }

    // -------------------------
    // 5. Build filter form
    // -------------------------
    $form_state = new FormState();
    $form_state->set('categories', $categories);
    $form_state->set('services', $services);

    $form = $this->formBuilder()->buildForm(
      '\Drupal\mspp_services\Form\ServicesFilterForm',
      $form_state
    );

    return [
      '#theme' => 'mspp_services_page',
      '#services' => $services,
      '#categories' => $categories,
      '#form' => $form,
      '#attached' => [
        'library' => ['mspp_services/services'],
      ],
    ];
  }
}