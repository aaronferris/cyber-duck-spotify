<?php

namespace Drupal\cyber_duck_spotify\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * HTTP Client wrapper for querying Spotify.
 */
class SpotifyClient implements SpotifyClientInterface {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Cyber Duck Spotify config.
   *
   * @var \Drupal\Core\Config\CyberDuckSpotifyConfig
   */
  protected $config = NULL;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * SpotifyClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $http_client;
    $this->config = $config_factory->get('cyber_duck_spotify.settings');
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Gets authentication from the Spotify API.
   *
   * @return object|bool
   *   The authentication request response or false.
   */
  public function getAuth() {
    $spotify_api_client_id = $this->config->get('spotify_api_client_id');
    $spotify_api_client_secret = $this->config->get('spotify_api_client_secret');

    try {
      $authorization = $this->httpClient->request('POST', 'https://accounts.spotify.com/api/token', [
        'form_params' => [
          'grant_type' => 'client_credentials',
          'client_id' => $spotify_api_client_id,
          'client_secret' => $spotify_api_client_secret,
        ],
      ]);

      return json_decode($authorization->getBody());
    }
    catch (GuzzleException $e) {
      $this->loggerFactory->get('spotify_client')->error($e);
      return FALSE;
    }

  }

  /**
   * Loose search of the Spotify API.
   *
   * @param int $search_term
   *   The term to search.
   * @param string $search_type
   *   The search type, albums, artists, songs etc.
   * @param int $search_count
   *   The number of results to retrieve.
   * @param object $auth
   *   An optional auth result for multiple searches.
   *
   * @return object|bool
   *   Results or false.
   */
  public function searchSpotifyApi($search_term, $search_type, $search_count, $auth = '') {
    if (empty($auth)) {
      $auth = $this->getAuth();
    }

    if ($auth) {
      // Auth successful.
      $spotify_api_url = $this->config->get('spotify_api_url');
      try {
        $request = $this->httpClient->request('GET', $spotify_api_url . "search?q=$search_term&type=$search_type&limit=$search_count", [
          'headers' => [
            'Authorization' => $auth->token_type . ' ' . $auth->access_token,
          ],
        ]);

        return json_decode($request->getBody());
      }
      catch (GuzzleException $e) {
        $this->loggerFactory->get('spotify_client')->error($e);
        return FALSE;
      }
    }

    return FALSE;
  }

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
   * @return object|bool
   *   Results or false.
   */
  public function getArtistDatabyId($artist_id, $search_type, $auth = '') {
    if (empty($auth)) {
      $auth = $this->getAuth();
    }

    if ($auth) {
      $spotify_api_url = $this->config->get('spotify_api_url');
      // More specific search.
      $search_url = $spotify_api_url . "artists/$artist_id";

      // @todo more api search options.
      switch ($search_type) {
        case 'albums':
          // https://api.spotify.com/v1/artists/{artist_id}/albums.
          $search_url = $search_url . '/albums?limit=10&include_groups=album';
          break;

        case 'top-tracks':
          // https://api.spotify.com/v1/artists/{artist_id}/top-tracks.
          $search_url = $search_url . '/top-tracks';
          break;

        case 'related-artists':
          // https://api.spotify.com/v1/artists/{id}/related-artists'.
          $search_url = $search_url . '/related-artists';

      }

      try {
        $request = $this->httpClient->request('GET', $search_url, [
          'headers' => [
            'Authorization' => $auth->token_type . ' ' . $auth->access_token,
          ],
        ]);

        return json_decode($request->getBody());
      }
      catch (GuzzleException $e) {
        $this->loggerFactory->get('spotify_client')->error($e);
        return FALSE;
      }
    }

    return FALSE;
  }

}
