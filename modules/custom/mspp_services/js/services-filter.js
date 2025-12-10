(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.servicesFilter = {
    attach: function (context, settings) {
      const filterServices = () => {
        const searchText = $('.search-input').val().toLowerCase();
        const selectedType = $('input[name="serviceType"]:checked').val();
        const selectedCategories = $('input[name="categories[]"]:checked').map(function() {
          return $(this).val();
        }).get();

        $('.service-card').each(function() {
          const card = $(this);
          const cardWrapper = card.closest('a');
          const title = card.find('.card-title').text().toLowerCase();
          const description = card.find('.card-text').text().toLowerCase();
          const categories = card.data('categories') ? card.data('categories').toString().split(',') : [];
          
          let showCard = true;

          // Text search
          if (searchText) {
            showCard = title.includes(searchText) || description.includes(searchText);
          }

          // Type filter
          if (showCard && selectedType) {
            showCard = card.data('type') === selectedType;
          }

          // Category filter
          if (showCard && selectedCategories.length > 0) {
            showCard = categories.some(cat => selectedCategories.includes(cat));
          }

          cardWrapper.toggle(showCard);
        });
      };

      // Add event listeners
      $('.search-input', context).on('keyup', filterServices);
      $('input[name="serviceType"]', context).on('change', filterServices);
      $('input[name="categories[]"]', context).on('change', filterServices);
      $('#filterServices', context).on('click', filterServices);
    }
  };
})(jQuery, Drupal);
