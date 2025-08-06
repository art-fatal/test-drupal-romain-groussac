<?php

namespace Drupal\seo_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for SEO Dashboard.
 */
class SeoDashboardController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a SeoDashboardController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_backend,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheBackend = $cache_backend;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('cache.default'),
      $container->get('messenger')
    );
  }

  /**
   * Display the SEO dashboard.
   *
   * @return array
   *   A render array for the dashboard.
   */
  public function dashboard() {
    $content = $this->getContentList();
    $stats = $this->getDashboardStats();

    return [
      '#theme' => 'seo_dashboard',
      '#content' => $content,
      '#stats' => $stats,
      '#attached' => [
        'library' => ['seo_block/dashboard'],
      ],
    ];
  }

  /**
   * Get content list with SEO indicators.
   *
   * @return array
   *   Array of content items with SEO data.
   */
  protected function getContentList() {
    $content = [];
    
    try {
      // Get all published nodes
      $query = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->condition('status', 1)
        ->sort('changed', 'DESC')
        ->range(0, 50)
        ->execute();

      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($query);

      foreach ($nodes as $node) {
        $seo_score = $this->calculateSeoScore($node);
        
        $content[] = [
          'id' => $node->id(),
          'title' => $node->label(),
          'type' => $node->bundle(),
          'author' => $node->getOwner()->getDisplayName(),
          'created' => $node->getCreatedTime(),
          'changed' => $node->getChangedTime(),
          'status' => $node->isPublished() ? 'Publié' : 'Brouillon',
          'seo_score' => $seo_score,
          'url' => $node->toUrl()->toString(),
        ];
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Erreur lors du chargement du contenu: @error', ['@error' => $e->getMessage()]));
    }

    return $content;
  }

  /**
   * Calculate SEO score for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return int
   *   SEO score from 0 to 100.
   */
  protected function calculateSeoScore($node) {
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

  /**
   * Get dashboard statistics.
   *
   * @return array
   *   Array of statistics.
   */
  protected function getDashboardStats() {
    try {
      $node_storage = $this->entityTypeManager->getStorage('node');
      
      // Total published nodes
      $published_query = $node_storage->getQuery()
        ->condition('status', 1)
        ->count()
        ->execute();
      
      // Total draft nodes
      $draft_query = $node_storage->getQuery()
        ->condition('status', 0)
        ->count()
        ->execute();
      
      // Average SEO score
      $all_nodes_query = $node_storage->getQuery()
        ->condition('status', 1)
        ->range(0, 100)
        ->execute();
      
      $all_nodes = $node_storage->loadMultiple($all_nodes_query);
      $total_score = 0;
      $node_count = count($all_nodes);
      
      foreach ($all_nodes as $node) {
        $total_score += $this->calculateSeoScore($node);
      }
      
      $average_score = $node_count > 0 ? round($total_score / $node_count) : 0;
      
      return [
        'total_published' => $published_query,
        'total_drafts' => $draft_query,
        'average_seo_score' => $average_score,
        'last_cache_clear' => $this->getLastCacheClearTime(),
      ];
    }
    catch (\Exception $e) {
      return [
        'total_published' => 0,
        'total_drafts' => 0,
        'average_seo_score' => 0,
        'last_cache_clear' => 'N/A',
      ];
    }
  }

  /**
   * Get last cache clear time.
   *
   * @return string
   *   Formatted time string.
   */
  protected function getLastCacheClearTime() {
    $cache_id = 'seo_dashboard_last_cache_clear';
    $cache = $this->cacheBackend->get($cache_id);
    
    if ($cache) {
      return date('d/m/Y H:i:s', $cache->data);
    }
    
    return 'Jamais';
  }

  /**
   * Clear cache action.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function clearCache(Request $request) {
    try {
      // Clear all caches
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['*']);
      
      // Store cache clear time
      $this->cacheBackend->set('seo_dashboard_last_cache_clear', time(), time() + 86400);
      
      $this->messenger->addStatus($this->t('Cache vidé avec succès.'));
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Cache vidé avec succès.',
        'timestamp' => date('d/m/Y H:i:s'),
      ]);
    }
    catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la purge du cache: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Run SEO check action.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response.
   */
  public function runSeoCheck(Request $request) {
    try {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('status', 1)
        ->range(0, 20)
        ->execute();
      
      $nodes = $node_storage->loadMultiple($query);
      $results = [];
      
      foreach ($nodes as $node) {
        $seo_score = $this->calculateSeoScore($node);
        $results[] = [
          'id' => $node->id(),
          'title' => $node->label(),
          'score' => $seo_score,
          'status' => $seo_score >= 70 ? 'Bon' : ($seo_score >= 40 ? 'Moyen' : 'Faible'),
        ];
      }
      
      $this->messenger->addStatus($this->t('Vérification SEO terminée pour @count contenus.', ['@count' => count($results)]));
      
      return new JsonResponse([
        'success' => true,
        'message' => 'Vérification SEO terminée.',
        'results' => $results,
        'timestamp' => date('d/m/Y H:i:s'),
      ]);
    }
    catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la vérification SEO: ' . $e->getMessage(),
      ], 500);
    }
  }

} 