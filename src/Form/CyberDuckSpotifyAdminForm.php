<?php

namespace Drupal\cyber_duck_spotify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * @file
 * Cyber Duck Spotify admin.
 */

/**
 * The Spotify Admin form.
 */
class CyberDuckSpotifyAdminForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cyber_duck_spotify_admin.settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cyber_duck_spotify.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('cyber_duck_spotify.settings');

    $form['spotify_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API URL'),
      '#default_value' => $config->get('spotify_api_url'),
    ];

    $form['spotify_api_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API client ID'),
      '#default_value' => $config->get('spotify_api_client_id'),
    ];

    $form['spotify_api_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify API client secret'),
      '#default_value' => $config->get('spotify_api_client_secret'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = [
      'spotify_api_url',
      'spotify_api_client_id',
      'spotify_api_client_secret',
    ];

    $values = $form_state->getValues();
    foreach ($settings as $setting) {
      $this->config('cyber_duck_spotify.settings')
        ->set($setting, $values[$setting])
        ->save();
    }

    drupal_set_message($this->t("Spotify config saved."));
  }

}
