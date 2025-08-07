<?php

namespace Drupal\seo_block\Service;

use Drupal;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\seo_block\Repository\ContentRepository;
use Exception;

class DashboardService {

  /**
   * @var ContentRepository
   */
  protected $contentRepository;

  /**
   * @var SeoCalculator
   */
  protected $seoCalculator;

  /**
   * @var MessengerInterface
   */
  protected $messenger;

  const DEFAULT_ITEMS_PER_PAGE = 5;

  public function __construct(
    ContentRepository $contentRepository,
    SeoCalculator $seoCalculator,
    MessengerInterface $messenger
  ) {
    $this->contentRepository = $contentRepository;
    $this->seoCalculator = $seoCalculator;
    $this->messenger = $messenger;
  }

  public function getDashboardData(int $page = 0, int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE): array {
    try {
      $itemsPerPage = $this->validateItemsPerPage($itemsPerPage);
      $page = max(0, $page);

      $contentData = $this->contentRepository->getPaginatedNodes($page, $itemsPerPage);

      $contentData['content'] = $this->addSeoScoresToContent($contentData['content']);

      $stats = $this->getDashboardStats();

      return [
        'content' => $contentData['content'],
        'pagination' => $contentData['pagination'],
        'stats' => $stats,
        'success' => true,
      ];

    } catch (Exception $e) {
      $this->messenger->addError('Erreur lors du chargement du dashboard: ' . $e->getMessage());

      return [
        'content' => [],
        'pagination' => [],
        'stats' => $this->getDefaultStats(),
        'success' => false,
        'error' => $e->getMessage(),
      ];
    }
  }

  protected function validateItemsPerPage(int $itemsPerPage): int {
    return min(max($itemsPerPage, 5), 100);
  }

  protected function addSeoScoresToContent(array $content): array {
    $enrichedContent = [];

    foreach ($content as $item) {
      try {
        $nodeStorage = Drupal::entityTypeManager()->getStorage('node');
        $node = $nodeStorage->load($item['id']);

        if ($node) {
          $item['seo_score'] = $this->seoCalculator->calculateSeoScore($node);
        } else {
          $item['seo_score'] = 0;
        }
      } catch (Exception $e) {
        $item['seo_score'] = 0;
      }

      $enrichedContent[] = $item;
    }

    return $enrichedContent;
  }

  protected function getDashboardStats(): array {
    try {
      $stats = $this->contentRepository->getDashboardStats();

      $publishedNodes = $this->contentRepository->getPublishedNodes(100);
      $stats['average_seo_score'] = $this->seoCalculator->calculateAverageSeoScore($publishedNodes);

      return $stats;

    } catch (Exception $e) {
      $this->messenger->addWarning('Impossible de calculer les statistiques: ' . $e->getMessage());
      return $this->getDefaultStats();
    }
  }

  protected function getDefaultStats(): array {
    return [
      'total_published' => 0,
      'total_drafts' => 0,
      'average_seo_score' => 0,
    ];
  }
}
