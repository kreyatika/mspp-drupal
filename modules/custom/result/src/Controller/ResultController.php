<?php

namespace Drupal\result\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\result\Service\ResultApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for exam results.
 */
class ResultController extends ControllerBase {

  /**
   * The result API client.
   *
   * @var \Drupal\result\Service\ResultApiClient
   */
  protected $resultApiClient;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a ResultController object.
   *
   * @param \Drupal\result\Service\ResultApiClient $result_api_client
   *   The result API client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ResultApiClient $result_api_client, RequestStack $request_stack) {
    $this->resultApiClient = $result_api_client;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('result.api_client'),
      $container->get('request_stack')
    );
  }

  /**
   * Displays the exam result.
   *
   * @param string $order_number
   *   The order number.
   *
   * @return array
   *   Render array for the result page.
   */
  public function content($order_number = null) {
    $result = null;
    $error_message = null;
    $sessions = [];
    $categories = [];

    // Fetch sessions and categories from API
    try {
      $sessions_data = $this->resultApiClient->getExamSessions();
      if (isset($sessions_data['data'])) {
        $sessions = $sessions_data['data'];
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('result')->error('Failed to fetch sessions: @message', ['@message' => $e->getMessage()]);
    }

    try {
      $categories_data = $this->resultApiClient->getCategories();
      if (isset($categories_data['data'])) {
        $categories = $categories_data['data'];
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('result')->error('Failed to fetch categories: @message', ['@message' => $e->getMessage()]);
    }

    // Handle form submission
    if ($this->requestStack->getCurrentRequest()->isMethod('POST')) {
      $request = $this->requestStack->getCurrentRequest();
      $order_number = $request->request->get('numero_ordre');
      $category_id = $request->request->get('category');
      $session_id = $request->request->get('session');
      
      if (!empty($order_number) && !empty($category_id) && !empty($session_id)) {
        try {
          $data = $this->resultApiClient->getExamResult($order_number, $session_id, $category_id);
          if (isset($data['data'])) {
            $result = $data['data'];
          } else {
            $error_message = $this->t('Aucun résultat trouvé pour ce numéro d\'ordre.');
          }
        }
        catch (\Exception $e) {
          $error_message = $this->t('Une erreur est survenue lors de la connexion au serveur.');
        }
      } else {
        $error_message = $this->t('Veuillez remplir tous les champs requis.');
      }
    }

    $module_path = \Drupal::service('extension.list.module')->getPath('result');
    return [
      '#type' => 'inline_template',
      '#template' => file_get_contents($module_path . '/templates/result-exam.html.twig'),
      '#context' => [
        'result' => $result,
        'error_message' => $error_message,
        'sessions' => $sessions,
        'categories' => $categories,
      ],
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'result/result',
        ],
      ],
    ];
  }

}
