<?php

namespace Drupal\cyber_duck_spotify\Service;

/**
 * Client interface for Spotify.
 */
interface SpotifyClientInterface {

  /**
   * Gets authentication from the Spotify API.
   *
   * @return array|bool
   *   The authentication request response or false.
   */
  public function getAuth();

  /**
   * Search the Spotify API.
   *
   * @param int $search_term
   *   The term to search.
   * @param int $search_type
   *   The search type, albums, artists, songs etc.
   * @param int $search_count
   *   The number of results to retrieve.
   * @param string $artist_id
   *   An artist ID.
   *
   * @return array|bool
   *   An array of results or false.
   */
  public function searchSpotifyApi($search_term, $search_type, $search_count, $artist_id);

}
