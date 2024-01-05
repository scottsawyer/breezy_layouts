<?php

namespace Drupal\breezy_layouts\Plugin\Layout;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Layout\LayoutDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Layout class for Breezy Layouts.
 */
class BreezyLayouts extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Config Breakpoints.
   *
   * @var array
   */
  protected $breakpoints;

  /**
   * Layouts configuration.
   *
   * @var array
   */
  protected $layoutsConfig;

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, BreakpointManagerInterface $breakpoint_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->breakpointManager = $breakpoint_manager;
    $this->configFactory = $config_factory;
    $this->layoutsConfig = $this->configFactory->get('breezy_layouts.settings');
    $this->breakpoints = $this->getBreakpoints();

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $breakpoint_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'wrappers' => [],
        'wrapper_classes' => '',
        'wrapper_container' => TRUE,
        'wrapper_gap' => [
          'x' => 'gap-x',
          'y' => 'gap-y'
        ],
        'sizes' => [],
        'order' => [],
        'offsets' => [],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $configuration = $this->getConfiguration();
    $regions = $this->getPluginDefinition()->getRegions();
    $breakpoints = $this->breakpoints;
    $region_orders = [];
    for($i = 0; $i < count($regions); $i++) {
      $region_orders[$i] = $i;
    }

    $form['attributes'] = [
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => $this->t('Wrapper attributes'),
      '#description' => $this->t('Attributes for the outermost element.'),
      '#tree' => TRUE,
    ];

    $form['attributes']['wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper classes'),
      '#description' => $this->t('Add additional classes to the outermost element. Note: use classes as provided by your theme.  Separate multiple class names with a space.'),
      '#default_value' => $configuration['wrapper_classes'],
      '#weight' => 1,
    ];

    $form['attributes']['wrapper_gutters'] = [
      '#type' => 'container',
    ];

    $form['attributes']['wrapper_gutters']['instructions'] = [
      '#markup' => '<div class="callout warning">' . $this->t('<p>Gutters are the space between the Regions in a Layout. Gutters can be applied as "Padding", "Margin", or "None".  Choose "None" to remove the space between regions.</p>  <p>For most cases, "Padding" is the best option.</p>') . '</div>',
      '#allowed_tags' => ['div', 'p'],
    ];

    $form['attributes']['wrapper_gap']['x'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('X-axis gap'),
      '#description' => $this->t('Choose if gap will apply to regions on the X-axis.'),
      '#default_value' => $configuration['wrapper_gap']['x'] ?? TRUE,
    ];

    $form['attributes']['wrapper_gap']['y'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Y-axis gap'),
      '#description' => $this->t('Choose if gap will apply to regions on the Y-axis.'),
      '#default_value' => $configuration['wrapper_gutters']['y'] ?? TRUE,
    ];

    $form['attributes']['wrapper_container'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Contained wrapper'),
      '#description' => $this->t('When checked, the Layout wil be contained to the max width set in the theme (1200px by default).  When the screen is wider than the max width, the regions in the Layout will be centered with space equally applied to the left and right sides.'),
      '#default_value' => $configuration['wrapper_container'] ?? TRUE,
      '#weight' => 0,
    ];

    $form['regions'] = [
      '#type' => 'container',
      '#title' => $this->t('Region Configuration'),
      '#weight' => 3,
      '#tree' => TRUE,
    ];

    $form['regions']['instructions'] = [
      '#markup' => '<div class="callout warning">' . $this->t('<p>Customize each region\'s configuration.</p>') . '</div>',
      '#allowed_tags' => ['div', 'p', 'strong'],
    ];

    foreach ($regions as $region_name => $region_definition) {

      $form['regions'][$region_name] = [
        '#type' => 'details',
        '#title' => $this->t('Region: @region', ['@region' => $region_name]),
        '#description' => $this->t('Use the settings below to customize the behavior of the @region region when viewed on each of the following screen sizes.', ['@region' => $region_definition['label']]),
        '#group' => 'region_config',
        '#weight' => $i,
        '#tree' => TRUE,
        '#attributes' => [
          'data-region' => $region_name
        ],
      ];

      foreach ($breakpoints as $bp_name => $bp_settings) {
        $breakpoint_settings = $this->getBreakpointSettings($bp_name);

        $form['regions'][$region_name][$bp_name] = [
          '#type' => 'details',
          '#title' => $this->t('@label settings', ['@label' => $bp_settings['label']]),
          '#description' => $this->t('These settings apply to the @region region when viewed on @size sized screens.', [
            '@region' => $region_definition['label'],
            '@size' => $bp_settings['label'],
          ]),
          '#tree' => TRUE,
          '#attributes' => [
            'data-breakpoint' => $bp_name
          ],
        ];

        $region_sizes = $this->getRegionSizes($bp_name);
        if (count($region_sizes) > 1) {
          $form['regions'][$region_name][$bp_name]['size'] = [
            '#type' => 'select',
            '#title' => $this->t('@label columns', ['@label' => $bp_settings['label']]),
            '#description' => $this->t('Columns define the width or span of each region.  Choose the number of columns the @region region will span when viewed on @label screens.<p><strong>Note:</strong> This layout consists of @columns equal width columns.  The total number of columns for all regions, must total @columns or less.</p>', [
              '@region' => $region_definition['label'],
              '@label' => $bp_settings['label'],
              '@columns' => $breakpoint_settings['columns'],
            ]),
            '#options' => $this->getRegionSizes($bp_name),
            '#default_value' => $configuration['sizes'][$region_name][$bp_name] ?? 'basis-auto',
            '#required' => TRUE,
          ];
        }
        elseif (count($region_sizes) == 1) {
          $form['regions'][$region_name][$bp_name]['size'] = [
            '#type' => 'hidden',
            '#value' => key($region_sizes),
          ];
        }


        /*
        if (count($region_offsets) > 1) {
          $form['regions'][$region_name][$bp_name]['offset'] = [
            '#type' => 'select',
            '#title' => $this->t('@label offset', ['@label' => $bp_label]),
            '#description' => $this->t('Offsets are empty columns at the beginning of each region.  Choose the number of columns to offset the @region region when viewed on @label screens.', [
              '@region' => $region_definition['label'],
              '@label' => $bp_label,
            ]),
            '#options' => $region_offsets,
            '#default_value' => $configuration['offsets'][$region_name][$bp_name] ?? 0,
          ];
        }
        elseif (count($region_offsets) == 1) {
          $form['regions'][$region_name][$bp_name]['offset'] = [
            '#type' => 'hidden',
            '#value' => key($region_offsets),
          ];
        }
        else {
          $form['regions'][$region_name][$bp_name]['offset'] = [
            '#type' => 'hidden',
            '#value' => 0,
          ];
        }
        /**/
        $order_options = $this->getRegionOrderOptions($region_orders, $bp_name);
        if (count($regions) > 1 && count($region_orders)) {
          $form['regions'][$region_name][$bp_name]['order'] = [
            '#type' => 'radios',
            '#title' => $this->t('@label order', ['@label' => $bp_settings['label']]),
            '#description' => $this->t('Regions with lower values for Order appear before columns with higher values. Choose the order of the @region region when viewed on @label screens.', [
              '@region' => $region_definition['label'],
              '@label' => $bp_settings['label'],
            ]),
            '#options' => $order_options,
            '#default_value' => $configuration['orders'][$region_name][$bp_name] ?? '',
            '#required' => TRUE,
          ];
        }
        elseif (count($region_orders) == 1) {
          $form['regions'][$region_name][$bp_name]['order'] = [
            '#type' => 'hidden',
            '#value' => key($order_options),
          ];
        }
        else {
          $form['regions'][$region_name][$bp_name]['order'] = [
            '#type' => 'hidden',
            '#value' => 'order-0',
          ];
        }

      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    /*
    $breakpoints = $this->breakpoints;
    $regions = $form_state->getValue('regions');
    foreach ($regions as $region_name) {
      foreach ($breakpoints as $bp => $breakpoint) {
        $column_count = $regions[$region_name][$bp]['size'] + $regions[$region_name][$bp]['offset'];
        if ($column_count > 12) {
          $form_state->setErrorByName("regions][$region_name][$bp]['size']", $this->t("The total columns, including offsets, can not total more than 12."));
        }
      }
    }
    /**/
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $breakpoints = $this->breakpoints;

    $this->configuration['attributes'] = $form_state->getValue('attributes');
    foreach (['wrapper_classes', 'wrapper_container', 'wrapper_gutters'] as $name) {
      $this->configuration[$name] = $this->configuration['attributes'][$name];
      unset($this->configuration['attributes'][$name]);
    }

    $regions = $form_state->getValue('regions');

    foreach ($regions as $region_name => $region_config) {
      $this->configuration['sizes'][$region_name] = [];
      $this->configuration['orders'][$region_name] = [];
      foreach ($breakpoints as $bp => $breakpoint_settings) {
        $this->configuration['sizes'][$region_name][] = $region_config[$bp]['size'];
        //$this->configuration['offsets'][$region_name][$bp] = $region_config[$bp]['offset'];
        $this->configuration['orders'][$region_name][] = $region_config[$bp]['order'];
      }
    }
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Returns an array of breakpoints.
   *
   * @return array
   *   An array of enabled breakpoints.
   */
  public function getBreakpoints() : array {
    $breakpoint_group = $this->layoutsConfig->get('breakpoint_group');
    $breakpoint_group_breakpoints = $this->breakpointManager->getBreakpointsByGroup($breakpoint_group);

    $config = $this->layoutsConfig->get('breakpoints');

    $available_breakpoints = [];
    foreach ($config as $bp_name => $bp_settings) {
      if (!$bp_settings['enabled']) {
        continue;
      }
      $bp_name = str_replace('__', '.', $bp_name);

      if (!isset($breakpoint_group_breakpoints[$bp_name])) {
        continue;
      }
      $key = str_replace($breakpoint_group . '.', '', $bp_name);
      $bp_settings['label'] = $breakpoint_group_breakpoints[$bp_name]->getLabel();
      $available_breakpoints[$key] = $bp_settings;
    }

    return $available_breakpoints;
  }

  /**
   * Get breakpoint settings.
   *
   * @param string $breakpoint
   *   The breakpoint.
   *
   * @return array
   *   The config settings for the breakpoint.
   */
  protected function getBreakpointSettings(string $breakpoint) {
    $breakpoint_group = $this->layoutsConfig->get('breakpoint_group');
    $config = $this->layoutsConfig->get('breakpoints');
    $settings = [];
    foreach ($config as $bp_name => $bp_settings) {
      $key = str_replace($breakpoint_group . '__', '', $bp_name);
      if (isset($bp_settings['enabled']) && $bp_settings['enabled']) {
        $settings[$key] = $bp_settings;
      }
    }
    if (isset($breakpoint, $settings)) {
      return $settings[$breakpoint];
    }

  }

  /**
   * Returns an array of region_sizes.
   *
   * @param string $breakpoint
   *   The breakpoint.
   *
   * @return array
   *   An array of region sizes.
   */
  private function getRegionSizes(string $breakpoint) : array {
    $breakpoints = $this->breakpoints;
    $available_sizes = [];

    if (isset($breakpoints[$breakpoint])) {
      $breakpoint_settings = $breakpoints[$breakpoint];
    }
    else {
      return [];
    }
    //$breakpoint_settings = $this->getBreakpointSettings($breakpoint);


    if (isset($breakpoint_settings['available_sizes'])) {
      $available_sizes = [];
      foreach ($breakpoint_settings['available_sizes'] as $size) {
        if (!empty($breakpoint_settings['prefix'])) {
          $key = $breakpoint_settings['prefix'] . ':' . $size;
          $available_sizes[$key] = $size;
        }
        else {
          $available_sizes[$size] = $size;
        }
      }
      return $available_sizes;
    }
    return [];
  }

  /**
   * Get the order options per breakpoint.
   *
   * @param array $orders
   *   The orders (based on number of regions).
   * @param string $breakpoint
   *   The current breakpoint.
   *
   * @return array
   *   An array of order options.
   */
  protected function getRegionOrderOptions(array $orders, string $breakpoint) : array {
    $breakpoint_settings = $this->getBreakpointSettings($breakpoint);
    $region_orders = [];
    foreach ($orders as $order_key => $order_value) {
      if (!empty($breakpoint_settings['prefix'])) {
        $key = $breakpoint_settings['prefix'] . ':order-' . $order_key;
        $region_orders[$key] = $order_value;
      }
      else {
        $key = 'order-' . $order_key;
        $region_orders[$key] = $order_value;
      }
    }
    return $region_orders;
  }

  /**
   * Returns an array of region_offsets.
   *
   * @return array
   *   An array of region offsets.
   */
  private function getRegionOffsets() : array {
    $config = $this->layoutsConfig->get('available_offsets');
    // Offsets.
    $region_offsets = [
      0 => $this->t('0 Columns'),
      1 => $this->t('1 Column'),
      2 => $this->t('2 Columns'),
      3 => $this->t('3 Columns'),
      4 => $this->t('4 Columns'),
      5 => $this->t('5 Columns'),
      6 => $this->t('6 Columns'),
      7 => $this->t('7 Columns'),
      8 => $this->t('8 Columns'),
      9 => $this->t('9 Columns'),
      10 => $this->t('10 Columns'),
      11 => $this->t('11 Columns'),
    ];

    $available_offsets = [];
    foreach ($region_offsets as $key => $value) {
      if ((bool) $config[$key] == TRUE) {
        $available_offsets[$key] = $value;
      }
    }
    return $available_offsets;
  }

}
