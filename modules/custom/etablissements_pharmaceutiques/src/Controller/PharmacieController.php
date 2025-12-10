<?php

namespace Drupal\etablissements_pharmaceutiques\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for pharmaceutical establishments.
 */
class PharmacieController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new PharmacieController.
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
   * Lists all pharmacies.
   *
   * @return array
   *   Render array for the pharmacy list page.
   */
  public function listPharmacies() {
    // Add a link to add new pharmacy
    $build['add_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Ajouter une pharmacie'),
      '#url' => Url::fromRoute('etablissements_pharmaceutiques.add'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    try {
      // Get pharmacies from database
      $query = $this->database->select('etablissements_pharmaceutiques', 'ep');
      $query->fields('ep');
      $query->orderBy('name', 'ASC');
      
      // Debug query
      \Drupal::logger('etablissements_pharmaceutiques')->notice('SQL Query: @query', [
        '@query' => $query->__toString()
      ]);
      
      $pharmacies = $query->execute()->fetchAll();
      
      // Debug results
      \Drupal::logger('etablissements_pharmaceutiques')->notice('Found @count pharmacies', [
        '@count' => count($pharmacies)
      ]);
    }
    catch (\Exception $e) {
      \Drupal::logger('etablissements_pharmaceutiques')->error('Error fetching pharmacies: @error', [
        '@error' => $e->getMessage()
      ]);
      $pharmacies = [];
    }

    // Build the table
    $build['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Nom'),
        $this->t('Adresse'),
        $this->t('Téléphone'),
        $this->t('Email'),
      ],
      '#empty' => $this->t('Aucune pharmacie trouvée.'),
    ];

    foreach ($pharmacies as $pharmacy) {
      $build['table'][] = [
        'name' => ['#markup' => $pharmacy->name],
        'address' => ['#markup' => $pharmacy->address],
        'phone' => ['#markup' => $pharmacy->phone],
        'email' => ['#markup' => $pharmacy->email ?: '-'],
      ];
    }

    return $build;
  }

}
