<?php

namespace Drupal\orgchart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Orgchart Controller.
 */
class OrgchartController extends ControllerBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, ConfigFactoryInterface $config_factory) {
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function orgchartView() {
    $config = $this->configFactory->get($this->routeMatch->getRouteName());
    $configuration = $config->get('config');
    $build = $config->get('build');

    $displays = [];
    if (!empty($build)) {
      foreach ($build as $key => $value) {
        $displays[$key] = [
          'values' => json_encode($value['values']),
          'width' => $configuration[$key]['width'] . 'px',
          'height' => (!empty($value['height'])) ? $value['height'] . 'px' : ($configuration[$key]['width'] * 2) . 'px',
        ];
      }
    }

    return [
      '#markup' => '<div id="render_orgchart"></div>',
      '#cache' => [
        'tags' => [
          $this->routeMatch->getRouteName(),
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

}
