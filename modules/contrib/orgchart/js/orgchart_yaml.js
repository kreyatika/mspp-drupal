/**
 * @file
 * JavaScript behaviors for CodeMirror integration.
 */

(function ($, Drupal, once) {

  'use strict';

  Drupal.orgchart = Drupal.orgchart || {};
  Drupal.orgchart.codeMirror = Drupal.orgchart.codeMirror || {};
  Drupal.orgchart.codeMirror.options = Drupal.orgchart.codeMirror.options || {};

  /**
   * Initialize CodeMirror editor.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.orgchartCodeMirror = {
    attach: function (context) {

      if (!window.CodeMirror) {
        return;
      }

      $(once('orgchart-codemirror', 'textarea.js-orgchart-codemirror', context)).each(function () {
        var $input = $(this);

        var $details = $input.parents('details:not([open])');
        $details.attr('open', 'open');

        $input.removeAttr('required');

        var options = $.extend({
          mode: 'yaml',
          lineNumbers: true,
          lineWrapping: ($input.attr('wrap') !== 'off'),
          viewportMargin: Infinity,
          readOnly: !!($input.prop('readonly') || $input.prop('disabled')),
          extraKeys: {
            Tab: function (cm) {
              var spaces = Array(cm.getOption('indentUnit') + 1).join(' ');
              cm.replaceSelection(spaces, 'end', '+element');
            },
            Esc: function (cm) {
              var textarea = $(cm.getTextArea());
              $(textarea).show().addClass('visually-hidden');
              var $tabbable = $(':tabbable');
              var tabindex = $tabbable.index(textarea);
              $(textarea).hide().removeClass('visually-hidden');

              $tabbable.eq(tabindex + 2).trigger('focus');
            }

          }
        }, Drupal.orgchart.codeMirror.options);

        var editor = CodeMirror.fromTextArea(this, options);

        $details.removeAttr('open');

        if ($input.css('min-height')) {
          var minHeight = $input.css('min-height');
          $(editor.getWrapperElement())
            .css('min-height', minHeight)
            .find('.CodeMirror-scroll')
            .css('min-height', minHeight);
        }
        if ($input.css('max-height')) {
          var maxHeight = $input.css('max-height');
          $(editor.getWrapperElement())
            .css('max-height', maxHeight)
            .find('.CodeMirror-scroll')
            .css('max-height', maxHeight);
        }

        var changeTimer = null;
        editor.on('change', function () {
          if (changeTimer) {
            window.clearTimeout(changeTimer);
            changeTimer = null;
          }
          changeTimer = setTimeout(function () {editor.save();}, 500);
        });

        $input.on('change', function () {
          editor.getDoc().setValue($input.val());
        });

        setTimeout(function () {
          var $tabPanel = $input.parents('.ui-tabs-panel:hidden');
          var $details = $input.parents('details:not([open])');

          if (!$tabPanel.length && $details.length) {
            return;
          }

          $tabPanel.show();
          $details.attr('open', 'open');

          editor.refresh();

          $tabPanel.hide();
          $details.removeAttr('open');
        }, 500);
      });

      if (window.CodeMirror.runMode) {
        $(once('orgchart-codemirror-runmode', '.js-orgchart-codemirror-runmode', context)).each(function () {
          CodeMirror.runMode($(this).addClass('cm-s-default').text(), $(this).attr('data-orgchart-codemirror-mode'), this);
        });
      }

    }
  };

})(jQuery, Drupal, once);
