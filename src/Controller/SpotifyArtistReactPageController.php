<?php

namespace Drupal\cyber_duck_spotify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Spotify artist page controller.
 */
class SpotifyArtistReactPageController extends ControllerBase {
  /**
   * Get the Artist page content to handle a React app.
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
    $build = [
      '#theme' => 'cyber_duck_spotify_react_page',
      '#attached' => [
        'drupalSettings' => [
          'artist_name' => $artist_name,
          'artist_id' => $request->query->get('id'),
        ],
      ],
    ];

    return $build;
  }

}
