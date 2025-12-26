(function ($, Drupal, drupalSettings) {
  'use strict';
  
  Drupal.behaviors.medicationSearch = {
    attach: function (context, settings) {
      var $searchInput = $('#medicationSearch', context);
      
      if ($searchInput.length && !$searchInput.hasClass('search-processed')) {
        $searchInput.addClass('search-processed');
        
        console.log('Medication search filter initialized');
        
        $searchInput.on('keyup', function () {
          var searchText = $(this).val().toLowerCase();
          
          console.log('Searching for:', searchText);
          
          if (searchText === '') {
            $('.medication-item').show();
            console.log('Showing all items');
            return;
          }
          
          var visibleCount = 0;
          $('.medication-item').each(function () {
            var text = $(this).text().toLowerCase();
            var matches = text.indexOf(searchText) > -1;
            $(this).toggle(matches);
            if (matches) visibleCount++;
          });
          
          console.log('Visible items:', visibleCount);
        });
      }
    }
  };

  Drupal.behaviors.medicationLoadMore = {
    attach: function (context, settings) {
      console.log('medicationLoadMore behavior attached');
      console.log('drupalSettings:', drupalSettings);
      console.log('context:', context);
      
      var $loadMoreBtn = $('#loadMoreBtn', context);
      var $loadMoreContainer = $('#loadMoreContainer', context);
      var $tbody = $('.table tbody', context);
      
      console.log('Found loadMoreBtn:', $loadMoreBtn.length);
      console.log('Found loadMoreContainer:', $loadMoreContainer.length);
      console.log('Found tbody:', $tbody.length);
      
      if ($loadMoreBtn.length && !$loadMoreBtn.hasClass('load-more-processed')) {
        $loadMoreBtn.addClass('load-more-processed');
        
        var medicationSettings = drupalSettings.authorized_medication || {};
        var loaded = medicationSettings.loaded || 0;
        var total = medicationSettings.total || 0;
        var limit = medicationSettings.limit || 20;
        var loadMoreUrl = medicationSettings.loadMoreUrl || '/medicaments-autorises/load-more';
        
        console.log('Load More initialized - Loaded:', loaded, 'Total:', total, 'URL:', loadMoreUrl);
        console.log('medicationSettings:', medicationSettings);
        
        if (loaded >= total) {
          $loadMoreContainer.hide();
        }
        
        $loadMoreBtn.on('click', function () {
          var $btn = $(this);
          var $text = $btn.find('.load-more-text');
          var $spinner = $btn.find('.load-more-spinner');
          
          $btn.prop('disabled', true);
          $text.addClass('d-none');
          $spinner.removeClass('d-none');
          
          console.log('Loading more medications from:', loadMoreUrl, 'offset:', loaded);
          
          $.ajax({
            url: loadMoreUrl,
            method: 'GET',
            data: {
              offset: loaded,
              limit: limit
            },
            success: function (response) {
              console.log('AJAX response:', response);
              if (response.success && response.medications) {
                response.medications.forEach(function (medication) {
                  var row = '<tr class="medication-item">' +
                    '<td>' + medication.name + '</td>' +
                    '<td>' + medication.shape + '</td>' +
                    '<td>' + medication.form + '</td>' +
                    '<td>' + medication.dosage + '</td>' +
                    '</tr>';
                  $tbody.append(row);
                });
                
                loaded += response.loaded;
                $('#loadedCount').text(loaded);
                
                console.log('Loaded more medications. Now at:', loaded, '/', total);
                
                if (loaded >= total) {
                  $loadMoreContainer.hide();
                }
              } else {
                console.error('Invalid response format:', response);
              }
            },
            error: function (xhr, status, error) {
              console.error('AJAX error:', status, error, xhr.responseText);
              alert('Erreur lors du chargement des médicaments: ' + error);
            },
            complete: function () {
              $btn.prop('disabled', false);
              $text.removeClass('d-none');
              $spinner.addClass('d-none');
            }
          });
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
