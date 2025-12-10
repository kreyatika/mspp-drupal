<?php

namespace Drupal\orgchart\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Render\Renderer;

/**
 * {@inheritdoc}
 */
class OrgchartConfigForm extends ConfigFormBase {

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $render;

  /**
   * {@inheritdoc}
   */
  public function __construct(RedirectDestinationInterface $redirect_destination, Renderer $renderer) {
    $this->redirectDestination = $redirect_destination;
    $this->render = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('redirect.destination'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orgchart_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'orgchart.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaults = $this->config('orgchart.settings')->get('defaults');

    $this->addOrgchartsTable($form);
    $this->addConfigForm($form, $defaults);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function addOrgchartsTable(&$form) {
    $orgcharts = _orgchart_get_all();
    if (!empty($orgcharts)) {
      $header = [
        $this->t('Path'),
        $this->t('Operations'),
      ];
      $rows = [];
      foreach ($orgcharts as $key => $orgchart) {
        $link = Link::fromTextAndUrl($orgchart['title'], Url::fromUri('internal:/' . $orgchart['path']))->toString();
        $operations = $this->buildActionLinks($key, $orgchart);
        $rows[] = [
          $link,
          $this->render->render($operations),
        ];
      }
      $form['orgcharts'] = [
        '#type' => 'details',
        '#title' => $this->t('Orgcharts'),
        '#open' => TRUE,
      ];
      $form['orgcharts']['table'] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildActionLinks($key, $orgchart) {
    $action_links = [];

    $display = FALSE;
    foreach ($orgchart['configs'] as $displaykey => $config) {
      if (isset($config['active']) && $config['active'] == 1) {
        $display = $displaykey;
        break;
      }
    }

    if ($display) {
      $action_links['build'] = [
        'title' => $this->t('Build'),
        'url' => Url::fromRoute('orgchart.configuration.chart.build', [
          'id' => $key,
          'display' => $display,
        ]),
      ];
    }
    $action_links['config'] = [
      'title' => $this->t('Edit Configuration'),
      'url' => Url::fromRoute('orgchart.configuration.chart.edit', ['id' => $key]),
      'query' => $this->redirectDestination->getAsArray(),
    ];
    $action_links['delete'] = [
      'title' => $this->t('Delete'),
      'url' => Url::fromRoute('orgchart.configuration.chart.delete', ['id' => $key]),
      'query' => $this->redirectDestination->getAsArray(),
    ];

    return [
      '#type' => 'dropbutton',
      '#links' => $action_links,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function addConfigForm(&$form, $defaults) {
    $form['defaults'] = [
      '#type' => 'details',
      '#title' => $this->t('Default Configuration'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];

    $form['defaults']['colors'] = [
      '#type' => 'details',
      '#title' => $this->t('Colors'),
      '#open' => FALSE,
    ];
    $form['defaults']['colors']['bgcolor'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cell Background Color'),
      '#size' => 10,
      '#default_value' => (!empty($defaults['colors']['bgcolor'])) ? $defaults['colors']['bgcolor'] : '#064771',
    ];
    $form['defaults']['colors']['textcolor'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cell Text Color'),
      '#size' => 10,
      '#default_value' => (!empty($defaults['colors']['textcolor'])) ? $defaults['colors']['textcolor'] : '#ffffff',
    ];
    $form['defaults']['colors']['textsize'] = [
      '#type' => 'number',
      '#required' => TRUE,
      '#title' => $this->t('Cell Text Size (px)'),
      '#step' => 1,
      '#min' => 13,
      '#default_value' => (!empty($defaults['colors']['textsize'])) ? $defaults['colors']['textsize'] : '15',
    ];
    $form['defaults']['colors']['descbgcolor'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cell Description Background Color'),
      '#size' => 10,
      '#default_value' => (!empty($defaults['colors']['descbgcolor'])) ? $defaults['colors']['descbgcolor'] : '#000',
    ];
    $form['defaults']['colors']['desctextcolor'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cell Description Text Color'),
      '#size' => 10,
      '#default_value' => (!empty($defaults['colors']['desctextcolor'])) ? $defaults['colors']['desctextcolor'] : '#ffffff',
    ];
    $form['defaults']['colors']['linebgcolor'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Line Background Color'),
      '#size' => 10,
      '#default_value' => (!empty($defaults['colors']['linebgcolor'])) ? $defaults['colors']['linebgcolor'] : '#064771',
    ];
    $form['defaults']['colors']['levels'] = [
      '#type' => 'details',
      '#title' => $this->t('Levels'),
      '#open' => TRUE,
    ];
    for ($i = 1; $i < 6; $i++) {
      $form['defaults']['colors']['levels'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('Level @count', ['@count' => $i]),
        '#open' => FALSE,
      ];
      $form['defaults']['colors']['levels'][$i]['bgcolor'] = [
        '#type' => 'textfield',
        '#required' => TRUE,
        '#title' => $this->t('Background Color'),
        '#size' => 10,
        '#default_value' => (!empty($defaults['colors']['levels'][$i]['bgcolor'])) ? $defaults['colors']['levels'][$i]['bgcolor'] : '#064771',
      ];
      $form['defaults']['colors']['levels'][$i]['textcolor'] = [
        '#type' => 'textfield',
        '#required' => TRUE,
        '#title' => $this->t('Text Color'),
        '#size' => 10,
        '#default_value' => (!empty($defaults['colors']['levels'][$i]['textcolor'])) ? $defaults['colors']['levels'][$i]['textcolor'] : '#fff',
      ];
    }

    $form['defaults']['desktop'] = [
      '#type' => 'details',
      '#title' => $this->t('Desktop'),
      '#open' => FALSE,
    ];
    $form['defaults']['desktop']['active'] = [
      '#type' => 'hidden',
      '#value' => 1,
    ];
    $form['defaults']['desktop']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#required' => TRUE,
      '#step' => 10,
      '#min' => 20,
      '#default_value' => (!empty($defaults['desktop']['width'])) ? $defaults['desktop']['width'] : '1200',
    ];

    $form['defaults']['tablet'] = [
      '#type' => 'details',
      '#title' => $this->t('Tablet'),
      '#open' => FALSE,
    ];
    $form['defaults']['tablet']['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => (!empty($defaults['tablet']['active'])) ? $defaults['tablet']['active'] : FALSE,
    ];
    $form['defaults']['tablet']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#step' => 10,
      '#min' => 20,
      '#default_value' => (!empty($defaults['tablet']['width'])) ? $defaults['tablet']['width'] : '900',
      '#states' => [
        'required' => [':input[name="defaults[tablet][active]"]' => ['checked' => TRUE]],
      ],
    ];

    $form['defaults']['phone'] = [
      '#type' => 'details',
      '#title' => $this->t('Phone'),
      '#open' => FALSE,
    ];
    $form['defaults']['phone']['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => (!empty($defaults['phone']['active'])) ? $defaults['phone']['active'] : FALSE,
    ];
    $form['defaults']['phone']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#step' => 10,
      '#min' => 20,
      '#default_value' => (!empty($defaults['phone']['width'])) ? $defaults['phone']['width'] : '600',
      '#states' => [
        'required' => [':input[name="defaults[phone][active]"]' => ['checked' => TRUE]],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach ($values['defaults'] as $display => $value) {
      if (isset($value['active']) && $value['active'] == 1 && empty($value['width'])) {
        $form_state->setError($form['defaults'][$display]['width'], $this->t('Width is required for an active display'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('orgchart.settings')->set('defaults', $values['defaults'])->save();
  }

}
