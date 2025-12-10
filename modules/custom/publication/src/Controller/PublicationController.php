<?php

namespace Drupal\publication\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller for the publication pages.
 */
class PublicationController extends ControllerBase {

  /**
   * Displays the publication content.
   *
   * @return array
   *   A render array representing the publication page content.
   */
  public function content() {
    // Query to get all published publication nodes.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'publication')
      ->condition('status', 1)
      ->sort('created', 'DESC');
    
    $nids = $query->execute();
    
    // If no nodes found, check if content type exists
    if (empty($nids)) {
      $types = \Drupal::service('entity_type.manager')
        ->getStorage('node_type')
        ->loadMultiple();
      if (!isset($types['publication'])) {
        $messenger->addWarning('Publication content type does not exist. Please create it first.');
        return [
          '#markup' => $this->t('Please set up the Publication content type first.'),
        ];
      }
    }
    
    $publications = Node::loadMultiple($nids);
    
    // Prepare publications for template.
    $items = [];
    foreach ($publications as $publication) {
      $items[] = [
        'title' => $publication->getTitle(),
        'body' => $publication->hasField('body') ? $publication->get('body')->value : '',
        'created' => $publication->getCreatedTime(),
        'field_fichier' => $publication->hasField('field_fichier') ? $publication->get('field_fichier')->entity->getFileUri() : '',
      ];
    }

    return [
      '#theme' => 'publication_list',
      '#publications' => $items,
      '#cache' => [
        'tags' => ['node_list:publication'],
      ],
    ];
  }

}
