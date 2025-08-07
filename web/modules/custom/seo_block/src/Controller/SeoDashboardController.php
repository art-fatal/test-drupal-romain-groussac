<?php

namespace Drupal\seo_block\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SeoDashboardController extends ControllerBase
{

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var MessengerInterface
   */
  protected $messenger;

  const ITEMS_PER_PAGE = 5;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface      $cache_backend,
    MessengerInterface         $messenger
  )
  {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheBackend = $cache_backend;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache.default'),
      $container->get('messenger')
    );
  }

  public function dashboard(Request $request)
  {
    $page = (int) $request->query->get('page', 0);
    $itemsPerPage = (int) $request->query->get('items_per_page', self::ITEMS_PER_PAGE);

    $itemsPerPage = min(max($itemsPerPage, 5), 100);

    $contentData = $this->getContentList($page, $itemsPerPage);
    $stats = $this->getDashboardStats();

    $build = [
      '#theme' => 'seo_dashboard',
      '#content' => $contentData['content'],
      '#pagination' => $contentData['pagination'],
      '#stats' => $stats,
      '#attached' => [
        'library' => ['seo_block/dashboard'],
      ],
    ];

    return $build;
  }

  protected function getContentList($page = 0, $itemsPerPage = self::ITEMS_PER_PAGE)
  {
    $content = [];
    $pagination = [];

    try {
      $nodeStorage = $this->entityTypeManager->getStorage('node');

      // Compter le nombre total de nœuds
      $totalCount = $nodeStorage->getQuery()
        ->count()
        ->execute();

      // Calculer l'offset
      $offset = $page * $itemsPerPage;

      // Récupérer les nœuds pour la page courante
      $query = $nodeStorage->getQuery()
        ->sort('changed', 'DESC')
        ->range($offset, $itemsPerPage)
        ->execute();

      $nodes = $nodeStorage->loadMultiple($query);

      foreach ($nodes as $node) {
        $content[] = [
          'id' => $node->id(),
          'title' => $node->label(),
          'type' => $node->bundle(),
          'author' => $node->getOwner()->getDisplayName(),
          'created' => $node->getCreatedTime(),
          'changed' => $node->getChangedTime(),
          'status' => $node->isPublished() ? 'Publié' : 'Brouillon',
          'seo_score' => $this->calculateSeoScore($node),
          'url' => $node->toUrl()->toString(),
        ];
      }

      $pagination = $this->calculatePagination($totalCount, $page, $itemsPerPage);
    } catch (Exception $e) {
      $this->messenger->addError($this->t('Erreur lors du chargement du contenu: @error', ['@error' => $e->getMessage()]));
    }

    return [
      'content' => $content,
      'pagination' => $pagination,
    ];
  }

  protected function calculatePagination($totalCount, $currentPage, $itemsPerPage)
  {
    $totalPages = ceil($totalCount / $itemsPerPage);
    $currentPage = max(0, min($currentPage, $totalPages - 1));

    // Calculer les pages à afficher
    $pagesToShow = 5;
    $startPage = max(0, $currentPage - floor($pagesToShow / 2));
    $endPage = min($totalPages - 1, $startPage + $pagesToShow - 1);

    // Ajuster le début si on est près de la fin
    if ($endPage - $startPage < $pagesToShow - 1) {
      $startPage = max(0, $endPage - $pagesToShow + 1);
    }

    $pages = [];
    for ($i = $startPage; $i <= $endPage; $i++) {
      $pages[] = [
        'number' => $i + 1,
        'page' => $i,
        'current' => $i === $currentPage,
        'url' => $this->generatePageUrl($i, $itemsPerPage),
      ];
    }

    return [
      'current_page' => $currentPage + 1,
      'total_pages' => $totalPages,
      'total_items' => $totalCount,
      'items_per_page' => $itemsPerPage,
      'start_item' => ($currentPage * $itemsPerPage) + 1,
      'end_item' => min(($currentPage + 1) * $itemsPerPage, $totalCount),
      'pages' => $pages,
      'has_previous' => $currentPage > 0,
      'has_next' => $currentPage < $totalPages - 1,
      'previous_url' => $this->generatePageUrl($currentPage - 1, $itemsPerPage),
      'next_url' => $this->generatePageUrl($currentPage + 1, $itemsPerPage),
      'first_url' => $this->generatePageUrl(0, $itemsPerPage),
      'last_url' => $this->generatePageUrl($totalPages - 1, $itemsPerPage),
    ];
  }

  protected function generatePageUrl($page, $itemsPerPage)
  {
    $routeName = 'seo_block.dashboard';
    $routeParameters = [];
    $query = [
      'page' => $page,
      'items_per_page' => $itemsPerPage,
    ];

    return \Drupal::urlGenerator()->generateFromRoute($routeName, $routeParameters, ['query' => $query]);
  }

  protected function calculateSeoScore($node)
  {
    $score = 0;

    // Title length check
    $title = $node->getTitle();
    if (strlen($title) >= 30 && strlen($title) <= 60) {
      $score += 20;
    }

    // Body content check
    if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
      $body = $node->get('body')->first()->getValue()['value'];
      if (strlen(strip_tags($body)) > 300) {
        $score += 30;
      }
    }

    // URL alias check
    if ($node->hasField('path') && !$node->get('path')->isEmpty()) {
      $score += 15;
    }

    // Meta description check (if available)
    if ($node->hasField('field_meta_description') && !$node->get('field_meta_description')->isEmpty()) {
      $score += 20;
    }

    // Image check (if available)
    if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
      $score += 15;
    }

    return min(100, $score);
  }

  protected function getDashboardStats()
  {
    try {
      $nodeStorage = $this->entityTypeManager->getStorage('node');

      $publishedQuery = $nodeStorage->getQuery()
        ->condition('status', 1)
        ->count()
        ->execute();

      $draftQuery = $nodeStorage->getQuery()
        ->condition('status', 0)
        ->count()
        ->execute();

      $allNodesQuery = $nodeStorage->getQuery()
        ->condition('status', 1)
        ->range(0, 100)
        ->execute();

      $allNodes = $nodeStorage->loadMultiple($allNodesQuery);
      $totalScore = 0;
      $nodeCount = count($allNodes);

      foreach ($allNodes as $node) {
        $totalScore += $this->calculateSeoScore($node);
      }

      $averageScore = $nodeCount > 0 ? round($totalScore / $nodeCount) : 0;

      return [
        'total_published' => $publishedQuery,
        'total_drafts' => $draftQuery,
        'average_seo_score' => $averageScore,
      ];
    } catch (Exception $e) {
      return [
        'total_published' => 0,
        'total_drafts' => 0,
        'average_seo_score' => 0,
      ];
    }
  }

  public function clearCache(Request $request) {
    try {
      drupal_flush_all_caches();

      // Vider les caches spécifiques importants
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['*']);
      \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
      \Drupal::service('theme.registry')->reset();
      \Drupal::service('twig')->invalidate();

      $this->messenger->addStatus($this->t('Cache vidé avec succès'));

      return new JsonResponse([
        'success' => true,
        'message' => 'Cache vidé avec succès',
        'timestamp' => date('d/m/Y H:i:s'),
      ]);
    }
    catch (Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la purge du cache: ' . $e->getMessage(),
      ], 500);
    }
  }


}
