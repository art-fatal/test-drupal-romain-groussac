<?php

namespace Drupal\seo_block\Service;

use Drupal;
use Drupal\Core\Messenger\MessengerInterface;
use Exception;

class CacheService {

  /**
   * @var MessengerInterface
   */
  protected $messenger;

  public function __construct(
    MessengerInterface $messenger
  ) {
    $this->messenger = $messenger;
  }

  public function clearAllCaches(): array {
    try {
      drupal_flush_all_caches();

      Drupal::service('cache_tags.invalidator')->invalidateTags(['*']);
      Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
      Drupal::service('theme.registry')->reset();
      Drupal::service('twig')->invalidate();

      $this->messenger->addStatus('Cache vidé avec succès');

      return [
        'success' => true,
        'message' => 'Cache vidé avec succès',
        'timestamp' => date('d/m/Y H:i:s'),
      ];

    } catch (Exception $e) {
      $this->messenger->addError('Erreur lors de la purge du cache: ' . $e->getMessage());

      return [
        'success' => false,
        'message' => 'Erreur lors de la purge du cache: ' . $e->getMessage(),
        'timestamp' => date('d/m/Y H:i:s'),
      ];
    }
  }
}
