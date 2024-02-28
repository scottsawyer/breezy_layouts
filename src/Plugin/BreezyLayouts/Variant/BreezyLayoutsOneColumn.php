<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant;

use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breakpoint\BreakpointManagerInterface;
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

    $breakpoint_group = $this->breezyLayoutsSettings->get('breakpoint_group');
    if ($breakpoint_group) {
      $form_state->set('breakpoint_group', $breakpoint_group);
      $form['breakpoint_group'] = [
        '#type' => 'hidden',
        '#value' => $breakpoint_group,
      ];
    }

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
        // Breakpoints may be represented with a dot ".", which is illegal as a
        // key.
        // Convert the dot to double underscore, but convert back.
        // @todo Create a method that properly sanitizes separaters.
        $breakpoint_name = str_replace('.', '__', $breakpoint_name);
        $breakpoint_wrapper_id = 'breakpoints-' . $breakpoint_name;
        $enabled = FALSE;
        if (isset($this->configuration['breakpoints'][$breakpoint_name]['enabled'])) {
          $enabled = $this->configuration['breakpoints'][$breakpoint_name]['enabled'];
        }
        $form['breakpoints'][$breakpoint_name] = [
          '#type' => 'details',
          '#title' => $breakpoint->getLabel(),
          '#tree' => TRUE,
          '#prefix' => '<div id="' . $breakpoint_wrapper_id . '">',
          '#suffix' => '</div>',
          '#open' => $enabled,
        ];

        $breakpoints_form_state = $form_state->get(['plugin_configuration', 'breakpoints']);
        if ($breakpoints_form_state && isset($breakpoints_form_state[$breakpoint_name]['enabled'])) {
          $enabled = $breakpoints_form_state[$breakpoint_name]['enabled'];
        }
        $form['breakpoints'][$breakpoint_name]['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable'),
          '#default_value' => $enabled,
          '#ajax' => [
            'wrapper' => $breakpoint_wrapper_id,
            'callback' => [$this, 'pluginCallback'],
          ],
          '#breakpoint_name' => $breakpoint_name,
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

            $prefix = $this->getPrefixForBreakpoint($breakpoint_name);

            if (isset($breakpoint_settings['container']['properties']) && !empty($breakpoint_settings['container']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['container'] = [
                '#type' => 'fieldset',
                '#title' => $this->t('Container'),
                '#description' => $this->t('Container settings'),
              ];
              foreach ($breakpoint_settings['container']['properties'] as $property_name => $property_values) {
                $element = $property_values['element'];
                $default_value = '';
                if (isset($default_settings['breakpoints'][$breakpoint_name]['container'][$property_name])) {
                  $default_value = $default_settings['breakpoints'][$breakpoint_name]['container'][$property_name];
                }
                $form['breakpoints'][$breakpoint_name]['container'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
              }
            }

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
