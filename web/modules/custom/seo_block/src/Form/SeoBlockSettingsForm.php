<?php

namespace Drupal\seo_block\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure SEO Block settings for this site.
 */
class SeoBlockSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'seo_block_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['seo_block.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('seo_block.settings');

    $form['articles_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Nombre d\'articles à afficher'),
      '#description' => $this->t('Entrez le nombre d\'articles à afficher dans le bloc SEO (maximum 15).'),
      '#default_value' => $config->get('articles_count'),
      '#min' => 1,
      '#max' => 15,
      '#step' => 1,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    
    $articles_count = $form_state->getValue('articles_count');
    
    if ($articles_count < 1) {
      $form_state->setError($form['articles_count'], $this->t('Le nombre d\'articles doit être au moins égal à 1.'));
    }
    
    if ($articles_count > 15) {
      $form_state->setError($form['articles_count'], $this->t('Le nombre d\'articles ne peut pas dépasser 15.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('seo_block.settings')
      ->set('articles_count', $form_state->getValue('articles_count'))
      ->save();

    // Invalidate cache tags to refresh the block
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['config:seo_block.settings']);

    parent::submitForm($form, $form_state);
  }

} 