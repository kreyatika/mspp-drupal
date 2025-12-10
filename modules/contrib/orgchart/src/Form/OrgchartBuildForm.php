<?php

namespace Drupal\orgchart\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Orgchart Build Form.
 */
class OrgchartBuildForm extends FormBase {

  /**
   * The current route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current orgchart.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $orgchart;

  /**
   * The current display.
   *
   * @var string
   */
  protected $display;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->orgchart = $this->configFactory->getEditable('orgchart.charts.' . $this->routeMatch->getParameter('id'));
    $this->display = $this->routeMatch->getParameter('display');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $id = NULL) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'orgchart_build_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    foreach ($this->orgchart->get('config') as $key => $config) {
      if (isset($config['active']) && $config['active'] == 1) {
        if ($key == $this->display) {
          $display_config = $config;
        }
      }
    }

    if (!empty($display_config)) {
      $build = $this->orgchart->get('build');
      $form['build'] = [
        '#type' => 'details',
        '#title' => $this->t('Orgchart (@type)', ['@type' => $this->display]),
        '#open' => TRUE,
      ];

      $form['build']['height'] = [
        '#type' => 'textfield',
        '#default_value' => (!empty($build[$this->display]['height'])) ? $build[$this->display]['height'] : (intval($display_config['width']) * 2),
        '#weight' => 99,
        '#attributes' => [
          'class' => ['visually-hidden'],
        ],
      ];
      $form['build']['values'] = [
        '#type' => 'textarea',
        '#default_value' => (!empty($build[$this->display]['values'])) ? json_encode($build[$this->display]['values']) : '',
        '#weight' => 99,
        '#attributes' => [
          'class' => ['visually-hidden'],
        ],
      ];

      $form['build']['add_point'] = [
        '#type' => 'button',
        '#value' => $this->t('New cell'),
      ];
      $this->addPointForm($form);

      $form['build']['add_conn'] = [
        '#type' => 'button',
        '#value' => $this->t('New connection'),
      ];
      $this->addLineForm($form);

      $form['build']['area'] = [
        '#markup' => Markup::create('<div style="height:' . $form['build']['height']['#default_value'] . 'px;width:' . intval($display_config['width']) . 'px;" id="draggable" class="ui-widget-content"></div>'),
      ];

      $form['#attached'] = [
        'library' => [
          'orgchart/orgchart.admin',
          'orgchart/orgchart.colors',
        ],
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function addPointForm(&$form) {
    $form['config']['add_point_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'dialog-cell',
        'title' => $this->t('New cell'),
      ],
    ];

    $form['config']['add_point_container']['id'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];

    $form['config']['add_point_container']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => '',
      '#size' => 30,
    ];

    $form['config']['add_point_container']['subtitle'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => '',
      '#rows' => 3,
    ];

    $form['config']['add_point_container']['link'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Link'),
      '#target_type' => 'node',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'filter' => [
          'status' => TRUE,
        ],
      ],
      '#process_default_value' => FALSE,
      '#attributes' => [
        'data-autocomplete-first-character-blacklist' => '/#?',
      ],
    ];

    $form['config']['add_point_container']['container'] = [
      '#type' => 'details',
      '#title' => $this->t('Style'),
      '#open' => TRUE,
    ];
    $form['config']['add_point_container']['container']['style'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['og-container-inline'],
      ],
    ];

    $form['config']['add_point_container']['container']['style']['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Level'),
      '#default_value' => '1',
      '#options' => [
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
      ],
    ];
    $form['config']['add_point_container']['container']['style']['fontweight'] = [
      '#type' => 'select',
      '#title' => $this->t('Font Weight'),
      '#default_value' => 'normal',
      '#options' => [
        'normal' => $this->t('Normal'),
        'bold' => $this->t('Bold'),
      ],
    ];

    $form['config']['add_point_container']['container']['style']['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Text size'),
      '#default_value' => 'normal',
      '#options' => [
        'pequena' => $this->t('Small'),
        'normal' => $this->t('Normal'),
        'grande' => $this->t('Big'),
      ],
    ];

    $form['config']['add_point_container']['container']['overrides'] = [
      '#type' => 'details',
      '#title' => $this->t('Overrides'),
      '#open' => FALSE,
    ];
    $form['config']['add_point_container']['container']['overrides']['style'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['og-container-inline'],
      ],
    ];

    $form['config']['add_point_container']['container']['overrides']['style']['bgcolor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BG Color'),
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => '#',
      ],
      '#size' => 10,
    ];

    $form['config']['add_point_container']['container']['overrides']['style']['color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text Color'),
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => '#',
      ],
      '#size' => 10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  private function addLineForm(&$form) {
    $form['config']['add_line_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'dialog-lines',
        'title' => $this->t('New line'),
      ],
    ];

    $form['config']['add_line_container']['lineid'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];

    $form['config']['add_line_container']['container'] = [
      '#type' => 'container',
    ];

    $form['config']['add_line_container']['container']['container1'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['og-container-inline'],
      ],
    ];

    $form['config']['add_line_container']['container']['container1']['orientation'] = [
      '#type' => 'select',
      '#title' => $this->t('Orientation'),
      '#default_value' => 'horizontal',
      '#options' => [
        'horizontal' => $this->t('Horizontal'),
        'vertical' => $this->t('Vertical'),
      ],
    ];

    $form['config']['add_line_container']['container']['container1']['linetype'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#default_value' => 'normal',
      '#options' => [
        'normal' => $this->t('Normal'),
        'dashed' => $this->t('Dashed'),
      ],
    ];

    $form['config']['add_line_container']['container']['container2'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['og-container-inline'],
      ],
    ];

    $form['config']['add_line_container']['container']['container2']['left_top_arrow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Left/Top Arrow'),
    ];

    $form['config']['add_line_container']['container']['container2']['right_bottom_arrow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Right/Bottom Arrow'),
    ];

    $form['config']['add_line_container']['overrides'] = [
      '#type' => 'details',
      '#title' => $this->t('Overrides'),
      '#open' => FALSE,
    ];

    $form['config']['add_line_container']['overrides']['linecolor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Line color'),
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => '#',
      ],
      '#size' => 10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $build = $this->orgchart->get('build');

    if (!empty($values['values'])) {
      $values['values'] = json_decode($values['values'], TRUE);
    }

    if (!empty($values['values']['cells'])) {
      foreach ($values['values']['cells'] as $key => $value) {
        if (!empty($value['link'])) {
          try {
            $uri = static::getUserEnteredStringAsUri($value['link']);
            $url = Url::fromUri($uri)->toString();
            $values['values']['cells'][$key]['link'] = $url;
          }
          catch (\Throwable $th) {
            $values['values']['cells'][$key]['link'] = '';
          }
        }
      }
    }

    $build[$this->display]['height'] = $values['height'];
    $build[$this->display]['values'] = $values['values'];

    $this->orgchart->set('build', $build);
    $this->orgchart->save();
    Cache::invalidateTags(['orgchart.charts.' . $this->routeMatch->getParameter('id')]);
  }

  /**
   * {@inheritdoc}
   */
  protected static function getUserEnteredStringAsUri($string) {
    $uri = trim($string);

    $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);
    if ($entity_id !== NULL) {
      $uri = 'entity:node/' . $entity_id;
    }
    elseif (in_array($string, ['<nolink>', '<none>'], TRUE)) {
      $uri = 'route:' . $string;
    }
    elseif (!empty($string) && parse_url($string, PHP_URL_SCHEME) === NULL) {
      if (strpos($string, '<front>') === 0) {
        $string = '/' . substr($string, strlen('<front>'));
      }
      if (!str_starts_with($string, '/')) {
        $string = '/' . $string;
      }
      $uri = 'internal:' . $string;
    }

    return $uri;
  }

}
