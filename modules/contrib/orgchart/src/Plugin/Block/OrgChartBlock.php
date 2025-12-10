<?php

namespace Drupal\orgchart\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a 'Organizational Charts' Block.
 *
 * @Block(
 *   id = "orgchart",
 *   admin_label = @Translation("Organizational Charts"),
 * )
 */
class OrgChartBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => 0,
      'orgchart' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['orgchart'] = [
      '#type' => 'select',
      '#options' => self::getCharts(),
      '#title' => $this->t('Organizational Chart'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['orgchart'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['orgchart'] = $form_state->getValue('orgchart');

    Cache::invalidateTags($this->getCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $id = 'orgchart.charts.' . $this->configuration['orgchart'];
    $config = $this->configFactory->get($id);
    $configuration = $config->get('config');
    $build = $config->get('build');

    $displays = [];
    if (!empty($build)) {
      foreach ($build as $key => $value) {
        $displays[$key] = [
          'values' => json_encode($value['values']),
          'width' => $configuration[$key]['width'] . 'px',
          'height' => $value['height'] . 'px',
        ];
      }
    }

    return [
      '#type' => 'inline_template',
      '#template' => '<style>{{ css }}</style><div id="render_orgchart"></div>',
      '#context' => [
        'css' => $this->injectCss(),
      ],
      '#cache' => [
        'tags' => [
          $id,
        ],
      ],
      '#attached' => [
        'library' => [
          'orgchart/orgchart',
          'orgchart/orgchart.colors',
        ],
        'drupalSettings' => [
          'orgcharts' => $displays,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $id = 'orgchart.charts.' . $this->configuration['orgchart'];
    return Cache::mergeTags(
      parent::getCacheTags(), [$id]
    );
  }

  /**
   * Gets the list of available charts.
   */
  public function getCharts() {
    $options = [];
    $orgcharts = _orgchart_get_all();

    foreach ($orgcharts as $key => $value) {
      $options[$key] = $value['title'];
    }

    return $options;
  }

  /**
   * Injects css variables.
   */
  public function injectCss() {
    $id = 'orgchart.charts.' . $this->configuration['orgchart'];
    $config = $this->configFactory->get($id);
    $css = _orgchart_build_css_vars($config);

    return implode('', $css);
  }

}
