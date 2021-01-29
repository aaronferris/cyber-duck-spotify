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
   * @return array|bool
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
   * Search the Spotify API.
   *
   * @param int $search_term
   *   The term to search.
   * @param int $search_type
   *   The search type, albums, artists, songs etc.
   * @param int $search_count
   *   The number of results to retrieve.
   * @param string $artist_id
   *   An optional Spotify artist ID.
   *
   * @return array|bool
   *   An array of results or false.
   */
  public function searchSpotifyApi($search_term, $search_type, $search_count, $artist_id = '') {
    $auth = $this->getAuth();

    if ($auth) {
      // Auth successful.
      $spotify_api_url = $this->config->get('spotify_api_url');

      // Loose search.
      // Format: https://api.spotify.com/v1/search?q=''&type=''&limit=''.
      $search_url = $spotify_api_url . "search?q=$search_term&type=$search_type&limit=$search_count";

      if (!empty($artist_id)) {
        // More specific search.
        // Format: https://api.spotify.com/v1/artists/{artist_id}.
        $search_url = $spotify_api_url . "artists/$artist_id";
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
