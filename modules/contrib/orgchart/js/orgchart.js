(function ($, Drupal) {
  Drupal.behaviors.render_orgchart = {
    attach: function (context, settings) {

      function applyZoom(display, parent) {
        let parentWidth = parent.width();
        let displayWidth = display.width();
        if (displayWidth > parentWidth) {
          let percent = ((parentWidth * 100) / displayWidth) / 100;
          display.css('zoom', percent);
        }  
      }

      function orgchart(selector, index, val) {
        let values = JSON.parse(val.values);
        let cells = values.cells;
        let lines = values.lines;
        let height = val.height;
        let width = val.width;


        $(selector).append('<div class="' + index + '"></div>');
        let display = $(selector).find('.' + index);

        for (let i = 0; i < cells.length; i++) {
          let text = '<span class="title">' + cells[i].title + '';
          if (cells[i].link !== '' && typeof cells[i].link !== 'undefined') {
            text = '<span class="title"><a href="' + cells[i].link + '">' + cells[i].title + '</a>';
          }

          if (cells[i].subtitle) {
            text += '<div class="subtitle">' + cells[i].subtitle.replace(/\n/g,"<br>") + '</div></span>';
          } 
          else {
            text += '</span>';
          }

          display.append('<div class="cell" rel="' + cells[i].id + '">' + text + '</div>');
          let cell = display.find(".cell[rel='" + cells[i].id + "']");
          cell.addClass('cell-level-' + cells[i].level);
          cell.addClass('cell-weight-' + cells[i].fontweight);
          cell.addClass('cell-' + cells[i].size);
          cell.css('left', (cells[i].left.replace('px', '') * 1));
          cell.css('top', cells[i].top);
          cell.css('width', cells[i].width.replace('px', '') * 1);
          cell.css('height', cells[i].height.replace('px', '') * 1);
          cell.css('color', cells[i].color);
          cell.css('background', cells[i].bgcolor);
        }

        display.height(height);
        display.width(width);

        for (let i = 0; i < lines.length; i++) {
          display.append('<div class="line" rel="' + lines[i].id + '"></div>');
          let line = display.find(".line[rel='" + lines[i].id + "']");
          line.addClass('line-' + lines[i].linetype);
          line.addClass('line-' + lines[i].orientation);
          line.css('left', (lines[i].left.replace('px', '') * 1));
          line.css('top', lines[i].top);
          line.css('width', lines[i].width);
          line.css('height', lines[i].height);
          line.css('max-height', lines[i].height);

          if (lines[i].linetype === 'dashed') {
            line.css('border-color', lines[i].linecolor);
          }
          else {
            line.css('background-color', lines[i].linecolor);
          }

          if (lines[i].left_top_arrow === '1') {
            line.addClass('left_top_arrow');
          } 
          if (lines[i].right_bottom_arrow === '1') {
            line.addClass('right_bottom_arrow');
          }
        }

        applyZoom(display, $(selector));
        $(window).on('resize', function() {
          applyZoom(display, $(selector));
        });

      }


      if ($('#render_orgchart').length > 0) {
        $(once('render_orgchart', '#render_orgchart', context)).each(function() {
          $.each(settings.orgcharts , function(index, values) { 
            orgchart('#render_orgchart', index, values);
          });
        });
      }


    }
  };
})(jQuery, Drupal);

