<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Breezy Layouts One Column Layout Variant plugin.
 *
 * @BreezyLayoutsVariantPlugin(
 *   id = "breezy_one_column",
 *   label = @Translation("Breezy one column"),
 *   description = @Translation("Provides a variant plugin for Breezy one
 *   column layout"), layout = "breezy-one-column",
 *   container = TRUE,
 *   wrapper = TRUE,
 * )
 */
class BreezyLayoutsOneColumn extends BreezyLayoutsVariantPluginBase {

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassesServiceInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClasses;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * BreezyLayouts settings.
   *
   * @var array
   */
  protected $breezyLayoutsSettings;

  /**
   * Constructs a new BreezyLayoutsOneColumn plugin object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager service.
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes
   *   The tailwind classes service.
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *    The layout plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreakpointManagerInterface $breakpoint_manager, BreezyLayoutsTailwindClassServiceInterface $tailwind_classes, LayoutPluginManagerInterface $layout_plugin_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $layout_plugin_manager, $breakpoint_manager, $config_factory);
    $this->configuration += $this->defaultConfiguration();
    $this->breakpointManager = $breakpoint_manager;
    $this->tailwindClasses = $tailwind_classes;
    $this->configFactory = $config_factory;
    $this->breezyLayoutsSettings = $config_factory->get('breezy_layouts.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $tailwind_classes = $container->get('breezy_layouts.tailwind_classes');
    /** @var \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager */
    $layout_plugin_manager = $container->get('plugin.manager.core.layout');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $breakpoint_manager,
      $tailwind_classes,
      $layout_plugin_manager,
      $config_factory
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'breakpoint_group' => '',
      'container' => [],
      'wrapper' => [],
      'main' => [],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $variant = $form_state->get('variant');
    $breakpoints_wrapper_id = 'breakpoints-wrapper';
    $form['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Debug'),
    ];
    $form['debug']['config'] = [
      '#markup' => 'configuration: <pre>' . print_r($this->configuration, TRUE) . '</pre>',
      '#allowed_tags' => ['pre'],
    ];

    $breakpoint_group = FALSE;


    $breakpoint_group = $this->breezyLayoutsSettings->get('breakpoint_group');
    if ($breakpoint_group) {
      $form_state->set('breakpoint_group', $breakpoint_group);
      $form['breakpoint_group'] = [
        '#type' => 'hidden',
        '#value' => $breakpoint_group,
      ];
    }

    /*
    if ($this->configuration['breakpoint_group']) {
      $breakpoint_group = $this->configuration['breakpoint_group'];
      $form_state->set('breakpoint_group', $breakpoint_group);
      $form['breakpoint_group'] = [
        '#type' => 'hidden',
        '#value' => $breakpoint_group,
      ];
    }
    /**/

    /*
    $form['breakpoint_group'] = [
      '#type' => 'select',
      '#title' => $this->t('Breakpoint group'),
      '#default_value' => $breakpoint_group ?? '',
      '#options' => $this->breakpointManager->getGroups(),
      '#required' => TRUE,
      '#empty_option'=>$this->t('- Select -'),
      '#ajax' => [
        'callback' => [$this, 'changeBreakpointGroup'],
        'wrapper' => $breakpoints_wrapper_id,
      ],
    ];
    /**/

    $form['breakpoints'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Breakpoints'),
      '#prefix' => '<div id="' . $breakpoints_wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $breakpoint_group = $form_state->get('breakpoint_group');
    if ($form_state->getValue('breakpoint_group')) {
      $breakpoint_group = $form_state->getValue('breakpoint_group');
    }

    if (!empty($breakpoint_group)) {

      $breakpoint_group_breakpoints = $this->breakpointManager->getBreakpointsByGroup($breakpoint_group);

      foreach ($breakpoint_group_breakpoints as $breakpoint_name => $breakpoint) {
        // Breakpoints are represented with a dot ".", which is illegal.
        // Convert the dot to double underscore, but convert back.
        $breakpoint_name = str_replace('.', '__', $breakpoint_name);
        $breakpoint_wrapper_id = 'breakpoints-' . $breakpoint_name;
        $form['breakpoints'][$breakpoint_name] = [
          '#type' => 'details',
          '#title' => $breakpoint->getLabel(),
          '#tree' => TRUE,
          '#prefix' => '<div id="' . $breakpoint_wrapper_id . '">',
          '#suffix' => '</div>',
          '#open' => $this->configuration['breakpoints'][$breakpoint_name]['enabled'] ?? FALSE,
        ];

        $form['breakpoints'][$breakpoint_name]['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['enabled'] ?? FALSE,
        ];

        $form['breakpoints'][$breakpoint_name]['prefix'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Breakpoint prefix'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['prefix'] ?? '',
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['container'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Container'),
          '#description' => $this->t('To omit the container, do not add any properties.'),
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];
        // Parent key.
        $parent_array = [
          'breakpoints',
          $breakpoint_name,
          'container',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        // Display properties.
        $form['breakpoints'][$breakpoint_name]['container']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['container']['add_property'] = $this->addPropertyLink($variant, $parent_array);


        $form['breakpoints'][$breakpoint_name]['wrapper'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Wrapper'),
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];
        // Parent key.
        $parent_array = [
          'breakpoints',
          $breakpoint_name,
          'wrapper',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        // Display properties.
        $form['breakpoints'][$breakpoint_name]['wrapper']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['wrapper']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['main'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Main region'),
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        // Parent key.
        $parent_array = [
          'breakpoints',
          $breakpoint_name,
          'main',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['main']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['main']['add_property'] = $this->addPropertyLink($variant, $parent_array);

      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue('plugin_configuration');
    $this->configuration['breakpoint_group'] = $values['breakpoint_group'];
    $this->configuration['container'] = $values['container'];
    $this->configuration['breakpoints'] = $values['breakpoints'];
  }

  /**
   * Callback for Breakpoint group selection.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function changeBreakpointGroup(array &$form, FormStateInterface $form_state) {
    $breakpoint_group = $form_state->getValue(['plugin_configuration', 'breakpoint_group']);
    $form_state->set('breakpoint_group', $breakpoint_group);
    $form_state->setRebuild();
    return $form['plugin_configuration']['breakpoints'];
  }

  /**
   * Callback for columns selection.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state.
   *
   * @return array
   */
  public function columnsCallback(array &$form, FormStateInterface $form_state) {
    return $form['plugin_configuration']['breakpoints'];
  }

  /**
   * Callback for components selection.
   *
   * @param array $form
   *   The form array
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array;
   */
  public function changeOverrides(array &$form, FormStateInterface $form_state) {
    $components = $form_state->getValue(['plugin_configuration', 'overrides', 'override_components']);
    $form_state->set('overrides', $components);
    return $form['plugin_configuration']['overrides']['components'];
  }

  /**
   * Maps the parent child relationship of css properties.
   *
   * Certain CSS properties only make sense at certain levels (parent / child).
   *
   * @param string|null $level
   *   The level of the parent child tree.
   *
   * @return array
   *   An array of properties that apply at the provided level.
   */
  protected function cssPropertyMap($level = NULL) : array {
    $properties = $this->tailwindClasses->getPropertyMap();


    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function layoutForm(array $form, FormStateInterface $form_state) {
    $default_settings = $form_state->get('default_settings');
    $breakpoint_group = $this->configuration['breakpoint_group'];
    $breakpoint_groups_breakpoints = $this->breakpointManager->getBreakpointsByGroup($breakpoint_group);
    $breakpoints = NULL;
    if (isset($this->configuration['breakpoints'])) {
      $breakpoints = $this->configuration['breakpoints'];
    }

    if ($breakpoints) {
      $form['breakpoints'] = [
        '#type' => 'container',
      ];

      foreach ($breakpoints as $breakpoint_name => $breakpoint_settings) {

        if ($breakpoint_settings['enabled']) {
          $breakpoint_name_converted = str_replace('__', '.', $breakpoint_name);

          if (isset($breakpoint_groups_breakpoints[$breakpoint_name_converted])) {
            $form['breakpoints'][$breakpoint_name] = [
              '#type' => 'details',
              '#title' => $breakpoint_groups_breakpoints[$breakpoint_name_converted]->getLabel(),
            ];

            $prefix = '';
            if (isset($breakpoint_settings['prefix'])) {
              $prefix = $breakpoint_settings['prefix'];
            }

            $form['breakpoints'][$breakpoint_name]['prefix'] = [
              '#type' => 'value',
              '#value' => $prefix,
            ];

            // Wrapper.
            if (isset($breakpoint_settings['wrapper']['properties']) && !empty($breakpoint_settings['wrapper']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['wrapper'] = [
                '#type' => 'fieldset',
                '#title' => $this->t('Wrapper'),
                '#description' => $this->t('Wrapper settings'),
              ];
              foreach ($breakpoint_settings['wrapper']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $type = $property_values['element']['type'];
                  $element = $property_values['element'];
                  $default_value = '';
                  if (isset($default_settings['breakpoints'][$breakpoint_name]['wrapper'][$property_name])) {
                    $default_value = $default_settings['breakpoints'][$breakpoint_name]['wrapper'][$property_name];
                  }
                  $form['breakpoints'][$breakpoint_name]['wrapper'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
              }

            }

            // Regions.
            if (isset($breakpoint_settings['main']['properties']) && !empty($breakpoint_settings['main']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['main'] = [
                '#type' => 'fieldset',
                '#title' => $this->t('Main'),
                '#description' => $this->t('Main settings'),
              ];
              foreach ($breakpoint_settings['main']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $type = $property_values['element']['type'];
                  $element = $property_values['element'];
                  $default_value = '';
                  if (isset($default_settings['breakpoints'][$breakpoint_name]['main'][$property_name])) {
                    $default_value = $default_settings['breakpoints'][$breakpoint_name]['main'][$property_name];
                  }
                  $form['breakpoints'][$breakpoint_name]['main'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
              }
            }


          }
        }
      }
    }
    return $form;
  }
}
