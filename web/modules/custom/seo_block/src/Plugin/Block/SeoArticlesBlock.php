<?php

namespace Drupal\seo_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\seo_block\Service\SeoCalculator;
use Drupal\seo_block\Repository\ContentRepository;

/**
 * @Block(
 *   id = "seo_articles_block",
 *   admin_label = @Translation("SEO Articles Block"),
 *   category = @Translation("SEO")
 * )
 */
class SeoArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entityTypeManager;
  protected $configFactory;
  protected $seoCalculator;
  protected $contentRepository;

  public function __construct(
    array $configuration, 
    $plugin_id, 
    $plugin_definition, 
    EntityTypeManagerInterface $entity_type_manager, 
    ConfigFactoryInterface $config_factory,
    SeoCalculator $seo_calculator,
    ContentRepository $content_repository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->seoCalculator = $seo_calculator;
    $this->contentRepository = $content_repository;
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
      $container->get('config.factory'),
      $container->get('seo_block.seo_calculator'),
      $container->get('seo_block.content_repository')
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
        'max-age' => 300,
        'tags' => [
          'node_list',
          'config:seo_block.settings',
        ],
        'contexts' => ['user.permissions'],
      ],
    ];
  }

  protected function getLatestArticles() {
    $config = $this->configFactory->get('seo_block.settings');
    $articlesCount = $config->get('articles_count') ?: 5;

    // Utiliser le repository pour récupérer les articles
    $articles = $this->contentRepository->getLatestArticles($articlesCount, 'article');

    // Ajouter les scores SEO à chaque article
    foreach ($articles as &$article) {
      try {
        $node = $this->entityTypeManager->getStorage('node')->load($article['id']);
        if ($node) {
          $article['seo_score'] = $this->seoCalculator->calculateSeoScore($node);
        } else {
          $article['seo_score'] = 0;
        }
      } catch (\Exception $e) {
        $article['seo_score'] = 0;
      }
    }

    return $articles;
  }

}
