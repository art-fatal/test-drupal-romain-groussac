<?php

namespace Drupal\Tests\seo_block\Unit\Service;

use Drupal\Tests\UnitTestCase;
use Drupal\seo_block\Service\SeoCalculator;
use Drupal\node\NodeInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Tests unitaires pour le service SeoCalculator.
 *
 * @group seo_block
 * @coversDefaultClass \Drupal\seo_block\Service\SeoCalculator
 */
class SeoCalculatorTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * Le service SeoCalculator à tester.
   *
   * @var \Drupal\seo_block\Service\SeoCalculator
   */
  protected $seoCalculator;

  /**
   * Le mock du gestionnaire d'entités.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->seoCalculator = new SeoCalculator($this->entityTypeManager->reveal());
  }

  /**
   * Teste le calcul du score SEO avec un titre optimal.
   *
   * @covers ::calculateSeoScore
   */
  public function testCalculateSeoScoreWithOptimalTitle() {
    $node = $this->createMockNode([
      'title' => 'Un titre optimal de 35 caractères pour le SEO',
      'body' => 'Un contenu riche avec plus de 1000 caractères pour tester le score SEO. Ce contenu devrait donner un bon score car il est suffisamment long et pertinent pour les moteurs de recherche.',
      'path' => ['alias' => '/mon-article-seo'],
      'field_meta_description' => 'Une meta description optimale entre 120 et 160 caractères pour un bon affichage dans les résultats de recherche.',
      'field_image' => ['image1.jpg', 'image2.jpg'],
      'field_keywords' => ['seo', 'drupal', 'optimisation'],
    ]);

    $score = $this->seoCalculator->calculateSeoScore($node);

    // Score attendu : 20 (titre) + 30 (contenu) + 15 (URL) + 20 (meta) + 10 (images) + 10 (mots-clés) = 105
    // Mais limité à 100
    $this->assertEquals(100, $score);
  }

  /**
   * Teste le calcul du score SEO avec un titre trop court.
   *
   * @covers ::calculateSeoScore
   */
  public function testCalculateSeoScoreWithShortTitle() {
    $node = $this->createMockNode([
      'title' => 'Titre court',
      'body' => 'Contenu minimal.',
    ]);

    $score = $this->seoCalculator->calculateSeoScore($node);

    // Score attendu : 5 (titre) + 10 (contenu) = 15
    $this->assertEquals(15, $score);
  }

  /**
   * Teste le calcul du score SEO avec un nœud vide.
   *
   * @covers ::calculateSeoScore
   */
  public function testCalculateSeoScoreWithEmptyNode() {
    $node = $this->createMockNode([
      'title' => '',
      'body' => '',
    ]);

    $score = $this->seoCalculator->calculateSeoScore($node);

    // Score attendu : 0
    $this->assertEquals(0, $score);
  }


  /**
   * Teste le calcul du score moyen.
   *
   * @covers ::calculateAverageSeoScore
   */
  public function testCalculateAverageSeoScore() {
    $node1 = $this->createMockNode(['title' => 'Titre optimal de 35 caractères', 'body' => 'Contenu riche']);
    $node2 = $this->createMockNode(['title' => 'Court', 'body' => 'Court']);

    $nodes = [$node1, $node2];

    $average = $this->seoCalculator->calculateAverageSeoScore($nodes);

    // Score moyen attendu : (50 + 15) / 2 = 32.5 arrondi à 33
    $this->assertEquals(33, $average);
  }

  /**
   * Teste le calcul du score moyen avec un tableau vide.
   *
   * @covers ::calculateAverageSeoScore
   */
  public function testCalculateAverageSeoScoreWithEmptyArray() {
    $average = $this->seoCalculator->calculateAverageSeoScore([]);

    $this->assertEquals(0, $average);
  }

  /**
   * Crée un mock de nœud avec les données spécifiées.
   *
   * @param array $data
   *   Les données du nœud.
   *
   * @return \Drupal\node\NodeInterface
   *   Le mock du nœud.
   */
  protected function createMockNode(array $data) {
    $node = $this->prophesize(NodeInterface::class);

    // Mock du titre
    $node->getTitle()->willReturn($data['title'] ?? '');

    // Mock du corps
    if (isset($data['body'])) {
      $bodyField = $this->prophesize(FieldItemListInterface::class);
      $bodyField->isEmpty()->willReturn(empty($data['body']));

      if (!empty($data['body'])) {
        $bodyItem = $this->prophesize(FieldItemInterface::class);
        $bodyItem->getValue()->willReturn(['value' => $data['body']]);
        $bodyField->first()->willReturn($bodyItem->reveal());
      }

      $node->hasField('body')->willReturn(true);
      $node->get('body')->willReturn($bodyField->reveal());
    } else {
      $node->hasField('body')->willReturn(false);
    }

    // Mock du chemin
    if (isset($data['path'])) {
      $pathField = $this->prophesize(FieldItemListInterface::class);
      $pathField->isEmpty()->willReturn(empty($data['path']['alias']));

      if (!empty($data['path']['alias'])) {
        $pathItem = $this->prophesize(FieldItemInterface::class);
        $pathItem->getValue()->willReturn($data['path']);
        $pathField->first()->willReturn($pathItem->reveal());
      }

      $node->hasField('path')->willReturn(true);
      $node->get('path')->willReturn($pathField->reveal());
    } else {
      $node->hasField('path')->willReturn(false);
    }

    // Mock de la meta description
    if (isset($data['field_meta_description'])) {
      $metaField = $this->prophesize(FieldItemListInterface::class);
      $metaField->isEmpty()->willReturn(empty($data['field_meta_description']));

      if (!empty($data['field_meta_description'])) {
        $metaItem = $this->prophesize(FieldItemInterface::class);
        $metaItem->getValue()->willReturn(['value' => $data['field_meta_description']]);
        $metaField->first()->willReturn($metaItem->reveal());
      }

      $node->hasField('field_meta_description')->willReturn(true);
      $node->get('field_meta_description')->willReturn($metaField->reveal());
    } else {
      $node->hasField('field_meta_description')->willReturn(false);
    }

    // Mock des images
    if (isset($data['field_image'])) {
      $imageField = $this->prophesize(FieldItemListInterface::class);
      $imageField->isEmpty()->willReturn(empty($data['field_image']));

      if (!empty($data['field_image'])) {
        $imageItems = [];
        foreach ($data['field_image'] as $image) {
          $imageItem = $this->prophesize(FieldItemInterface::class);
          $imageItem->isEmpty()->willReturn(false);
          $imageItems[] = $imageItem->reveal();
        }
        $imageField->willIterateAs($imageItems);
      }

      $node->hasField('field_image')->willReturn(true);
      $node->get('field_image')->willReturn($imageField->reveal());
    } else {
      $node->hasField('field_image')->willReturn(false);
    }

    // Mock des mots-clés
    if (isset($data['field_keywords'])) {
      $keywordsField = $this->prophesize(FieldItemListInterface::class);
      $keywordsField->isEmpty()->willReturn(empty($data['field_keywords']));
      $keywordsField->count()->willReturn(count($data['field_keywords']));

      $node->hasField('field_keywords')->willReturn(true);
      $node->get('field_keywords')->willReturn($keywordsField->reveal());
    } else {
      $node->hasField('field_keywords')->willReturn(false);
    }

    return $node->reveal();
  }

}
