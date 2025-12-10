<?php

namespace Drupal\service_featured\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block_content\Entity\BlockContent;

/**
 * Provides a 'Service Featured' Block.
 *
 * @Block(
 *   id = "service_featured",
 *   admin_label = @Translation("Service Featured"),
 *   category = @Translation("Custom"),
 * )
 */
class ServiceFeaturedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block_content = BlockContent::load(4);
    
    if (!$block_content) {
      return [];
    }

    $items = [];
    
    // Get field values (these are multi-value fields).
    $names = $block_content->get('field_service_featured_name')->getValue();
    $descriptions = $block_content->get('field_service_featured_desc')->getValue();
    $links = $block_content->get('field_service_featured_link')->getValue();
    $photos = $block_content->get('field_service_featured_photo')->referencedEntities();

    foreach ($names as $delta => $name) {
      $item = [
        'name' => $name['value'] ?? '',
        'description' => $descriptions[$delta]['value'] ?? '',
        'url' => $links[$delta]['uri'] ?? '',
        'link_title' => $links[$delta]['title'] ?? '',
      ];
      
      // Get photo URL if exists (Media entity).
      if (isset($photos[$delta])) {
        $media = $photos[$delta];
        // Get the source field (usually field_media_image for images).
        $source_field = $media->getSource()->getConfiguration()['source_field'];
        if ($media->hasField($source_field) && !$media->get($source_field)->isEmpty()) {
          $file = $media->get($source_field)->entity;
          if ($file) {
            $item['photo_url'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }
      
      $items[] = $item;
    }

    return [
      '#theme' => 'service_featured_block',
      '#items' => $items,
      '#attached' => [
        'library' => [
          'service_featured/service_featured',
        ],
      ],
      '#cache' => [
        'tags' => $block_content->getCacheTags(),
      ],
    ];
  }

}
