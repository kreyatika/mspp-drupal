<?php

namespace Drupal\hero_section\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block_content\Entity\BlockContent;

/**
 * Provides a 'Hero Section' Block.
 *
 * @Block(
 *   id = "hero_section",
 *   admin_label = @Translation("Hero Section"),
 *   category = @Translation("Custom"),
 * )
 */
class HeroSectionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load block content ID 3 (hero_section).
    $block_content = BlockContent::load(3);

    if (!$block_content) {
      return [];
    }

    $title = '';
    $slogan = '';
    $image_url = '';

    // Get hero title.
    if ($block_content->hasField('field_hero_title') && !$block_content->get('field_hero_title')->isEmpty()) {
      $title = $block_content->get('field_hero_title')->value;
    }

    // Get hero slogan.
    if ($block_content->hasField('field_hero_slogan') && !$block_content->get('field_hero_slogan')->isEmpty()) {
      $slogan = $block_content->get('field_hero_slogan')->value;
    }

    // Get hero image (media reference).
    if ($block_content->hasField('field_hero_image') && !$block_content->get('field_hero_image')->isEmpty()) {
      $media = $block_content->get('field_hero_image')->entity;
      if ($media) {
        $source_field = $media->getSource()->getConfiguration()['source_field'];
        if ($media->hasField($source_field) && !$media->get($source_field)->isEmpty()) {
          $file = $media->get($source_field)->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }
    }

    return [
      '#theme' => 'hero_section_block',
      '#title' => $title,
      '#slogan' => $slogan,
      '#image_url' => $image_url,
      '#attached' => [
        'library' => [
          'hero_section/hero_section',
        ],
      ],
      '#cache' => [
        'tags' => $block_content->getCacheTags(),
      ],
    ];
  }

}
