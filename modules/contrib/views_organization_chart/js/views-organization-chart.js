(function ($, Drupal, drupalSettings, once) {

  let config = {
    chart: {
      height: 600,
      inverted: true
    },
    title: {
      text: Drupal.t('Organization Chart')
    },
    accessibility: {
      point: {
        descriptionFormat: '{add index 1}. {toNode.name}' +
         '{#if (ne toNode.name toNode.id)}, {toNode.id}{/if}, ' +
         'reports to {fromNode.id}'
     }
    },
    series: [{
      type: 'organization',
      keys: ['from', 'to'],
      colorByPoint: false,
      color: '#007ad0',
      dataLabels: {
        color: 'white'
      },
      borderColor: 'white',
      nodeWidth: 'auto',
    }],
    tooltip: {
      outside: true
    },
    exporting: {
      allowHTML: true,
      sourceWidth: 800,
      sourceHeight: 600
    }
  };
  Drupal.behaviors.organization = {
    attach: function attach(context) {
      $(once('organization', ".views-view-organization", context))
        .each(function () {
          let id = $(this).attr('id');
          config.series[0].levels = drupalSettings[id].levels;
          config.series[0].nodes = drupalSettings[id].nodes;
          config.series[0].data = drupalSettings[id].data;
          config.series[0].name = drupalSettings[id].title;
          config.title.text = drupalSettings[id].title;
          Highcharts.chart(id, config);
        });
    }
  };
}(jQuery, Drupal, drupalSettings, once));
