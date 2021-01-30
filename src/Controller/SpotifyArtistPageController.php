<?php

namespace Drupal\cyber_duck_spotify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cyber_duck_spotify\Service\SpotifyClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   The content to be built.
   */
  public function content($artist_name, Request $request) {
    $artist = $albums = $results = [];
    // If a user has routed to the artist page via the block, an ID will be
    // present in the URL as a query parameter. We use this for a more
    // accurate search if available. If the ID isn't available the page
    // will revert to provide a search based on artist name instead.
    $artist_id = $request->query->get('id');
    $auth = $this->spotifyClient->getAuth();

    $search_types = [
      'artist',
      'albums',
    ];

    if ($auth) {
      if (!empty($artist_id)) {
        // More accurate search based on Spotify artist ID.
        foreach ($search_types as $search_type) {
          $results[$search_type] = $this->spotifyClient->getArtistDatabyId($artist_id, $search_type, $auth);
        }
      }
      else {
        // Loose search.
        // 1 = result count.
        $results['artist'] = $this->spotifyClient->searchSpotifyApi($artist_name, 'artist', 1);
        $id = $results['artist']->artists->items[0]->id;
        // Now we have the artist ID get further data.
        $results['albums'] = $this->spotifyClient->getArtistDatabyId($id, 'albums', $auth);
      }

      foreach ($search_types as $search_type) {
        if (empty($results[$search_type])) {
          // The API calls may return false, clean up that noise here.
          unset($results[$search_type]);
        }
      }

      if (!empty($results)) {
        if (empty($artist_id)) {
          // If no artist ID has been provided the Spotify search API
          // returns an array of a single artist. Handle that difference
          // in structure here.
          $result = $results['artist']->artists->items[0];
        }
        else {
          $result = $results['artist'];
        }

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
            $albums[] = [
              'name' => $album->name,
              'artwork' => $album->images[0]->url,
              'url' => $album->external_urls->spotify,
            ];
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
