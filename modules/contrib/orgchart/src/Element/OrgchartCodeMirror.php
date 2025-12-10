<?php

namespace Drupal\orgchart\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textarea;
use Drupal\orgchart\Utility\OrgchartYaml;

/**
 * Provides a yaml editor for using CodeMirror.
 *
 * @FormElement("orgchart_codemirror")
 */
class OrgchartCodeMirror extends Textarea {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#mode' => 'yaml',
      '#skip_validation' => FALSE,
      '#decode_value' => FALSE,
      '#cols' => 60,
      '#rows' => 5,
      '#wrap' => TRUE,
      '#resizable' => 'vertical',
      '#process' => [
        [$class, 'processOrgchartCodeMirror'],
        [$class, 'processAjaxForm'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderOrgchartCodeMirror'],
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'textarea',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE && $element['#mode'] === 'yaml' && isset($element['#default_value'])) {
      if (is_array($element['#default_value'])) {
        $element['#default_value'] = OrgchartYaml::encode($element['#default_value']);
      }
      if ($element['#default_value'] === '{  }') {
        $element['#default_value'] = '';
      }
      return $element['#default_value'];
    }
    return NULL;
  }

  /**
   * Processes a 'orgchart_codemirror' element.
   */
  public static function processOrgchartCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#mode'] = 'yaml';

    if (empty($element['#wrap'])) {
      $element['#attributes']['wrap'] = 'off';
    }

    $element += ['#element_validate' => []];
    array_unshift($element['#element_validate'], [
      get_called_class(),
      'validateOrgchartCodeMirror',
    ]);

    return $element;
  }

  /**
   * Prepares a #type 'orgchart_code' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderOrgchartCodeMirror(array $element) {
    static::setAttributes($element, [
      'js-orgchart-codemirror',
      'orgchart-codemirror',
      $element['#mode'],
    ]);
    $element['#attributes']['data-orgchart-codemirror-mode'] = 'yaml';
    $element['#attached']['library'][] = 'orgchart/orgchart.codemirror';
    return $element;
  }

  /**
   * Orgchart element validation handler for #type 'orgchart_codemirror'.
   */
  public static function validateOrgchartCodeMirror(&$element, FormStateInterface $form_state, &$complete_form) {
    if (!empty($element['#disable'])) {
      $element['#value'] = $element['#default_value'] ?? NULL;
      $form_state->setValueForElement($element, $element['#default_value'] ?? NULL);
    }
    $errors = static::getErrors($element, $form_state, $complete_form);
    if ($errors) {
      $build = [
        'title' => [
          '#markup' => t('%title is not valid.', ['%title' => static::getTitle($element)]),
        ],
        'errors' => [
          '#theme' => 'item_list',
          '#items' => $errors,
        ],
      ];
      $form_state->setError($element, \Drupal::service('renderer')->render($build));
    }
    else {
      if ($element['#mode'] === 'yaml'
        && (isset($element['#default_value']) && is_array($element['#default_value']) || $element['#decode_value'])
      ) {
        $value = $element['#value'] ? OrgchartYaml::decode($element['#value']) : [];
        $form_state->setValueForElement($element, $value);
      }
    }
  }

  /**
   * Get validation errors.
   */
  protected static function getErrors(&$element, FormStateInterface $form_state, &$complete_form) {
    if (!empty($element['#skip_validation'])) {
      return NULL;
    }

    return static::validateYaml($element, $form_state, $complete_form);
  }

  /**
   * Get an element's title.
   *
   * @param array $element
   *   An element.
   *
   * @return string
   *   The element's title.
   */
  protected static function getTitle(array $element) {
    if (isset($element['#title'])) {
      return $element['#title'];
    }

    return t('YAML');
  }

  /**
   * Validate YAML.
   */
  protected static function validateYaml($element, FormStateInterface $form_state, $complete_form) {
    try {
      $value = $element['#value'];
      $data = OrgchartYaml::decode($value);
      if (!is_array($data) && $value) {
        throw new \Exception('YAML must contain an associative array of elements.');
      }
      return NULL;
    }
    catch (\Exception $exception) {
      return [$exception->getMessage()];
    }
  }

}
