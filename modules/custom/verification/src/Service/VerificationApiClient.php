<?php

namespace Drupal\verification\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for verifying permits using the external API.
 */
class VerificationApiClient {

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
   * Constructs a VerificationApiClient object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Verifies a permit by NIF.
   *
   * @param string $nif
   *   The NIF to verify.
   *
   * @return array
   *   The permit data.
   *
   * @throws \Exception
   */
  public function verifyPermit($nif) {
    try {
      // Keep the dashes in the NIF
      $formattedNif = $nif; // No cleaning, keep as is with dashes
      $response = $this->httpClient->request('GET', "{$this->baseUrl}/verification/permit/{$formattedNif}");
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
      throw new \Exception('Failed to verify permit: ' . $e->getMessage());
    }
  }

}
