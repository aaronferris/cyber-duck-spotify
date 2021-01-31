<?php

namespace Drupal\cyber_duck_spotify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cyber_duck_spotify\Service\SpotifyClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Spotify artist page controller.
 */
class SpotifyArtistPageController extends ControllerBase {

  /**
   * Spotify client.
   *
   * @var \Drupal\cyber_duck_spotify\Service\SpotifyClient
   */
  protected $spotifyClient;

  /**
   * Spotify Artist Page constructor.
   */
  public function __construct(SpotifyClientInterface $spotifyClient) {
    $this->spotifyClient = $spotifyClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cyber_duck_spotify.client')
    );
  }

  /**
   * Get the Artist page content.
   *
   * @param string $artist_name
   *   The artist name pulled in from the url - artist/{artist_name}.
   *
   * @return array
   *   The content to be built.
   */
  public function content($artist_name) {
    $artist = $albums = $results = [];
    $auth = $this->spotifyClient->getAuth();

    $search_types = [
      'artist',
      'albums',
    ];

    if ($auth) {
      // First get the artist from the artist name.
      $results['artist'] = $this->spotifyClient->searchSpotifyApi($artist_name, 'artist', 1, $auth);
      $id = $results['artist']->artists->items[0]->id;
      // Now we have the artist ID get further data.
      $results['albums'] = $this->spotifyClient->getArtistDatabyId($id, 'albums', $auth);

      foreach ($search_types as $search_type) {
        if (empty($results[$search_type])) {
          // The API calls may return false, clean up that noise here.
          unset($results[$search_type]);
        }
      }

      if (!empty($results)) {
        if (!empty($results['artist']->artists->items[0])) {
          $result = $results['artist']->artists->items[0];

          // Build the artist data.
          $artist = [
            'name' => $result->name,
            'id' => $result->id,
            'genres' => implode(',', $result->genres),
            'image' => $result->images[0]->url,
            'followers' => $result->followers->total,
            'spotify_external_url' => $result->external_urls->spotify,
          ];

          // Add any album data.
          if (!empty($results['albums'])) {
            foreach ($results['albums']->items as $album) {
              $albums[$album->name] = [
                'artwork' => $album->images[0]->url,
                'url' => $album->external_urls->spotify,
              ];
            }
          }
        }
      }
    }

    $build = [
      '#theme' => 'cyber_duck_spotify_page',
      '#artist' => $artist,
      '#albums' => $albums,
    ];

    return $build;
  }

}
