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
   * @param string $search_type
   *   The search type, albums, artists, songs etc.
   * @param int $search_count
   *   The number of results to retrieve.
   *
   * @return array|bool
   *   An array of results or false.
   */
  public function searchSpotifyApi($search_term, $search_type, $search_count);

  /**
   * Get an artist data from ID.
   *
   * @param string $artist_id
   *   An optional Spotify artist ID.
   * @param string $search_type
   *   The search type, albums, artists, songs etc.
   * @param object $auth
   *   An optional auth result for multiple searches.
   *
   * @return array|bool
   *   An array of results or false.
   */
  public function getArtistDatabyId($artist_id, $search_type, $auth);

}
