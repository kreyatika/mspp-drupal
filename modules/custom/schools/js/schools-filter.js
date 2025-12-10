(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.schoolsFilter = {
    attach: function (context, settings) {
      const itemsToShow = 10;
      const $schools = $('.school-card', context);
      
      // Initially hide schools beyond the first 10
      $schools.slice(itemsToShow).hide();
      
      // Show/hide Load More button based on visible items
      function updateLoadMoreButton() {
        const visibleItems = $('.school-card:visible', context).length;
        const totalItems = $('.school-card', context).not('.filtered-out').length;
        
        if (visibleItems < totalItems) {
          if ($('#load-more-btn', context).length === 0) {
            $('.col-lg-8', context).append('<div class="text-center mt-4"><button id="load-more-btn" class="btn btn-outline-primary">Afficher plus d\'écoles</button></div>');
          }
        } else {
          $('#load-more-btn', context).parent().remove();
        }
      }
      
      // Load More button click handler
      $(document).on('click', '#load-more-btn', function() {
        const $hiddenSchools = $('.school-card:hidden').not('.filtered-out');
        $hiddenSchools.slice(0, itemsToShow).fadeIn();
        updateLoadMoreButton();
      });
      
      // Filter schools by department and programs
      function filterSchools() {
        const selectedDepartment = $('#departement-filter', context).val().toLowerCase();
        const selectedPrograms = [];
        const searchQuery = $('#school-search', context).val().toLowerCase().trim();
        
        // Get all checked program checkboxes
        $('.form-check-input:checked', context).each(function() {
          selectedPrograms.push($(this).val());
        });
        
        // Show/hide schools based on filters
        $('.school-card', context).each(function() {
          const schoolCard = $(this);
          const schoolDepartment = schoolCard.data('department');
          const schoolPrograms = schoolCard.data('programs').split(' ');
          const schoolName = schoolCard.find('.card-title').text().toLowerCase();
          const schoolAddress = schoolCard.find('.school-address').text().toLowerCase();
          
          const departmentMatch = !selectedDepartment || schoolDepartment === selectedDepartment;
          const programMatch = selectedPrograms.length === 0 || selectedPrograms.some(program => 
            schoolPrograms.indexOf(program) !== -1
          );
          const searchMatch = !searchQuery || 
            schoolName.includes(searchQuery) || 
            schoolAddress.includes(searchQuery);
          
          if (departmentMatch && programMatch && searchMatch) {
            schoolCard.removeClass('filtered-out');
            if ($('.school-card:visible', context).length < itemsToShow) {
              schoolCard.show();
            } else {
              schoolCard.hide();
            }
          } else {
            schoolCard.addClass('filtered-out').hide();
          }
        });
        
        // Show message if no schools match the filters
        if ($('.school-card:visible', context).length === 0) {
          if ($('.no-results-message', context).length === 0) {
            $('.col-lg-8', context).append('<div class="alert alert-info no-results-message">Aucune école ne correspond aux critères sélectionnés.</div>');
          }
        } else {
          $('.no-results-message', context).remove();
        }
        
        // Update Load More button after filtering
        updateLoadMoreButton();
      }
      
      // Initial Load More button setup
      updateLoadMoreButton();
      
      // Event listeners for filters
      $(once('filter', '#departement-filter, .form-check-input, #school-search', context)).on('change keyup', filterSchools);
    }
  };
})(jQuery, Drupal);
