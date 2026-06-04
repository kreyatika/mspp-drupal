(function ($, Drupal, once) {

  // Auto-submit selects, strip empty params on submit.
  Drupal.behaviors.publicationsFilter = {
    attach: function (context, settings) {
      once('publications-select', 'select[name="category"], select[name="direction"], select[name="year"]', context).forEach(function (el) {
        $(el).on('change', function () {
          $(this).closest('form').trigger('submit');
        });
      });

      once('publications-form', 'form', context).forEach(function (form) {
        $(form).on('submit', function () {
          $(form).find('input, select').each(function () {
            if ($(this).val() === '' || $(this).val() === null) {
              $(this).prop('disabled', true);
            }
          });
        });
      });
    }
  };

  // PDF.js thumbnail rendering with IntersectionObserver lazy loading.
  Drupal.behaviors.publicationsPdfPreview = {
    attach: function (context, settings) {
      if (typeof pdfjsLib === 'undefined') return;

      pdfjsLib.GlobalWorkerOptions.workerSrc =
        drupalSettings.path.baseUrl + 'modules/custom/publications/js/pdf.worker.min.js';

      // Observe the preview container (not the canvas) — display:none elements
      // are never intersecting so the canvas itself can't be observed directly.
      var previews = once('pdf-preview', '.pub-card-preview', context);
      if (!previews.length) return;

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          observer.unobserve(entry.target);
          var canvas = entry.target.querySelector('.pub-pdf-canvas');
          var loader = entry.target.querySelector('.pub-pdf-loader');
          if (loader) loader.classList.add('is-loading');
          if (canvas) renderPdf(canvas);
        });
      }, { rootMargin: '150px' });

      previews.forEach(function (preview) {
        if (preview.querySelector('.pub-pdf-canvas')) {
          observer.observe(preview);
        }
      });

      function renderPdf(canvas) {
        var url = drupalSettings.path.baseUrl + canvas.getAttribute('data-pdf');
        var loader = canvas.parentElement.querySelector('.pub-pdf-loader');

        var loadingTask = pdfjsLib.getDocument({ url: url, verbosity: 0 });

        var timeout = setTimeout(function () {
          loadingTask.destroy();
          showFallback(loader);
        }, 8000);

        loadingTask.promise.then(function (pdf) {
          clearTimeout(timeout);
          return pdf.getPage(1);
        }).then(function (page) {
          var targetWidth = canvas.parentElement.offsetWidth || 220;
          var scale = targetWidth / page.getViewport({ scale: 1 }).width;
          var viewport = page.getViewport({ scale: scale });

          canvas.width  = viewport.width;
          canvas.height = viewport.height;

          return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;
        }).then(function () {
          if (loader) loader.style.display = 'none';
          canvas.style.display = 'block';
        }).catch(function () {
          clearTimeout(timeout);
          showFallback(loader);
        });
      }

      function showFallback(loader) {
        if (loader) {
          loader.innerHTML = '<i class="fas fa-file-pdf pub-pdf-fallback"></i>';
        }
      }
    }
  };

})(jQuery, Drupal, once);
