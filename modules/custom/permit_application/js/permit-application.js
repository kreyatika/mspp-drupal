(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.permitApplication = {
    attach: function (context, settings) {
      // NIF formatting - only numbers, auto-format with dashes
      var nifInputs = once('nif-format', '.nif-input, #edit-nif, #edit-personal-nif, input[name="personal[nif]"]', context);
      
      nifInputs.forEach(function(input) {
        $(input).on('input paste keyup', function(e) {
          var cursorPos = this.selectionStart;
          var oldValue = this.value;
          
          // Remove all non-numeric characters
          var value = this.value.replace(/[^0-9]/g, '');
          
          // Limit to 10 digits
          if (value.length > 10) {
            value = value.substring(0, 10);
          }
          
          // Format with dashes: 000-000-000-0
          var formatted = '';
          if (value.length > 0) {
            formatted = value.substring(0, 3);
            if (value.length > 3) {
              formatted += '-' + value.substring(3, 6);
            }
            if (value.length > 6) {
              formatted += '-' + value.substring(6, 9);
            }
            if (value.length > 9) {
              formatted += '-' + value.substring(9, 10);
            }
          }
          
          this.value = formatted;
          
          // Restore cursor position
          if (oldValue !== formatted) {
            var newCursorPos = cursorPos;
            if (cursorPos >= 3 && oldValue.charAt(3) !== '-' && formatted.charAt(3) === '-') newCursorPos++;
            if (cursorPos >= 7 && oldValue.charAt(7) !== '-' && formatted.charAt(7) === '-') newCursorPos++;
            if (cursorPos >= 11 && oldValue.charAt(11) !== '-' && formatted.charAt(11) === '-') newCursorPos++;
            this.setSelectionRange(newCursorPos, newCursorPos);
          }
        });
        
        // Prevent non-numeric input on keydown
        $(input).on('keydown', function(e) {
          if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 35, 36, 37, 39]) !== -1 ||
              ((e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88 || e.keyCode === 90) && (e.ctrlKey === true || e.metaKey === true))) {
            return;
          }
          if ((e.shiftKey || e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
          }
        });
      });

      // File upload validation
      var fileInputs = once('file-validate', 'input[type="file"]', context);
      fileInputs.forEach(function(input) {
        $(input).on('change', function() {
          var file = this.files[0];
          if (file) {
            var maxSize = $(this).data('max-size') || 5242880;
            var allowedTypes = $(this).attr('accept').split(',').map(function(t) { return t.trim(); });
            
            if (file.size > maxSize) {
              alert('Le fichier est trop volumineux. Taille maximale: ' + (maxSize / 1024 / 1024) + 'MB');
              this.value = '';
              return;
            }
            
            var fileExt = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(fileExt)) {
              alert('Type de fichier non autorisé. Formats acceptés: ' + allowedTypes.join(', '));
              this.value = '';
              return;
            }
          }
        });
      });

      // Form submission loading state
      var forms = once('form-submit', '#permit-application-form, form.permit-application-form', context);
      forms.forEach(function(form) {
        $(form).on('submit', function() {
          var $submitBtn = $(this).find('input[type="submit"]');
          $submitBtn.prop('disabled', true);
          $submitBtn.val('Envoi en cours...');
        });
      });
    }
  };

})(jQuery, Drupal, once);
