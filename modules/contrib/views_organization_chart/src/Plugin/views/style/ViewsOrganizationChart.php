<?php

namespace Drupal\views_organization_chart\Plugin\views\style;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Organization chart style plugin.
 *
 * @ingroup views_style_plugins
 */
#[ViewsStyle(
  id: "views_organization_chart",
  title: new TranslatableMarkup("Organization chart"),
  help: new TranslatableMarkup("Displays rows in a organization chart."),
  theme: "views_style_views_organization_chart",
  display_types: ["normal"],
)]
class ViewsOrganizationChart extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['name_field'] = ['default' => ''];
    $options['title_field'] = ['default' => ''];
    $options['image_field'] = ['default' => ''];
    $options['parent_field'] = ['default' => ''];
    $options['levels_color'] = ['default' => 'silver,#980104,#359154'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $optionsNone = ['' => $this->t('- None -')];
    $fields = $this->displayHandler->getHandlers('field');
    $labels = $this->displayHandler->getFieldLabels();
    $field_labels = [];
    foreach ($fields as $field_name => $field) {
      $field_labels[$field_name] = $labels[$field_name];
      if (!empty($field->options["type"])) {
        $type[$field->options["type"]][$field_name] = $labels[$field_name];
      }
      if (!empty($field->multiple)) {
        $multiples[$field_name] = $field->multiple;
      }
    }
    $options = $optionsNone + $field_labels;
    $optionsImage = empty($type["image"]) ? $optionsNone : $optionsNone + $type["image"];
    $optionsParent = empty($type["entity_reference_label"]) ? $optionsNone : $optionsNone + $type["entity_reference_label"];
    $form['name_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Name field'),
      '#description' => $this->t('Select a name field'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $this->options['name_field'] ?? '',
    ];
    $form['title_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Title field'),
      '#options' => $options,
      '#default_value' => $this->options['title_field'] ?? '',
    ];
    $form['image_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Avatar field'),
      '#description' => $this->t('Select a image field'),
      '#options' => $optionsImage,
      '#default_value' => $this->options['image_field'] ?? '',
    ];
    $form['parent_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent field'),
      '#description' => $this->t('Select a number field'),
      '#options' => $optionsParent,
      '#default_value' => $this->options['parent_field'] ?? '',
      '#required' => TRUE,
    ];
    $form['levels_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Level color'),
      '#description' => $this->t('Set the color for each level, separated by ,'),
      '#default_value' => $this->options['levels_color'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

}
