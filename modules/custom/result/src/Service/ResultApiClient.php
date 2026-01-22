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
  protected $baseUrl = 'https://dfpss.mspp.gouv.ht/api';

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
   * @param int $sessionId
   *   The session ID.
   * @param int $categoryId
   *   The category ID.
   *
   * @return array
   *   The exam result data.
   *
   * @throws \Exception
   */
  public function getExamResult($orderNumber, $sessionId, $categoryId) {
    try {
      $response = $this->httpClient->request('POST', "{$this->baseUrl}/exams/order-number", [
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'order_number' => $orderNumber,
          'session_id' => $sessionId,
          'category_id' => $categoryId,
        ],
      ]);
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Failed to fetch exam result: ' . $e->getMessage());
    }
  }

  /**
   * Fetches exam sessions from the API.
   *
   * @return array
   *   The exam sessions data.
   *
   * @throws \Exception
   */
  public function getExamSessions() {
    try {
      $response = $this->httpClient->request('GET', "{$this->baseUrl}/exams/sessions");
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Failed to fetch exam sessions: ' . $e->getMessage());
    }
  }

  /**
   * Fetches healthcare professional categories from the API.
   *
   * @return array
   *   The categories data.
   *
   * @throws \Exception
   */
  public function getCategories() {
    try {
      $response = $this->httpClient->request('GET', "{$this->baseUrl}/healthcare-professionals/categories");
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Failed to fetch categories: ' . $e->getMessage());
    }
  }

}
