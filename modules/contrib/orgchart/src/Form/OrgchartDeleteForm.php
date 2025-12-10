<?php

namespace Drupal\orgchart\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Orgchart Delete Form.
 */
class OrgchartDeleteForm extends ConfirmFormBase {

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
   * {@inheritdoc}
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->orgchart = $this->configFactory->getEditable('orgchart.charts.' . $this->routeMatch->getParameter('id'));
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') == 1) {
      $this->orgchart->delete();
    }

    $form_state->setRedirect('orgchart.configuration');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_delete_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('orgchart.configuration');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to delete %id?', ['%id' => $this->orgchart->get('path')]);
  }

}
