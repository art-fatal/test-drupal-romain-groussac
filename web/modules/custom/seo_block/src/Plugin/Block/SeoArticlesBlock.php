<?php

namespace Drupal\seo_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a block displaying the 5 latest articles with SEO score.
 *
 * @Block(
 *   id = "seo_articles_block",
 *   admin_label = @Translation("SEO Articles Block"),
 *   category = @Translation("SEO")
 * )
 */
class SeoArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SeoArticlesBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $articles = $this->getLatestArticles();

    return [
      '#theme' => 'seo_articles_block',
      '#articles' => $articles,
      '#attached' => [
        'library' => [
          'seo_block/seo-block',
        ],
      ],
      '#cache' => [
        'max-age' => 300, // Cache for 5 minutes
        'tags' => [
          'node_list',
          'config:seo_block.settings',
        ],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  /**
   * Get the latest published articles.
   *
   * @return array
   *   Array of article data with SEO scores.
   */
  protected function getLatestArticles() {
    // Get the configured number of articles
    $config = $this->configFactory->get('seo_block.settings');
    $articlesCount = $config->get('articles_count') ?: 5;
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', 'article')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, $articlesCount)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    if (empty($nids)) {
      return [];
    }

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $articles = [];

    foreach ($nodes as $node) {
      /** @var \Drupal\node\NodeInterface $node */
      $seo_score = $this->calculateSeoScore($node);

      $articles[] = [
        'title' => $node->getTitle(),
        'url' => $node->toUrl()->toString(),
        'created' => $node->getCreatedTime(),
        'seo_score' => $seo_score,
        'summary' => $node->get('body')->summary ?: substr(strip_tags($node->get('body')->value), 0, 150) . '...',
      ];
    }

    return $articles;
  }

  /**
   * Calculate SEO score for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to calculate SEO score for.
   *
   * @return int
   *   The SEO score (0-100).
   */
  protected function calculateSeoScore(NodeInterface $node) {
    $score = 0;

    // Check title length (optimal: 50-60 characters)
    $title = $node->getTitle();
    $title_length = strlen($title);
    if ($title_length >= 30 && $title_length <= 70) {
      $score += 20;
    } elseif ($title_length >= 20 && $title_length <= 80) {
      $score += 10;
    }

    // Check body content length (minimum 300 words recommended)
    $body = $node->get('body')->value;
    $word_count = str_word_count(strip_tags($body));
    if ($word_count >= 300) {
      $score += 25;
    } elseif ($word_count >= 150) {
      $score += 15;
    }

    // Check if node has a summary
    if (!empty($node->get('body')->summary)) {
      $score += 15;
    }

    // Check if node has tags (if taxonomy is available)
    if ($node->hasField('field_tags') && !$node->get('field_tags')->isEmpty()) {
      $score += 20;
    }

    // Check if node has an image (if image field is available)
    if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
      $score += 20;
    }

    return min(100, $score);
  }

}
