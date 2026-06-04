<?php

namespace Drupal\publications\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\publications\Service\PublicationsApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the Publications listing page.
 */
class PublicationsController extends ControllerBase {

  protected $publicationsApi;
  protected $pagerManager;

  public function __construct(PublicationsApiService $publications_api, PagerManagerInterface $pager_manager) {
    $this->publicationsApi = $publications_api;
    $this->pagerManager    = $pager_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('publications.api'),
      $container->get('pager.manager')
    );
  }

  public function content(Request $request): array {
    $filters = [
      'search'    => $request->query->get('search', ''),
      'category'  => $request->query->get('category', ''),
      'direction' => $request->query->get('direction', ''),
      'year'      => $request->query->get('year', ''),
      'sort'      => $request->query->get('sort', 'date_desc'),
    ];

    $limit = 20;
    $total = $this->publicationsApi->countPublications($filters);
    $pager = $this->pagerManager->createPager($total, $limit);
    $page  = $pager->getCurrentPage();

    return [
      '#theme'        => 'publications_list',
      '#publications' => $this->publicationsApi->getPublications($filters, $limit, $page * $limit),
      '#categories'   => $this->publicationsApi->getCategories(),
      '#directions'   => $this->publicationsApi->getDirections(),
      '#years'        => $this->publicationsApi->getYears(),
      '#filters'      => $filters,
      '#total'        => $total,
      '#pager'        => ['#type' => 'pager'],
      '#attached'     => [
        'library' => ['publications/publications-filter'],
      ],
    ];
  }

}
