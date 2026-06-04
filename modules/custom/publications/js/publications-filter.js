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
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

      var canvases = once('pdf-preview', '.pub-pdf-canvas', context);
      if (!canvases.length) return;

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          observer.unobserve(entry.target);
          renderPdf(entry.target);
        });
      }, { rootMargin: '100px' });

      canvases.forEach(function (canvas) {
        observer.observe(canvas);
      });

      function renderPdf(canvas) {
        var url = canvas.getAttribute('data-pdf');
        var loader = canvas.parentElement.querySelector('.pub-pdf-loader');

        pdfjsLib.getDocument(url).promise.then(function (pdf) {
          return pdf.getPage(1);
        }).then(function (page) {
          var viewport = page.getViewport({ scale: 1 });
          var targetWidth = canvas.parentElement.offsetWidth || 220;
          var scale = targetWidth / viewport.width;
          var scaled = page.getViewport({ scale: scale });

          canvas.width  = scaled.width;
          canvas.height = scaled.height;

          page.render({
            canvasContext: canvas.getContext('2d'),
            viewport: scaled,
          }).promise.then(function () {
            if (loader) loader.style.display = 'none';
            canvas.style.display = 'block';
          });
        }).catch(function () {
          // PDF unavailable — show fallback placeholder.
          if (loader) loader.innerHTML = '<i class="fas fa-file-pdf" style="font-size:2rem;color:#ccc;"></i>';
        });
      }
    }
  };

})(jQuery, Drupal, once);
