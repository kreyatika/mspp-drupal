<?php

namespace Drupal\orgchart\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Orgchart Edit Form.
 */
class OrgchartEditForm extends OrgchartConfigForm {

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
   * The route builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * The current orgchart.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $orgchart;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match, RouteBuilderInterface $route_builder) {
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->routeBuilder = $route_builder;
    $this->orgchart = $this->configFactory->getEditable('orgchart.charts.' . $this->routeMatch->getParameter('id'));
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $id = NULL) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orgchart_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Title'),
      '#default_value' => $this->orgchart->get('title'),
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Path'),
      '#default_value' => $this->orgchart->get('path'),
    ];

    parent::addConfigForm($form, $this->orgchart->get('config'));
    $form['defaults']["#title"] = $this->t('Configuration');
    $form['defaults']["#open"] = TRUE;

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Orgchart'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $values = $form_state->getValues();
    if ($values['path'] != $this->orgchart->get('path')) {
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['path'] != $this->orgchart->get('path')) {
      $path = ltrim($values['path'], '/');
      $path = rtrim($path, '/');
      $this->orgchart->set('path', $path);
      $this->routeBuilder->rebuild();
    }

    $this->orgchart->set('title', $values['title']);
    $this->orgchart->set('config', $values['defaults']);
    $this->orgchart->save();
    Cache::invalidateTags(['orgchart.charts.' . $this->routeMatch->getParameter('id')]);
  }

}
