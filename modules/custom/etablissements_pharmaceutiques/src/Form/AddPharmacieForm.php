<?php

namespace Drupal\etablissements_pharmaceutiques\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding a new pharmacy.
 */
class AddPharmacieForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new AddPharmacieForm.
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'etablissements_pharmaceutiques_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nom de la pharmacie'),
      '#required' => TRUE,
    ];

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Adresse'),
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Téléphone'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enregistrer'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    
    // Debug values
    \Drupal::logger('etablissements_pharmaceutiques')->notice('Form values: @values', [
      '@values' => print_r($values, TRUE)
    ]);
    
    try {
      // Insert into database
      $id = $this->database->insert('etablissements_pharmaceutiques')
        ->fields([
          'name' => $values['name'],
          'address' => $values['address'],
          'phone' => $values['phone'],
          'email' => $values['email'],
          'created' => time(),
          'changed' => time(),
        ])
        ->execute();

      if ($id) {
        \Drupal::logger('etablissements_pharmaceutiques')->notice('Successfully inserted pharmacy with ID: @id', [
          '@id' => $id
        ]);
        $this->messenger()->addStatus($this->t('La pharmacie a été ajoutée avec succès.'));
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('etablissements_pharmaceutiques')->error('Error inserting pharmacy: @error', [
        '@error' => $e->getMessage()
      ]);
      $this->messenger()->addError($this->t('Une erreur est survenue lors de l\'enregistrement: @error', [
        '@error' => $e->getMessage()
      ]));
    }
    
    $form_state->setRedirect('etablissements_pharmaceutiques.list');
  }

}
