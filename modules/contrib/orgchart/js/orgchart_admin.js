(function ($, Drupal) {
  Drupal.behaviors.orgchart_admin = {
    attach: function (context, settings) {
      $(once('orgchart_admin', 'body', context)).each(function () {

        /*
         * INIT
         */
        $("#draggable").resizable({
          handles: 's',
          stop: function (e, ui) {
            let height = Math.round(ui.size.height / 5) * 5;
            $("input[name='height']").val(height);
          },
        });
        let dialog = $("#dialog-cell").dialog({autoOpen: false});
        let dialogconn = $("#dialog-lines").dialog({autoOpen: false});

        /*
         * Get saved values.
         */
        function getSavedValues() {
          let values = {};
          if ($("textarea[name='values']").length > 0 && $("textarea[name='values']").val()) {
            values = JSON.parse($("textarea[name='values']").val());
          }
          else {
            values['cells'] = {};
            values['lines'] = {};
          }
          return values;
        }

        /*
         * Update saved values.
         */
        function updateSavedValues(values) {
          if ($("textarea[name='values']").length > 0) {
            $("textarea[name='values']").val(JSON.stringify(values));
          }
        }

        /*
         * Build Cell Text.
         */
        function getCellText(title, subtitle) {
          let text = '<span class="title">' + title + '';

          if (subtitle !== '' && typeof subtitle !== 'undefined') {
            text += '<div class="subtitle">' + subtitle.replace(/\n/g,"<br>") + '</div></span>';
          } 
          else {
            text += '</span>';
          }
          return text;
        }

        /*
         * Updates cell after move.
         */
        function updateCellMove(e, ui, cell) {
          let values = getSavedValues();
          let cells = values['cells'];
          for (let i = 0; i < cells.length; i++) {
            if (cells[i].id === Number(e.target.attributes.rel.value)) {
              cells[i].left = (Math.round(ui.position.left / 5) * 5) + 'px';
              cells[i].top = (Math.round(ui.position.top / 5) * 5) + 'px';
            }
          }
          values['cells'] = cells;
          updateSavedValues(values);
        }

        /*
         * Updates cell after resize.
         */
        function updateCellSize(e, ui, cell) {
          let values = getSavedValues();
          let cells = values['cells'];
          for (let i = 0; i < cells.length; i++) {
            if (cells[i].id === Number(e.target.attributes.rel.value)) {
              cells[i].width = (Math.round(ui.size.width / 5) * 5) + 'px';
              cells[i].height = (Math.round(ui.size.height / 5) * 5) + 'px';
            }
          }
          values['cells'] = cells;
          updateSavedValues(values);
        }

        /*
         * Add draggable to cell.
         */
        function cellAddDraggable(cell) {
          cell.draggable({
            containment: "#draggable",
            stop: function (e, ui) {
              updateCellMove(e, ui, cell);
            },
            grid: [5, 5]
          }).resizable({
            stop: function (e, ui) {
              updateCellSize(e, ui, cell);
            },
            grid: [5, 5]
          });
        }

        /*
         * Updates line after move.
         */
        function updateLineMove(e, ui, cell) {
          let values = getSavedValues();
          let lines = values['lines'];
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].id === Number(e.target.attributes.rel.value)) {
              lines[i].left = (Math.round(ui.position.left / 5) * 5) + 'px';
              lines[i].top = (Math.round(ui.position.top / 5) * 5) + 'px';
            }
          }
          values['lines'] = lines;
          updateSavedValues(values);
        }

        /*
         * Updates line after resize.
         */
        function updateLineSize(e, ui, line) {
          let values = getSavedValues();
          let lines = values['lines'];
          let orientation = 'vertical';
          if (e.target.attributes.class.value.search('horizontal') > 0) {
            orientation = 'horizontal';
          }
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].id === Number(e.target.attributes.rel.value)) {
              if (orientation === 'horizontal') {
                lines[i].width = (Math.round(ui.size.width / 5) * 5) + 'px';
                lines[i].height = '2px';
              } else {
                lines[i].width = '2px';
                lines[i].height = (Math.round(ui.size.height / 5) * 5) + 'px';
              }
            }
          }
          values['lines'] = lines;
          updateSavedValues(values);
        }

        /*
         * Add draggable to line.
         */
        function lineAddDraggable(line, orientation) {
          line.draggable({
            containment: "#draggable",
            stop: function (e, ui) {
              updateLineMove(e, ui, line);
            },
            grid: [5, 5]
          }).resizable({
            handles: orientation,
            stop: function (e, ui) {
              updateLineSize(e, ui, line);
            },
            grid: [5, 5]
          });
        }

        /*
         * Change resizable orientation.
         */
        function changeResizable(line, orientation) {
          line.resizable('destroy');
          line.resizable({
            handles: orientation,
            stop: function (e, ui) {
              updateLineSize(e, ui, line);
            },
            grid: [5, 5]
          });
        }

        /*
         * Get line orientation.
         */
        function lineGetOrientation(line) {
          let orientation = "s";
          if (line.orientation === 'horizontal') {
            orientation = "e";
          }
          return orientation;
        }

        /*
         * Set line background.
         */
        function lineSetBackground(type, orientation, linecolor, line) {
          line.css('background', '');
          line.css('background-color', '');
          line.css('border-color', '');
          if (linecolor) {
            if (type === 'dashed') {
              if (orientation === 'horizontal') {
                line.css('background', 'repeating-linear-gradient( to right, ' + linecolor + ', ' + linecolor + ' 6px, #fff 6px, #fff 12px )');
              } 
              else {
                line.css('background', 'repeating-linear-gradient( to bottom, ' + linecolor + ', ' + linecolor + ' 6px, #fff 6px, #fff 12px )');
              }
            } 
            else {
              line.css('background-color', linecolor);
            }
            line.css('border-color', linecolor);
          }
        }

        /*
         * Set line arrows.
         */
        function lineSetArrow(leftTopArrow, rightBottomArrow, line) {
          if (leftTopArrow === '1' || leftTopArrow === true) {
            line.addClass('left_top_arrow');
          } 
          if (rightBottomArrow === '1' || rightBottomArrow === true) {
            line.addClass('right_bottom_arrow');
          }
        }

        /*
         * Gets next ID.
         */
        function getNextId(values) {
          let count = 0;
          for (let i = values.length - 1; i >= 0; i--) {
            if (values[i].id > count) {
              count = values[i].id;
            }
          }
          count = count + 1;

          return count;
        }

        /*
         * Appends line to draggable.
         */
        function appendLine(id) {
          $("#draggable").append('<div class="line" rel="' + id + '"><span class="delete"></span><span class="edit"></span></div>');
          let line = $("#draggable .line[rel='" + id + "']");

          return line;
        }

        /*
         * Appends cell to draggable.
         */
        function appendCell(id, text) {
          $("#draggable").append('<div class="cell" rel="' + id + '">' + text + '<span class="delete"></span><span class="edit"></span></div>');
          let cell = $("#draggable .cell[rel='" + id + "']");

          return cell;
        }

        /*
         * Get line form val.
         */
        function getLineValue(id) {
          let elem = $("#dialog-lines [name='" + id + "']");
          if (elem.length) {
            if (elem.attr('type') === 'checkbox') {
              return elem.prop('checked');
            }
            else {
              return elem.val();
            }
          }
          return false;
        }

        /*
         * Get Cell form val.
         */
        function getCellValue(id) {
          let elem = $("#dialog-cell [name='" + id + "']");
          if (elem.length) {
            return elem.val();
          }
          return false;
        }

        /*
         * Get element to clone.
         */
        function getElementToClone(elements, element) {
          let elementtoclone = {};
          for (let i = elements.length - 1; i >= 0; i--) {
            if (elements[i].id === Number(element.attr('rel'))) {
              elementtoclone = elements[i];
            }
          }

          return elementtoclone;
        }

        /*
         * Clear Form.
         */
        function clearForm(element) {
          element.find('input, select, textarea').each(function () {
            $(this).val('');
          });
          element.find('select').each(function () {
            let select = $(this);
            select.find('option').each(function () {
              if ($(this).attr('selected') === 'selected') {
                select.val($(this).attr('value'));
              }
            });
          });
          element.find('input[type="checkbox"]').each(function () {
            console.log($(this).prop('checked'));
            $(this).prop('checked', false);
          });
        }

        /*
         * Adds line to orgchart.
         */
        function addLine() {
          let values = getSavedValues();
          let lines = values['lines'];

          let count = getNextId(lines);
          let line = appendLine(count);
          let lineinfo = {
            id: count,
            orientation: getLineValue('orientation'),
            linetype: getLineValue('linetype'),
            left: '10px',
            top: '10px',
            width: (getLineValue('orientation') === 'vertical') ? '2px' : '100px',
            height: (getLineValue('orientation') === 'vertical') ? '100px' : '2px',
            linecolor: getLineValue('linecolor'),
            left_top_arrow: getLineValue('left_top_arrow'),
            right_bottom_arrow: getLineValue('right_bottom_arrow'),
          };

          line.addClass('line-' + lineinfo.linetype);
          line.addClass('line-' + lineinfo.orientation);
          lineSetBackground(lineinfo.linetype, lineinfo.orientation, lineinfo.linecolor, line);
          lineSetArrow(lineinfo.left_top_arrow, lineinfo.right_bottom_arrow, line);

          let orientation = lineGetOrientation(lineinfo);
          if (count === 1) {
            lines = [];
          } 
          lines.push(lineinfo);
          values['lines'] = lines;
          updateSavedValues(values);
          lineAddDraggable(line, orientation);
          dialogconn.dialog("close");
        }

        /*
         * Deletes line from orgchart.
         */
        function deleteLine(line) {
          let values = getSavedValues();
          let lines = values['lines'];
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].id === Number(line.attr('rel'))) {
              lines.splice(i, 1);
            }
          }
          line.remove();
          values['lines'] = lines;
          updateSavedValues(values);
        }

        /*
         * Updates line in orgchart.
         */
        function updateLine() {
          let line = $("#draggable div.line.outline-element-clicked");
          let values = getSavedValues();
          let lines = values['lines'];

          for (let i = 0; i < lines.length; i++) {
            if (lines[i].id === Number(line.attr('rel'))) {
              if (lines[i].orientation !== getLineValue('orientation')) {
                let width = lines[i].width;
                lines[i].width = lines[i].height;
                lines[i].height = width;
                line.css('height', lines[i].height);
                line.css('width', lines[i].width);
              }

              lines[i].linetype = getLineValue('linetype');
              lines[i].orientation = getLineValue('orientation');
              lines[i].linecolor = getLineValue('linecolor');
              lines[i].left_top_arrow = getLineValue('left_top_arrow');
              lines[i].right_bottom_arrow = getLineValue('right_bottom_arrow');

              lineSetBackground(lines[i].linetype, lines[i].orientation, lines[i].linecolor, line);
              line.removeClass('left_top_arrow').removeClass('right_bottom_arrow');
              lineSetArrow(lines[i].left_top_arrow, lines[i].right_bottom_arrow, line);
              line.removeClass('line-horizontal').removeClass('line-vertical').removeClass('line-dashed').removeClass('line-normal');
              line.addClass('line-' + lines[i].linetype).addClass('line-' + lines[i].orientation);
              let orientation = lineGetOrientation(lines[i]);
              changeResizable(line, orientation);
            }
          }
          values['lines'] = lines;
          updateSavedValues(values);
          dialogconn.dialog("close");
        }

        /*
         * Clone line.
         */
        function cloneLine() {
          let linetocloneElement = $("#draggable div.line.outline-element-clicked");
          let values = getSavedValues();
          let lines = values['lines'];
          let count = getNextId(lines);
          let linetoclone = getElementToClone(lines, linetocloneElement)

          let line = appendLine(count);
          let lineinfo = {
            id: count,
            orientation: linetoclone.orientation,
            linetype: linetoclone.linetype,
            left: '10px',
            top: '10px',
            width: linetoclone.width,
            height: linetoclone.height,
            linecolor: linetoclone.linecolor,
            left_top_arrow: linetoclone.left_top_arrow,
            right_bottom_arrow: linetoclone.right_bottom_arrow,
          };

          line.addClass('line-' + lineinfo.linetype);
          line.addClass('line-' + lineinfo.orientation);
          lineSetBackground(lineinfo.linetype, lineinfo.orientation, lineinfo.linecolor, line);
          lineSetArrow(lineinfo.left_top_arrow, lineinfo.right_bottom_arrow, line);          
          line.css('height', linetoclone.height);
          line.css('width', linetoclone.width);

          let orientation = lineGetOrientation(lineinfo);
          lines.push(lineinfo);
          values['lines'] = lines;
          updateSavedValues(values);
          lineAddDraggable(line, orientation);
          dialogconn.dialog("close");
        }

        /*
         * Adds cell to orgchart.
         */
        function addCell() {
          let values = getSavedValues();
          let cells = values['cells'];
          let count = getNextId(cells);

          let cellinfo = {
            id: count,
            title: getCellValue('name'),
            link: getCellValue('link'),
            left: '0px',
            top: '0px',
            width: '300px',
            height: '30px',
            size: getCellValue('size'),
            color: getCellValue('color'),
            bgcolor: getCellValue('bgcolor'),
            fontweight: getCellValue('fontweight'),
            level: getCellValue('level'),
            subtitle: getCellValue('subtitle')
          };

          let text = getCellText(cellinfo.title, cellinfo.subtitle);
          let cell = appendCell(count, text);
          cell.addClass('cell-level-' + cellinfo.level);
          cell.addClass('cell-weight-' + cellinfo.fontweight);
          cell.addClass('cell-' + cellinfo.size);
          cell.css('background-color', cellinfo.bgcolor);
          cell.css('color', cellinfo.color);

          if (count === 1) {
            cells = [];
          }
          cells.push(cellinfo);
          values['cells'] = cells;
          updateSavedValues(values);
          cellAddDraggable(cell);
          dialog.dialog("close");
        }


        /*
         * Deletes cell from orgchart.
         */
        function deleteCell(cell) {
          let values = getSavedValues();
          let cells = values['cells'];
          for (let i = 0; i < cells.length; i++) {
            if (cells[i].id === Number(cell.attr('rel'))) {
              cells.splice(i, 1);
            }
          }
          cell.remove();
          values['cells'] = cells;
          updateSavedValues(values);
        }

        /*
         * Updates cell in orgchart.
         */
        function updateCell() {
          let cell = $("#draggable div.cell.outline-element-clicked");
          let values = getSavedValues();
          let cells = values['cells'];
          for (let i = 0; i < cells.length; i++) {
            if (cells[i].id === Number(cell.attr('rel'))) {
              cells[i].title = getCellValue('name');
              cells[i].link = getCellValue('link');
              cells[i].size = getCellValue('size');
              cells[i].color = getCellValue('color');
              cells[i].bgcolor = getCellValue('bgcolor');
              cells[i].fontweight = getCellValue('fontweight');
              cells[i].level = getCellValue('level');
              cells[i].subtitle = getCellValue('subtitle');
              let subtitle = '';
              if (cells[i].subtitle) {
                subtitle = '<div class="subtitle">' + cells[i].subtitle + '</div>';
              }

              cell.find('span.title').html(cells[i].title + subtitle);
              cell.css('background-color', cells[i].bgcolor);
              cell.css('color', cells[i].color);
              cell.removeClass();
              cell.addClass('cell').addClass('cell-' + cells[i].size);
              cell.addClass('cell-weight-' + cells[i].fontweight);
              cell.addClass('cell-level-' + cells[i].level);
            }
          }
          values['cells'] = cells;
          updateSavedValues(values);
          dialog.dialog("close");
        }

        /*
         * Clone cell.
         */
        function cloneCell() {
          let celltoCloneElement = $("#draggable div.cell.outline-element-clicked");
          let values = getSavedValues();
          let cells = values['cells'];
          let count = getNextId(cells);
          let celltoClone = getElementToClone(cells, celltoCloneElement)
          
          let text = getCellText(celltoClone.title, celltoClone.subtitle);
          let cell = appendCell(count, text);
          cell.addClass('cell-level-' + celltoClone.level);
          cell.addClass('cell-weight-' + celltoClone.fontweight);
          cell.addClass('cell-' + celltoClone.size);

          let cellinfo = {
            id: count,
            title: celltoClone.title,
            link: celltoClone.link,
            left: '10px',
            top: '10px',
            width: celltoClone.width,
            height: celltoClone.height,
            size: celltoClone.size,
            color: celltoClone.color,
            bgcolor: celltoClone.bgcolor,
            fontweight: celltoClone.fontweight,
            level: celltoClone.level,
            subtitle: celltoClone.subtitle
          };

          cell.css('background-color', celltoClone.bgcolor);
          cell.css('color', celltoClone.color);
          cell.css('height', celltoClone.height);
          cell.css('width', celltoClone.width);

          cells.push(cellinfo);
          values['cells'] = cells;
          updateSavedValues(values);
          cellAddDraggable(cell);
          dialog.dialog("close");
        }

        /*
         * INITIAL LOAD
         */
        let values = getSavedValues();
        let cells = values['cells'];

        for (let i = 0; i < cells.length; i++) {
          let text = getCellText(cells[i].title, cells[i].subtitle);
          let cell = appendCell(cells[i].id, text);
          cell.addClass('cell-level-' + cells[i].level);
          cell.addClass('cell-weight-' + cells[i].fontweight);
          cell.addClass('cell-' + cells[i].size);
          cell.css('left', cells[i].left);
          cell.css('top', cells[i].top);
          cell.css('width', cells[i].width);
          cell.css('height', cells[i].height);
          cell.css('color', cells[i].color);
          cell.css('background', cells[i].bgcolor);
          cellAddDraggable(cell);
        }

        let lines = values['lines'];
        for (let i = 0; i < lines.length; i++) {
          let line = appendLine(lines[i].id);
          line.addClass('line-' + lines[i].linetype);
          line.addClass('line-' + lines[i].orientation);
          line.css('left', lines[i].left);
          line.css('top', lines[i].top);
          line.css('width', lines[i].width);
          line.css('height', lines[i].height);

          let orientation = lineGetOrientation(lines[i]);
          lineSetBackground(lines[i].linetype, lines[i].orientation, lines[i].linecolor, line);
          lineSetArrow(lines[i].left_top_arrow, lines[i].right_bottom_arrow, line);
          lineAddDraggable(line, orientation);
        }

        $("#edit-add-point").click(function (event) {
          event.stopPropagation();
          event.preventDefault();

          clearForm($("#dialog-cell"));
          dialog = $("#dialog-cell").dialog({
            autoOpen: true,
            height: 620,
            title: Drupal.t("Add Cell"),
            width: 650,
            modal: true,
            buttons: {
              Add: addCell,
              Cancel: function () {
                dialog.dialog("close");
              }
            }
          });
        });

        $("#edit-add-conn").click(function (event) {
          event.stopPropagation();
          event.preventDefault();

          clearForm($("#dialog-lines"));
          dialogconn = $("#dialog-lines").dialog({
            autoOpen: true,
            height: 350,
            title: Drupal.t("Add connection"),
            width: 450,
            modal: true,
            buttons: {
              Add: addLine,
              Cancel: function () {
                dialogconn.dialog("close");
              }
            }
          });
        });

        $("#draggable").delegate('div.cell.outline-element-clicked span.delete', 'click', function (event) {
          let r = confirm(Drupal.t("Are you sure you want to delete the cell?"));
          if (r === true) {
            deleteCell($(this).parents('.cell'));
          }
        });
        
        $("#draggable").delegate('div.line.outline-element-clicked span.delete', 'click', function (event) {
          let r = confirm(Drupal.t("Are you sure you want to delete the connection?"));
          if (r === true) {
            deleteLine($(this).parents('.line'));
          }
        });

        $("#draggable").delegate('div.cell.outline-element-clicked span.edit', 'click', function (event) {
          let values = getSavedValues();
          let cells = values['cells'];
          for (let i = 0; i < cells.length; i++) {
            if (cells[i].id === Number($(this).parents('.cell').attr('rel'))) {
              $("#dialog-cell #edit-id").val(cells[i].id);
              $("#dialog-cell #edit-name").val(cells[i].title);
              $("#dialog-cell #edit-link").val(cells[i].link);
              $("#dialog-cell #edit-size").val(cells[i].size);
              $("#dialog-cell #edit-color").val(cells[i].color);
              $("#dialog-cell #edit-bgcolor").val(cells[i].bgcolor);
              $("#dialog-cell #edit-fontweight").val(cells[i].fontweight);
              $("#dialog-cell #edit-level").val(cells[i].level);
              $("#dialog-cell #edit-subtitle").val(cells[i].subtitle);
            }
          }
          dialog = $("#dialog-cell").dialog({
            autoOpen: true,
            height: 620,
            width: 650,
            title: Drupal.t("Edit Cell"),
            modal: true,
            buttons: {
              Save: updateCell,
              Clone: cloneCell,
              Cancel: function () {
                dialog.dialog("close");
              }
            }
          });
        });

        $("#draggable").delegate('div.line.outline-element-clicked span.edit', 'click', function (event) {
          let values = getSavedValues();
          let lines = values['lines'];
          for (let i = 0; i < lines.length; i++) {
            if (lines[i].id === Number($(this).parents('.line').attr('rel'))) {
              $("#dialog-lines #edit-lineid").val(lines[i].id);
              $("#dialog-lines #edit-linetype").val(lines[i].linetype);
              $("#dialog-lines #edit-orientation").val(lines[i].orientation);
              $("#dialog-lines #edit-linecolor").val(lines[i].linecolor);
              if (lines[i].left_top_arrow === '1' || lines[i].left_top_arrow === true) {
                $("#dialog-lines input[name='left_top_arrow']").prop('checked', true);
              }
              else {
                $("#dialog-lines input[name='left_top_arrow']").prop('checked', false);
              }
              if (lines[i].right_bottom_arrow === '1' || lines[i].right_bottom_arrow === true) {
                $("#dialog-lines input[name='right_bottom_arrow']").prop('checked', true);
              }
              else {
                $("#dialog-lines input[name='right_bottom_arrow']").prop('checked', false);
              }
            }
          }
          dialogconn = $("#dialog-lines").dialog({
            autoOpen: true,
            height: 350,
            width: 450,
            title: Drupal.t("Edit Connection"),
            modal: true,
            buttons: {
              Save: updateLine,
              Clone: cloneLine,
              Cancel: function () {
                dialogconn.dialog("close");
              }
            }
          });
        });

        $('#draggable').delegate('.cell, .line', 'mouseover', function (event) {
          $(this).addClass('outline-element');
        }).delegate('.cell, .line', 'mouseout', function (event) {
          $(this).removeClass('outline-element');
        }).delegate('.cell, .line', 'click', function (event) {
          $('#draggable .cell, #draggable .line').removeClass('outline-element-clicked');
          $(this).toggleClass('outline-element-clicked');
        }).delegate('.cell.outline-element-clicked, .line.outline-element-clicked', 'dblclick', function (event) {
          $(this).find('span.edit').trigger('click');
        });

      });

    }
  };
})(jQuery, Drupal);
