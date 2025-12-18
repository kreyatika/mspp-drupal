<?php

namespace Drupal\permit_application\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * Permit Application Form.
 */
class PermitApplicationForm extends FormBase {

protected ClientInterface $httpClient;
protected EntityTypeManagerInterface $entityTypeManager;

public function __construct(
  ClientInterface $http_client,
  EntityTypeManagerInterface $entity_type_manager
) {
  $this->httpClient = $http_client;
  $this->entityTypeManager = $entity_type_manager;
}

public static function create(ContainerInterface $container): self {
  return new static(
    $container->get('http_client'),
    $container->get('entity_type.manager')
  );
}

  public function getFormId(): string {
    return 'permit_application_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#attributes']['class'][] = 'permit-application-form';

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
      '#prefix' => '<div class="row mb-3"><div class="col-md-6">',
      '#suffix' => '</div>',
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#attributes' => ['class' => ['form-control']],
      '#prefix' => '<div class="col-md-6">',
      '#suffix' => '</div></div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => ['class' => ['btn', 'btn-primary', 'btn-lg']],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Add any validation if needed
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();

    // API payload
    $payload = [
      'first_name' => $values['first_name'],
      'last_name' => $values['last_name'],
    ];

    try {
      $api_endpoint = \Drupal::config('permit_application.settings')->get('api_endpoint') ?? 'https://api.example.com/permit-applications';
      
      $response = $this->httpClient->post($api_endpoint, [
        'json' => $payload,
        'headers' => [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ],
      ]);

      if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
        $this->messenger()->addStatus($this->t('Form submitted successfully.'));
        $form_state->setRedirect('<front>');
      }
      else {
        throw new \Exception('API returned status code: ' . $response->getStatusCode());
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('permit_application')->error('API submission error: @message', ['@message' => $e->getMessage()]);
      $this->messenger()->addError($this->t('An error occurred during submission. Please try again later.'));
    }
  }

}