<?php

namespace Drupal\result\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for fetching exam results from the external API.
 */
class ResultApiClient {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The base URL for the API.
   *
   * @var string
   */
  protected $baseUrl = 'http://178.128.75.110/api';

  /**
   * Constructs a ResultApiClient object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Fetches exam result by order number.
   *
   * @param string $orderNumber
   *   The order number to fetch results for.
   *
   * @return array
   *   The exam result data.
   *
   * @throws \Exception
   */
  public function getExamResult($orderNumber) {
    try {
      $response = $this->httpClient->request('GET', "{$this->baseUrl}/exams/order-number/{$orderNumber}");
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Failed to fetch exam result: ' . $e->getMessage());
    }
  }

}
