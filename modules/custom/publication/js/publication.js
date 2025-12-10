(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.publicationFilter = {
    attach: function (context, settings) {
      once('publication-filter', '#publication-filter-form', context).forEach(function (form) {
        $(form).on('submit', function (e) {
          e.preventDefault();
          // TODO: Implement filter functionality
          // This will be implemented when we add the filter backend functionality
        });
      });
    }
  };
})(jQuery, Drupal, once);
