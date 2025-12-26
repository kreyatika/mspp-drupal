<?php

namespace Drupal\authorized_medication\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for authorized medications.
 */
class MedicationController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new MedicationController.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Lists all medications.
   *
   * @return array
   *   Render array for the medication list page.
   */
  public function listMedications() {
    $build['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['medication-actions'],
      ],
    ];

    $build['actions']['add_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Ajouter un médicament'),
      '#url' => Url::fromRoute('authorized_medication.add'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    $build['actions']['import_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Importer CSV'),
      '#url' => Url::fromRoute('authorized_medication.import'),
      '#attributes' => [
        'class' => ['button', 'button--secondary'],
        'style' => 'margin-left: 10px;',
      ],
    ];

    try {
      $query = $this->database->select('authorized_medication', 'am');
      $query->fields('am');
      $query->orderBy('name', 'ASC');
      
      $medications = $query->execute()->fetchAll();

      $build['table'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Nom'),
          $this->t('Forme'),
          $this->t('Type'),
          $this->t('Dosage'),
        ],
        '#empty' => $this->t('Aucun médicament trouvé.'),
      ];

      foreach ($medications as $medication) {
        $build['table'][] = [
          'name' => ['#markup' => $medication->name],
          'shape' => ['#markup' => $medication->shape],
          'form' => ['#markup' => $medication->form],
          'dosage' => ['#markup' => $medication->dosage],
        ];
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Une erreur est survenue lors de la récupération des médicaments.'));
      $build['table'] = [
        '#markup' => $this->t('Impossible de charger la liste des médicaments.'),
      ];
    }

    return $build;
  }

  /**
   * Display public list of medications.
   *
   * @return array
   *   Render array for the public medications page.
   */
  public function publicList() {
    try {
      $limit = 20;
      $offset = 0;

      $query = $this->database->select('authorized_medication', 'am')
        ->fields('am')
        ->orderBy('name', 'ASC')
        ->range($offset, $limit);

      $medications = $query->execute()->fetchAll();

      $total_query = $this->database->select('authorized_medication', 'am')
        ->countQuery();
      $total = $total_query->execute()->fetchField();

      return [
        '#theme' => 'medications_public_list',
        '#medications' => $medications,
        '#total' => $total,
        '#limit' => $limit,
        '#attached' => [
          'library' => [
            'authorized_medication/medications',
          ],
          'drupalSettings' => [
            'authorized_medication' => [
              'total' => $total,
              'loaded' => count($medications),
              'limit' => $limit,
              'loadMoreUrl' => Url::fromRoute('authorized_medication.load_more')->toString(),
            ],
          ],
        ],
      ];
    }
    catch (\Exception $e) {
      return [
        '#markup' => $this->t('Impossible de charger la liste des médicaments.'),
      ];
    }
  }

  /**
   * AJAX callback to load more medications.
   */
  public function loadMore() {
    $offset = \Drupal::request()->query->get('offset', 0);
    $limit = \Drupal::request()->query->get('limit', 20);

    try {
      $query = $this->database->select('authorized_medication', 'am')
        ->fields('am')
        ->orderBy('name', 'ASC')
        ->range($offset, $limit);

      $medications = $query->execute()->fetchAll();

      $rows = [];
      foreach ($medications as $medication) {
        $rows[] = [
          'name' => $medication->name,
          'shape' => $medication->shape,
          'form' => $medication->form,
          'dosage' => $medication->dosage,
        ];
      }

      return new \Symfony\Component\HttpFoundation\JsonResponse([
        'success' => TRUE,
        'medications' => $rows,
        'loaded' => count($rows),
      ]);
    }
    catch (\Exception $e) {
      return new \Symfony\Component\HttpFoundation\JsonResponse([
        'success' => FALSE,
        'message' => $this->t('Erreur lors du chargement des médicaments.'),
      ], 500);
    }
  }

}
