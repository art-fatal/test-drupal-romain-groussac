<?php

namespace Drupal\seo_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\seo_block\Service\DashboardService;
use Drupal\seo_block\Service\CacheService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SeoDashboardController extends ControllerBase {

  /**
   * @var DashboardService
   */
  protected $dashboardService;

  /**
   * @var CacheService
   */
  protected $cacheService;

  public function __construct(
    DashboardService $dashboardService,
    CacheService $cacheService
  ) {
    $this->dashboardService = $dashboardService;
    $this->cacheService = $cacheService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('seo_block.dashboard_service'),
      $container->get('seo_block.cache_service')
    );
  }

  public function dashboard(Request $request) {
    $page = (int) $request->query->get('page', 0);
    $itemsPerPage = (int) $request->query->get('items_per_page', DashboardService::DEFAULT_ITEMS_PER_PAGE);

    $dashboardData = $this->dashboardService->getDashboardData($page, $itemsPerPage);

    $build = [
      '#theme' => 'seo_dashboard',
      '#content' => $dashboardData['content'],
      '#pagination' => $dashboardData['pagination'],
      '#stats' => $dashboardData['stats'],
      '#attached' => [
        'library' => ['seo_block/dashboard'],
      ],
    ];

    if (!$dashboardData['success']) {
      $this->messenger()->addError($dashboardData['error'] ?? 'Erreur lors du chargement du dashboard');
    }

    return $build;
  }

  public function clearCache(Request $request) {
    $result['success'] = $this->cacheService->clearAllCaches();

    $status_code = $result['success'] ? 200 : 500;
    return new JsonResponse($result, $status_code);
  }
}
