<?php

namespace Drupal\schools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\schools\Service\SchoolsApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the Schools listing page.
 */
class SchoolsController extends ControllerBase {

  /**
   * The schools API service.
   *
   * @var \Drupal\schools\Service\SchoolsApiService
   */
  protected $schoolsApi;

  /**
   * Constructs a SchoolsController object.
   *
   * @param \Drupal\schools\Service\SchoolsApiService $schools_api
   *   The schools API service.
   */
  public function __construct(SchoolsApiService $schools_api) {
    $this->schoolsApi = $schools_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('schools.api')
    );
  }

  /**
   * Displays the schools page.
   *
   * @return array
   *   A render array for the schools page.
   */
  public function content() {
    return [
      '#theme' => 'schools_list',
      '#schools' => $this->schoolsApi->getSchools(),
      '#departments' => $this->schoolsApi->getDepartments(),
      '#programs' => $this->schoolsApi->getPrograms(),
      '#attached' => [
        'library' => ['schools/schools-filter'],
      ],
    ];
  }
}
