<?php

namespace Drupal\orgchart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Orgchart Add Form.
 */
class OrgchartAddForm extends FormBase {

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
   * The route builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory, RouteBuilderInterface $route_builder) {
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
    $this->routeBuilder = $route_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('config.factory'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orgchart_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Title'),
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Path'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Orgchart'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $path = ltrim($values['path'], '/');
    $path = rtrim($path, '/');
    $route_count = $this->routeProvider->getRoutesByPattern($path)->count();
    if ($route_count > 0) {
      $form_state->setErrorByName('path', $this->t('Path not available'));
    }
    else {
      $list = _orgchart_get_all();
      foreach ($list as $value) {
        if ($value['path'] == $path) {
          $form_state->setErrorByName('path', $this->t('Path not available'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $path = ltrim($values['path'], '/');
    $path = rtrim($path, '/');
    $key = md5($path . time());

    $defaults = $this->configFactory->get('orgchart.settings')->get('defaults');
    $config = $this->configFactory->getEditable('orgchart.charts.' . $key);
    $config->set('title', $values['title']);
    $config->set('path', $path);
    $config->set('config', $defaults);
    $config->save();

    $this->routeBuilder->rebuild();
    $form_state->setRedirect('orgchart.configuration.chart.build', ['id' => $key]);
  }

}
