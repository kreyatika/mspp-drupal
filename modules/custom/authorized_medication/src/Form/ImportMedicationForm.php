<?php

namespace Drupal\authorized_medication\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * Form for importing medications from CSV.
 */
class ImportMedicationForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new ImportMedicationForm.
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
    return 'authorized_medication_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Fichier CSV'),
      '#description' => $this->t('Sélectionnez un fichier CSV contenant les médicaments. Format: nom,forme,type,dosage'),
      '#upload_location' => 'public://csv_imports/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Importer'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_id = $form_state->getValue('csv_file')[0];
    $file = File::load($file_id);
    
    if ($file) {
      $filename = \Drupal::service('file_system')->realpath($file->getFileUri());
      
      if (($handle = fopen($filename, 'r')) !== FALSE) {
        $batch = [
          'title' => $this->t('Importation des médicaments...'),
          'operations' => [],
          'init_message' => $this->t('Démarrage de l\'importation'),
          'progress_message' => $this->t('Traitement ligne @current sur @total.'),
          'error_message' => $this->t('Une erreur est survenue pendant l\'importation.'),
        ];

        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
          $batch['operations'][] = [
            '\Drupal\authorized_medication\Form\ImportMedicationForm::importMedicationLine',
            [$data]
          ];
        }
        fclose($handle);

        batch_set($batch);
      }
      
      // Mark file as permanent
      $file->setPermanent();
      $file->save();
    }
  }

  /**
   * Batch operation callback for importing a single line.
   */
  public static function importMedicationLine($data, &$context) {
    try {
      if (count($data) >= 4) {
        \Drupal::database()->insert('authorized_medication')
          ->fields([
            'name' => $data[0],
            'shape' => $data[1],
            'form' => $data[2],
            'dosage' => $data[3],
            'created' => time(),
            'changed' => time(),
          ])
          ->execute();
        
        $context['results'][] = $data[0];
        $context['message'] = t('Import du médicament: @title', ['@title' => $data[0]]);
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('authorized_medication')->error('Erreur d\'importation pour @med: @error', [
        '@med' => $data[0] ?? 'unknown',
        '@error' => $e->getMessage(),
      ]);
    }
  }

}
