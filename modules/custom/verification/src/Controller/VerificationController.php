<?php

namespace Drupal\verification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\verification\Service\VerificationApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for permit verification.
 */
class VerificationController extends ControllerBase {

  /**
   * The verification API client.
   *
   * @var \Drupal\verification\Service\VerificationApiClient
   */
  protected $verificationApiClient;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a VerificationController object.
   *
   * @param \Drupal\verification\Service\VerificationApiClient $verification_api_client
   *   The verification API client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(VerificationApiClient $verification_api_client, RequestStack $request_stack) {
    $this->verificationApiClient = $verification_api_client;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('verification.api_client'),
      $container->get('request_stack')
    );
  }

  /**
   * Displays the permit verification form and results.
   *
   * @return array
   *   Render array for the verification page.
   */
  public function content() {
    $permit_data = null;
    $error_message = null;

    if ($this->requestStack->getCurrentRequest()->isMethod('POST')) {
      $request = $this->requestStack->getCurrentRequest();
      $nif = trim($request->request->get('nif')); // Just trim whitespace, keep dashes
      
      if (!empty($nif)) {
        try {
          $data = $this->verificationApiClient->verifyPermit($nif);
          if (isset($data['data'])) {
            $permit_data = $data['data'];
          } else {
            $error_message = $this->t('Aucun permis trouvé avec ce numéro.');
          }
        }
        catch (\Exception $e) {
          $error_message = $this->t('Une erreur est survenue lors de la connexion au serveur.');
        }
      }
    }

    return [
      '#theme' => 'verification_permit',
      '#permit_data' => $permit_data,
      '#error_message' => $error_message,
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'verification/verification',
        ],
      ],
    ];
  }

}
