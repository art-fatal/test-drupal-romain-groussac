<?php

namespace Drupal\seo_block\Repository;

use Drupal;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class ContentRepository {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var CacheBackendInterface
   */
  protected $cacheBackend;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    CacheBackendInterface $cacheBackend
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->cacheBackend = $cacheBackend;
  }

  public function getPaginatedNodes(int $page = 0, int $itemsPerPage = 5): array {
    $storage = $this->entityTypeManager->getStorage('node');

    $count = $storage->getQuery()
      ->count()
      ->execute();

    $offset = $page * $itemsPerPage;

    $query = $storage->getQuery()
      ->sort('changed', 'DESC')
      ->range($offset, $itemsPerPage)
      ->execute();

    $nodes = $storage->loadMultiple($query);

    $content = [];
    foreach ($nodes as $node) {
      $content[] = $this->formatNodeData($node);
    }

    $pagination = $this->calculatePagination($count, $page, $itemsPerPage);

    return [
      'content' => $content,
      'pagination' => $pagination,
    ];
  }

  protected function formatNodeData(NodeInterface $node): array {
    return [
      'id' => $node->id(),
      'title' => $node->label(),
      'type' => $node->bundle(),
      'author' => $node->getOwner()->getDisplayName(),
      'created' => $node->getCreatedTime(),
      'changed' => $node->getChangedTime(),
      'status' => $node->isPublished() ? 'Publié' : 'Brouillon',
      'url' => $node->toUrl()->toString(),
    ];
  }

  protected function calculatePagination(int $totalCount, int $currentPage, int $itemsPerPage): array {
    $totalPages = ceil($totalCount / $itemsPerPage);
    $currentPage = max(0, min($currentPage, $totalPages - 1));

    $pagesToShow = 5;
    $startPage = max(0, $currentPage - floor($pagesToShow / 2));
    $endPage = min($totalPages - 1, $startPage + $pagesToShow - 1);

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

  protected function generatePageUrl(int $page, int $itemsPerPage): string {
    $routeName = 'seo_block.dashboard';
    $routeParameters = [];
    $query = [
      'page' => $page,
      'items_per_page' => $itemsPerPage,
    ];

    return Drupal::urlGenerator()->generateFromRoute($routeName, $routeParameters, ['query' => $query]);
  }

  public function getDashboardStats(): array {
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $publishedCount = $nodeStorage->getQuery()
      ->condition('status', 1)
      ->count()
      ->execute();

    $draftCount = $nodeStorage->getQuery()
      ->condition('status', 0)
      ->count()
      ->execute();

    return [
      'total_published' => $publishedCount,
      'total_drafts' => $draftCount,
    ];
  }

  public function getPublishedNodes(int $limit = 100): array {
    $node_storage = $this->entityTypeManager->getStorage('node');

    $query = $node_storage->getQuery()
      ->condition('status', 1)
      ->range(0, $limit)
      ->execute();

    return $node_storage->loadMultiple($query);
  }

  public function getLatestArticles(int $limit = 5, string $contentType = 'article'): array {
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $query = $nodeStorage->getQuery()
      ->condition('type', $contentType)
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, $limit)
      ->accessCheck(FALSE)
      ->execute();

    if (empty($query)) {
      return [];
    }

    $nodes = $nodeStorage->loadMultiple($query);
    $articles = [];

    foreach ($nodes as $node) {
      $articles[] = $this->formatArticleData($node);
    }

    return $articles;
  }

  protected function formatArticleData(NodeInterface $node): array {
    $bodyField = $node->get('body');
    $summary = '';

    if (!$bodyField->isEmpty()) {
      $bodyValue = $bodyField->first()->getValue();
      $summary = !empty($bodyValue['summary'])
        ? $bodyValue['summary']
        : substr(strip_tags($bodyValue['value'] ?? ''), 0, 150) . '...';
    }

    return [
      'id' => $node->id(),
      'title' => $node->getTitle(),
      'url' => $node->toUrl()->toString(),
      'created' => $node->getCreatedTime(),
      'summary' => $summary,
      'type' => $node->bundle(),
      'author' => $node->getOwner()->getDisplayName(),
    ];
  }

}
