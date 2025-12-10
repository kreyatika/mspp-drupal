/**
 * @file
 * Main JavaScript file for Conatel theme with Bootstrap.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.conatelTheme = {
    attach: function (context, settings) {
      // Initialize Bootstrap tooltips
      $(context).find('[data-bs-toggle="tooltip"]').once('conatelTooltips').each(function () {
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
          new bootstrap.Tooltip(this);
        }
      });

      // Initialize Bootstrap popovers
      $(context).find('[data-bs-toggle="popover"]').once('conatelPopovers').each(function () {
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
          new bootstrap.Popover(this);
        }
      });

      // Add fade-in class to content elements
      $(context).find('.content .field').once('conatelFadeIn').addClass('fade-in');

      console.log('Conatel theme with Bootstrap loaded');
    }
  };

})(jQuery, Drupal);
