<?php

namespace Drupal\schools\Service;

use Drupal\Core\Http\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for fetching schools data from external APIs.
 */
class SchoolsApiService {

  /**
   * The HTTP client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Base URL for the API.
   *
   * @var string
   */
  protected $baseUrl = 'https://dfpss.mspp.gouv.ht/api';

  /**
   * Constructs a SchoolsApiService object.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The HTTP client factory.
   */
  public function __construct(ClientFactory $http_client_factory) {
    $this->httpClientFactory = $http_client_factory;
  }

  /**
   * Fetches data from an API endpoint.
   */
  protected function fetchApiData($endpoint) {
    try {
      $client = $this->httpClientFactory->fromOptions([
        'verify' => false,
        'timeout' => 30,
      ]);
      
      $url = $this->baseUrl . '/' . $endpoint;
      \Drupal::logger('schools')->notice('Fetching from URL: @url', ['@url' => $url]);
      
      $response = $client->get($url);
      $body = (string) $response->getBody();
      \Drupal::logger('schools')->notice('Raw API response: @data', ['@data' => $body]);
      
      $data = json_decode($body, TRUE);
      if (json_last_error() !== JSON_ERROR_NONE) {
        \Drupal::logger('schools')->error('JSON decode error: @error', ['@error' => json_last_error_msg()]);
        return [];
      }
      
      \Drupal::logger('schools')->notice('Decoded data: <pre>@data</pre>', ['@data' => print_r($data, TRUE)]);
      
      if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
        $result = $data['data'];
        \Drupal::logger('schools')->notice('Returning data: <pre>@data</pre>', ['@data' => print_r($result, TRUE)]);
        return $result;
      }
      
      return [];
    }
    catch (GuzzleException $e) {
      \Drupal::logger('schools')->error('API request failed: @message', [
        '@message' => $e->getMessage(),
      ]);
      return [];
    }
  }

  /**
   * Gets all schools data.
   */
  public function getSchools() {
    return $this->fetchApiData('schools');
  }

  /**
   * Gets all departments data.
   */
  public function getDepartments() {
    return $this->fetchApiData('school-departments');
  }

  /**
   * Gets all healthcare programs data.
   */
  public function getPrograms() {
    return $this->fetchApiData('healthcare-programs');
  }
}
