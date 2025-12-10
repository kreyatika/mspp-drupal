<?php

namespace Drupal\orgchart\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\orgchart\Utility\OrgchartYaml;

/**
 * Orgchart Build Form.
 */
class OrgchartYamlForm extends FormBase {

  /**
   * The current route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current orgchart.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $orgchart;

  /**
   * The current display.
   *
   * @var string
   */
  protected $display;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->orgchart = $this->configFactory->getEditable('orgchart.charts.' . $this->routeMatch->getParameter('id'));
    $this->display = $this->routeMatch->getParameter('display');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $id = NULL) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orgchart_build_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    foreach ($this->orgchart->get('config') as $key => $config) {
      if (isset($config['active']) && $config['active'] == 1) {
        if ($key == $this->display) {
          $display_config = $config;
        }
      }
    }

    if (!empty($display_config)) {
      $build = $this->orgchart->get('build');
      $form['elements'] = [
        '#type' => 'orgchart_codemirror',
        '#title' => $this->t('@display Elements (YAML)', ['@display' => ucfirst($this->display)]),
        '#default_value' => (!empty($build[$this->display]['values'])) ? OrgchartYaml::encode($build[$this->display]['values']) : '',
        '#required' => TRUE,
        '#element_validate' => ['::validateElementsYaml'],
        '#attributes' => ['style' => 'min-height: 600px'],
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ];
    }

    return $form;
  }

  /**
   * Element validate callback.
   */
  public function validateElementsYaml(array &$element, FormStateInterface $form_state) {
    if ($form_state->getErrors()) {
      return;
    }

    $elements = $form_state->getValue('elements');
    $elements = OrgchartYaml::decode($elements);
    $form_state->setValueForElement($element, $elements);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $build = $this->orgchart->get('build');
    $build[$this->display]['values'] = $values['elements'];

    $this->orgchart->set('build', $build);
    $this->orgchart->save();
    Cache::invalidateTags(['orgchart.charts.' . $this->routeMatch->getParameter('id')]);
  }

}
