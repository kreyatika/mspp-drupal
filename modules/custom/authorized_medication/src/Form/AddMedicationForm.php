<?php

namespace Drupal\authorized_medication\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding a new medication.
 */
class AddMedicationForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new AddMedicationForm.
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
    return 'authorized_medication_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nom du médicament'),
      '#required' => TRUE,
    ];

    $form['shape'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forme'),
      '#required' => TRUE,
    ];

    $form['form'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
    ];

    $form['dosage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dosage'),
      '#required' => TRUE,
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
    try {
      $id = $this->database->insert('authorized_medication')
        ->fields([
          'name' => $form_state->getValue('name'),
          'shape' => $form_state->getValue('shape'),
          'form' => $form_state->getValue('form'),
          'dosage' => $form_state->getValue('dosage'),
          'created' => time(),
          'changed' => time(),
        ])
        ->execute();

      if ($id) {
        $this->messenger()->addStatus($this->t('Le médicament a été ajouté avec succès.'));
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Une erreur est survenue lors de l\'enregistrement.'));
    }
    
    $form_state->setRedirect('authorized_medication.list');
  }

}
