<?php

namespace Drupal\cyber_duck_spotify\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\cyber_duck_spotify\Service\SpotifyClientInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a Spotify artists block.
 *
 * @Block(
 *   id = "cyber_duck_spotify_artists",
 *   admin_label = @Translation("Spotify artists"),
 *   category = @Translation("Cyber Duck"),
 * )
 */
class SpotifyArtistsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Spotify client.
   *
   * @var \Drupal\cyber_duck_spotify\Service\SpotifyClient
   */
  protected $spotifyClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SpotifyClientInterface $spotifyClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->spotifyClient = $spotifyClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cyber_duck_spotify.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $count = $config['artist_count'];
    $artists = [];

    // Seems there isnt a way of a random artist search, so randomize it in
    // code.
    // 65 = letter A. 65+25 = Z. Which gives the full alphabet range.
    $search_term = chr(65 + rand(0, 25));

    $results = $this->spotifyClient->searchSpotifyApi($search_term, 'artist', $count);

    if (!empty($results)) {
      $items = $results->artists->items;

      foreach ($items as $item) {
        $artists[] = [
          'name' => $item->name,
          'id' => $item->id,
        ];
      }
    }

    $build = [
      '#theme' => 'cyber_duck_spotify_block',
      '#artists' => $artists,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['artist_count'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'data-type' => 'number',
      ],
      '#maxlength' => 2,
      '#title' => $this->t('Artist display count'),
      '#description' => $this->t('Select the max number of artists to display in this block.'),
      '#default_value' => isset($config['artist_count']) ? $config['artist_count'] : [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $artist_count = $form_state->getValue('artist_count');

    if (!is_numeric($artist_count) || $artist_count > 20) {
      $form_state->setErrorByName('artist_count', $this->t('Count must be a number and less than or equal to 20'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('artist_count', $form_state->getValue('artist_count'));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 30;
  }

}
