<?php

namespace Drupal\orgchart\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes.
 */
class OrgchartRoutes {

  /**
   * Provides dynamic routes.
   */
  public function routes() {
    $routes = [];

    $orgcharts = _orgchart_get_all();
    foreach ($orgcharts as $key => $orgchart) {
      $routes['orgchart.charts.' . $key] = new Route(
        '/' . $orgchart['path'],
        [
          '_controller' => '\Drupal\orgchart\Controller\OrgchartController::orgchartView',
          '_title' => $orgchart['title'],
        ],
        [
          '_permission'  => 'access orgchart',
        ]
      );
    }

    return $routes;
  }

}
