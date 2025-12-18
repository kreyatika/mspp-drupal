<?php

namespace Drupal\permit_application\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Permit Application routes.
 */
class PermitApplicationController extends ControllerBase {

  /**
   * Builds the permit application page.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm('Drupal\permit_application\Form\PermitApplicationForm');

    return [
      '#theme' => 'permit_application_page',
      '#form' => $form,
      '#attached' => [
        'library' => [
          'permit_application/permit_application',
        ],
      ],
    ];
  }

}
