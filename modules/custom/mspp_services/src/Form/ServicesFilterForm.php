<?php

namespace Drupal\mspp_services\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for filtering services.
 */
class ServicesFilterForm extends FormBase {

  public function getFormId() {
    return 'mspp_services_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#method'] = 'get';
    $form['#action'] = \Drupal::request()->getRequestUri();
    $form['#cache'] = ['max-age' => 0];

    // Get current values from URL
    $current_search = \Drupal::request()->query->get('search');
    $current_categories = \Drupal::request()->query->all()['categories'] ?? [];

    // Search field
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => '<h6 class="card-title fw-bold mb-3">Rechercher par mots-clés</h6>',
      '#default_value' => $current_search,
      '#attributes' => [
        'class' => ['form-control'],
        'placeholder' => $this->t('Tapez un mot-clé...'),
      ],
    ];

    // Categories checkboxes with counts
    if ($form_state->has('categories') && $form_state->has('services')) {
      $categories = $form_state->get('categories');
      $services = $form_state->get('services');
      $options = [];
      $counts = [];

      // Count services per category
      foreach ($services as $service) {
        if (!empty($service['categories'])) {
          foreach ($service['categories'] as $cat) {
            $tid = $cat['tid'];
            if (!isset($counts[$tid])) {
              $counts[$tid] = 0;
            }
            $counts[$tid]++;
          }
        }
      }

      // Build options with counts
      foreach ($categories as $cat) {
        $count = isset($counts[$cat['tid']]) ? $counts[$cat['tid']] : 0;
        $options[$cat['tid']] = $cat['name'] . ' (' . $count . ')';
      }

      $form['categories'] = [
        '#type' => 'checkboxes',
        '#title' => '<h6 class="card-title fw-bold mt-3">Types</h6>',
        '#options' => $options,
        '#default_value' => array_map('strval', (array) $current_categories),
      ];
    }

    // Submit button
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filtrer'),
      '#attributes' => ['class' => ['btn', 'btn-primary mt-3']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    
    // Clean up search
    $search = isset($values['search']) ? trim($values['search']) : '';
    
    // Clean up categories
    $categories = [];
    if (isset($values['categories']) && is_array($values['categories'])) {
      $categories = array_filter($values['categories'], function($value) {
        return $value !== 0;
      });
    }
    
    // Build query
    $query = [];
    if ($search !== '') {
      $query['search'] = $search;
    }
    if (!empty($categories)) {
      $query['categories'] = array_keys($categories);
    }
    
    // Redirect with query parameters
    $form_state->setRedirect('mspp_services.page', [], ['query' => $query]);
  }
}