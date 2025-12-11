<?php

namespace Drupal\mspp_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for MSPP custom search page.
 */
class SearchController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a SearchController object.
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
   * Search page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   A render array for the search page.
   */
  public function search(Request $request) {
    $keywords = $request->query->get('q', '');
    $results = [];

    if (!empty($keywords)) {
      $results = $this->performSearch($keywords);
    }

    return [
      '#theme' => 'mspp_search_results',
      '#keywords' => $keywords,
      '#results' => $results,
      '#result_count' => count($results),
    ];
  }

  /**
   * Perform the search query.
   *
   * @param string $keywords
   *   The search keywords.
   *
   * @return array
   *   Array of search results.
   */
  protected function performSearch($keywords) {
    $results = [];
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Search in node titles and body fields.
    $query = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 50);

    // Create an OR condition group for title and body.
    $or_group = $query->orConditionGroup()
      ->condition('title', '%' . $keywords . '%', 'LIKE')
      ->condition('body.value', '%' . $keywords . '%', 'LIKE');

    $query->condition($or_group);

    $nids = $query->execute();

    if (!empty($nids)) {
      $nodes = $node_storage->loadMultiple($nids);

      foreach ($nodes as $node) {
        $body = $node->hasField('body') && !$node->get('body')->isEmpty()
          ? $node->get('body')->value
          : '';

        // Create excerpt with highlighted keywords.
        $excerpt = $this->createExcerpt($body, $keywords);

        $url = $node->toUrl()->toString();
        // Get clean path for display (remove base path).
        $display_url = $node->toUrl()->getInternalPath();

        $results[] = [
          'title' => $node->getTitle(),
          'url' => $url,
          'display_url' => '/' . $display_url,
          'type' => $node->type->entity->label(),
          'excerpt' => $excerpt,
          'date' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'd/m/Y'),
        ];
      }
    }

    return $results;
  }

  /**
   * Create an excerpt from the body text.
   *
   * @param string $text
   *   The full text.
   * @param string $keywords
   *   The search keywords.
   * @param int $length
   *   The excerpt length.
   *
   * @return string
   *   The excerpt with highlighted keywords.
   */
  protected function createExcerpt($text, $keywords, $length = 200) {
    // Strip HTML tags.
    $text = strip_tags($text);

    // Find the position of the keyword.
    $pos = stripos($text, $keywords);

    if ($pos !== FALSE) {
      // Start a bit before the keyword.
      $start = max(0, $pos - 50);
      $excerpt = substr($text, $start, $length);

      // Add ellipsis if needed.
      if ($start > 0) {
        $excerpt = '...' . $excerpt;
      }
      if (strlen($text) > $start + $length) {
        $excerpt .= '...';
      }
    }
    else {
      // Just take the beginning of the text.
      $excerpt = substr($text, 0, $length);
      if (strlen($text) > $length) {
        $excerpt .= '...';
      }
    }

    return $excerpt;
  }

}
