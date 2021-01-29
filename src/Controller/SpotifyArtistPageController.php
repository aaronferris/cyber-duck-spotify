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
   * Get the Artist page contents.
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
    $artist = [];
    // If a user has routed to the artist page via the block, an ID will be
    // present in the URL as a query parameter. We use this for a more
    // accurate search if available. If the ID isn't available the page
    // should revert to provide a earch based on artist name instead.
    // @todo dependency injection.
    $artist_id = $request->query->get('id');
    $result = $this->spotifyClient->searchSpotifyApi($artist_name, 'artist', 1, $artist_id);

    if (!empty($result)) {
      if (empty($artist_id)) {
        // If no ID has been provided the Spotify API returns an array of
        // a single artist. Handle that difference in structure here.
        $result = $result->artists->items[0];
      }

      $artist = [
        'name' => $result->name,
        'id' => $result->id,
        'genres' => implode(',', $result->genres),
        'image' => $result->images[0]->url,
        'followers' => $result->followers->total,
        'spotify_external_url' => $result->external_urls->spotify,
      ];
    }

    $build = [
      '#theme' => 'cyber-duck-spotify-page',
      '#artist' => $artist,
    ];

    return $build;
  }

}
