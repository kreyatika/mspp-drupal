<?php

namespace Drupal\directions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the Directions page.
 */
class DirectionsController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a DirectionsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Displays the directions page.
   *
   * @return array
   *   Render array for the directions page.
   */
  public function content() {
    // Load filter terms from directions vocabulary with hierarchy
    $filter_terms = $this->buildTermTree('directions');

    // Load directions from content type
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => 'directions',
        'status' => 1,
      ]);

    $directions = [];
    foreach ($nodes as $node) {
      $directions[] = [
        'id' => $node->id(),
        'title' => $node->label(),
        'description' => $node->hasField('field_mission') && !$node->get('field_mission')->isEmpty() 
          ? $node->get('field_mission')->value 
          : '',
        'url' => $node->toUrl()->toString(),
      ];
    }

    return [
      '#theme' => 'directions_list',
      '#directions' => $directions,
      '#filter_terms' => $filter_terms,
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'directions/directions',
        ],
      ],
    ];
  }

  /**
   * Builds a hierarchical tree of taxonomy terms.
   *
   * @param string $vid
   *   The vocabulary ID.
   * @param int $parent
   *   The parent term ID.
   *
   * @return array
   *   An array of terms with children.
   */
  protected function buildTermTree($vid, $parent = 0) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, $parent, 1, TRUE);
    $tree = [];

    foreach ($terms as $term) {
      $children = $this->buildTermTree($vid, $term->id());
      $tree[] = [
        'id' => $term->id(),
        'name' => $term->label(),
        'url' => $term->toUrl()->toString(),
        'children' => $children,
      ];
    }

    return $tree;
  }

}
