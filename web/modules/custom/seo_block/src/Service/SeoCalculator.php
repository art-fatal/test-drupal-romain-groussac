<?php

namespace Drupal\seo_block\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class SeoCalculator {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function calculateSeoScore(NodeInterface $node): int {
    $score = 0;

    $score += $this->calculateTitleScore($node);
    $score += $this->calculateContentScore($node);
    $score += $this->calculateUrlScore($node);
    $score += $this->calculateMetaDescriptionScore($node);
    $score += $this->calculateTagsScore($node);
    $score += $this->calculateImageScore($node);

    return min(100, $score);
  }

  protected function calculateTitleScore(NodeInterface $node): int {
    $title = $node->getTitle();
    $titleLength = strlen($title);

    if ($titleLength >= 30 && $titleLength <= 60) {
      return 20;
    }
    elseif ($titleLength >= 20 && $titleLength <= 70) {
      return 15;
    }
    elseif ($titleLength > 0) {
      return 5;
    }

    return 0;
  }

  protected function calculateContentScore(NodeInterface $node): int {
    if (!$node->hasField('body') || $node->get('body')->isEmpty()) {
      return 0;
    }

    $body = $node->get('body')->first()->getValue()['value'];
    $contentLength = strlen(strip_tags($body));

    if ($contentLength > 1000) {
      return 30;
    }
    elseif ($contentLength > 300) {
      return 20;
    }
    elseif ($contentLength > 100) {
      return 10;
    }

    return 0;
  }

  protected function calculateUrlScore(NodeInterface $node): int {
    if (!$node->hasField('path') || $node->get('path')->isEmpty()) {
      return 0;
    }

    $path = $node->get('path')->first()->getValue();
    if (!empty($path['alias'])) {
      return 15;
    }

    return 0;
  }

  protected function calculateMetaDescriptionScore(NodeInterface $node): int {
    if (!$node->hasField('field_meta_description') || $node->get('field_meta_description')->isEmpty()) {
      return 0;
    }

    $metaDescription = $node->get('field_meta_description')->first()->getValue()['value'];
    $descriptionLength = strlen($metaDescription);

    if ($descriptionLength >= 120 && $descriptionLength <= 160) {
      return 20;
    }
    elseif ($descriptionLength >= 80 && $descriptionLength <= 200) {
      return 15;
    }
    elseif ($descriptionLength > 0) {
      return 5;
    }

    return 0;
  }


  protected function calculateImageScore(NodeInterface $node): int {
    if (!$node->hasField('field_image') || $node->get('field_image')->isEmpty()) {
      return 0;
    }

    $images = $node->get('field_image');
    $score = 0;

    foreach ($images as $image) {
      if (!$image->isEmpty()) {
        $score += 5;
      }
    }

    return min(15, $score);
  }


  protected function calculateTagsScore(NodeInterface $node): int {
    if (!$node->hasField('field_tags') || $node->get('field_tags')->isEmpty()) {
      return 0;
    }

    $tags = $node->get('field_tags');
    $score = 0;

    foreach ($tags as $tag) {
      if (!$tag->isEmpty()) {
        $score += 5;
      }
    }

    return min(15, $score);
  }

  public function calculateAverageSeoScore(array $nodes): int {
    if (empty($nodes)) {
      return 0;
    }

    $totalScore = 0;
    foreach ($nodes as $node) {
      $totalScore += $this->calculateSeoScore($node);
    }

    return round($totalScore / count($nodes));
  }
}
