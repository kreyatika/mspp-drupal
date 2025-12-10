(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.verificationNifFormat = {
    attach: function (context, settings) {
      once('nif-format', '#nif', context).forEach(function(element) {
        $(element).on('input', function() {
          let value = this.value.replace(/[^0-9]/g, '');
          
          if (value.length > 9) {
            // Format as xxx-xxx-xxx-x
            value = value.slice(0, 9) + '-' + value.slice(9);
            // Add dashes after every 3 digits for the first 9 digits
            value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
          } else if (value.length > 6) {
            // Format as xxx-xxx-xxx
            value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
          } else if (value.length > 3) {
            // Format as xxx-xxx
            value = value.slice(0, 3) + '-' + value.slice(3);
          }
          
          // Limit to 10 digits total
          if (value.length > 13) {
            value = value.slice(0, 13);
          }
          
          this.value = value;
        });
      });

      // Keep the formatted input with dashes
    }
  };
})(jQuery, Drupal);
