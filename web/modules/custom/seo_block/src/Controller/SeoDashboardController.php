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

  public function dashboard()
  {
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

  protected function getContentList()
  {
    $content = [];

    try {
      $query = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->sort('changed', 'DESC')
        ->range(0, 50)
        ->execute();

      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($query);

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
    } catch (Exception $e) {
      $this->messenger->addError($this->t('Erreur lors du chargement du contenu: @error', ['@error' => $e->getMessage()]));
    }

    return $content;
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
