<?php

namespace Drupal\hero_block\Plugin\Block;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use StringTranslationTrait;
use Drupal\file\Entity\File;
/**
 * Provides a 'Hero' block.
 *
 * @Block(
 *   id = "hero_block",
 *   admin_label = @Translation("Hero block"),
 *   category = @Translation("Image Blocks")
 * )
 */
class HeroBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $title = isset($config['btitle']) ? $config['btitle'] : '';
    $sub_title = isset($config['sub_title']) ? $config['sub_title'] : '';
    $hero_image = isset($config['hero_image']) ? $config['hero_image'] : '';
    if (!empty($hero_image[0])) {
      $file = \Drupal::service('entity_type.manager')->getStorage('file')->load($hero_image[0]);
      $logo_output = "";
      if ($file) {
        $get_uri = $file->get('uri')->getValue();
        $style = \Drupal::entityTypeManager()->getStorage('image_style')->load($config['image_style']);
        $logo_output = $style->buildUrl($get_uri[0]['value']);
      }
      return array(
        '#title' => $title,
        '#sub_title' => $sub_title,
        '#img' => $logo_output,
        '#theme' => 'content_hero_block',
      );
    }
  }


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $form['btitle'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title:'),
      '#default_value' => isset($config['btitle']) ? $config['btitle'] : '',
    );
    $form['sub_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Sub Title:'),
      '#default_value' => isset($config['sub_title']) ? $config['sub_title'] : '',
    );
    $optionlist = ImageStyle::loadMultiple();
    foreach ($optionlist as $key => $value) {
      $options[$key] = $value->getName();
    }
    $form['image_style'] = array(
      '#type' => 'select',
      '#title' => $this->t('Image Style:'),
      '#options' => $options,
      '#default_value' => isset($config['image_style']) ? $config['image_style'] : '',
    );
    $form['hero_image'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('Hero Image:'),
      '#default_value' => isset($config['hero_image']) ? $config['hero_image'] : '',
      '#theme' => 'image_widget',
      '#upload_location' => 'public://images/',
      '#preview_image_style' => 'medium',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('btitle', $form_state->getValue('btitle'));
    $this->setConfigurationValue('sub_title', $form_state->getValue('sub_title'));
    $this->setConfigurationValue('image_style', $form_state->getValue('image_style'));
    $this->setConfigurationValue('hero_image', $form_state->getValue('hero_image'));
    $hero_image = $form_state->getValue('hero_image');
    $file = \Drupal::service('entity_type.manager')->getStorage('file')->load($hero_image[0]);
    if (gettype($file) == 'object') {
      $file->status = \Drupal\file\FileInterface::STATUS_PERMANENT;
      // Save.
      $file->save();
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $file->setOwner($user);
    }
  }

}
