<?php

namespace Drupal\the_minister\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block_content\Entity\BlockContent;

/**
 * Provides a 'The Minister' Block.
 *
 * @Block(
 *   id = "the_minister",
 *   admin_label = @Translation("The Minister"),
 *   category = @Translation("Custom"),
 * )
 */
class TheMinisterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load block content ID 6 (the_minister with data).
    $block_content = BlockContent::load(6);

    if (!$block_content) {
      return [];
    }

    $name = '';
    $photo_url = '';

    // Get minister name.
    if ($block_content->hasField('field_block_minister_name') && !$block_content->get('field_block_minister_name')->isEmpty()) {
      $name = $block_content->get('field_block_minister_name')->value;
    }

    // Get minister photo (image field, not media).
    if ($block_content->hasField('field_block_minister_photo') && !$block_content->get('field_block_minister_photo')->isEmpty()) {
      $file = $block_content->get('field_block_minister_photo')->entity;
      if ($file) {
        $photo_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }

    return [
      '#theme' => 'the_minister_block',
      '#name' => $name,
      '#photo_url' => $photo_url,
      '#attached' => [
        'library' => [
          'the_minister/the_minister',
        ],
      ],
      '#cache' => [
        'tags' => $block_content->getCacheTags(),
      ],
    ];
  }

}
