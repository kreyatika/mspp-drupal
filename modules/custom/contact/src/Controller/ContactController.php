<?php

namespace Drupal\contact\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the contact page.
 */
class ContactController extends ControllerBase {

  /**
   * Renders the contact page.
   *
   * @return array
   *   Render array for the contact page.
   */
  public function content() {
    return [
      '#theme' => 'contact_page',
      '#attached' => [
        'library' => [
          'contact/contact',
        ],
      ],
    ];
  }

}
