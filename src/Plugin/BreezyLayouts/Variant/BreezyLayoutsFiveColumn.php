<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Utility\BreezyLayoutsBreakpointHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Breezy Layouts Four Column Layout Variant plugin.
 *
 * @BreezyLayoutsVariantPlugin(
 *   id = "breezy_five_column",
 *   label = @Translation("Breezy five column"),
 *   description = @Translation("Provides a variant plugin for Breezy five
 *   column layout"),
 *   layout = "breezy-five-column",
 *   container = TRUE,
 *   wrapper = TRUE,
 * )
 */
class BreezyLayoutsFiveColumn extends BreezyLayoutsVariantPluginBase implements BreezyLayoutsVariantPluginInterface {

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $element_plugin_manager
   *   The element plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreakpointManagerInterface $breakpoint_manager, ConfigFactoryInterface $config_factory, BreezyLayoutsElementPluginManagerInterface $element_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $element_plugin_manager);
    $this->configuration += $this->defaultConfiguration();
    $this->breakpointManager = $breakpoint_manager;
    $this->configFactory = $config_factory;
    $this->breezyLayoutsSettings = $config_factory->get('breezy_layouts.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $element_plugin_manager $element_plugin_manager */
    $element_plugin_manager = $container->get('plugin.manager.breezy_layouts.element');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $breakpoint_manager,
      $config_factory,
      $element_plugin_manager
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
        'left_outer' => [],
        'left_inner' => [],
        'center' => [],
        'right_outer' => [],
        'right_inner' => [],
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

        $breakpoint_name = BreezyLayoutsBreakpointHelper::getSanitizedBreakpointName($breakpoint_name);
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
          //'plugin_configuration',
          'breakpoints',
          $breakpoint_name,
          'wrapper',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        // Display properties.
        $form['breakpoints'][$breakpoint_name]['wrapper']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['wrapper']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['left_outer'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Left outer region'),
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
          'left_outer',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['left_outer']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['left_outer']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['left_inner'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Left inner region'),
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
          'left_inner',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['left_inner']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['left_inner']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['center'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Center region'),
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
          'center',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['center']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['center']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['right_inner'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Right inner region'),
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
          'right_inner',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['right_inner']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['right_inner']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['right_outer'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Right outer region'),
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
          'right_outer',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['right_outer']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['right_outer']['add_property'] = $this->addPropertyLink($variant, $parent_array);

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
        // If there are elements with a UI, set all containers to visible.

        if ($breakpoint_settings['enabled']) {
          $breakpoint_name_converted = BreezyLayoutsBreakpointHelper::getOriginalBreakpointName($breakpoint_name);

          if (isset($breakpoint_groups_breakpoints[$breakpoint_name_converted])) {

            $form['breakpoints'][$breakpoint_name] = [
              '#type' => 'container',
              '#title' => $breakpoint_groups_breakpoints[$breakpoint_name_converted]->getLabel(),
            ];

            $breakpoint_has_ui = FALSE;
            $prefix = $this->getPrefixForBreakpoint($breakpoint_name);

            // Container.
            if (isset($breakpoint_settings['container']['properties']) && !empty($breakpoint_settings['container']['properties'])) {

              $form['breakpoints'][$breakpoint_name]['container'] = [
                '#type' => 'container',
                '#title' => $this->t('Container'),
                '#description' => $this->t('Container settings'),
              ];

              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['container']['properties'] as $property_name => $property_values) {
                $property = $property_values;
                $element = $property_values['element'];
                $default_value = NULL;
                if ($this->elementHasUi($property)) {
                  $container_has_ui = TRUE;
                  // Only set the default value if the element has a UI.
                  if (isset($default_settings['breakpoints'][$breakpoint_name]['container'][$property_name])) {
                    $default_value = $default_settings['breakpoints'][$breakpoint_name]['container'][$property_name];
                  }
                }

                $form['breakpoints'][$breakpoint_name]['container'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
              }

              if ($container_has_ui) {
                // If there are any elements with a UI, make the container a fieldset.
                $breakpoint_has_ui = TRUE;
                $form['breakpoints'][$breakpoint_name]['container']['#type'] = 'fieldset';
              }
            }

            // Wrapper.
            if (isset($breakpoint_settings['wrapper']['properties']) && !empty($breakpoint_settings['wrapper']['properties'])) {

              $form['breakpoints'][$breakpoint_name]['wrapper'] = [
                '#type' => 'container',
                '#title' => $this->t('Wrapper'),
                '#description' => $this->t('Wrapper settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['wrapper']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;
                    if (isset($default_settings['breakpoints'][$breakpoint_name]['wrapper'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['wrapper'][$property_name];
                    }
                  }

                  $form['breakpoints'][$breakpoint_name]['wrapper'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['wrapper']['#type'] = 'fieldset';
                }
              }
            }

            // Regions.
            // Left outer.
            if (isset($breakpoint_settings['left_outer']['properties']) && !empty($breakpoint_settings['left_outer']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['left_outer'] = [
                '#type' => 'container',
                '#title' => $this->t('Left outer'),
                '#description' => $this->t('Left outer settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['left_outer']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;

                    if (isset($default_settings['breakpoints'][$breakpoint_name]['left_outer'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['left_outer'][$property_name];
                    }
                  }

                  $form['breakpoints'][$breakpoint_name]['left_outer'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['left_outer']['#type'] = 'fieldset';
                }
              }
            }

            // Left inner.
            if (isset($breakpoint_settings['left_inner']['properties']) && !empty($breakpoint_settings['left_inner']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['left_inner'] = [
                '#type' => 'container',
                '#title' => $this->t('Left inner'),
                '#description' => $this->t('Left inner settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['left_inner']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;

                    if (isset($default_settings['breakpoints'][$breakpoint_name]['left_inner'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['left_inner'][$property_name];
                    }
                  }

                  $form['breakpoints'][$breakpoint_name]['left_inner'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['left_inner']['#type'] = 'fieldset';
                }
              }
            }

            // Center.
            if (isset($breakpoint_settings['center']['properties']) && !empty($breakpoint_settings['center']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['center'] = [
                '#type' => 'container',
                '#title' => $this->t('Center'),
                '#description' => $this->t('Center settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['center']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;
                    if (isset($default_settings['breakpoints'][$breakpoint_name]['center'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['center'][$property_name];
                    }
                  }
                  $form['breakpoints'][$breakpoint_name]['center'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['center']['#type'] = 'fieldset';
                }
              }
            }

            // Right inner.
            if (isset($breakpoint_settings['right_inner']['properties']) && !empty($breakpoint_settings['right_inner']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['right_inner'] = [
                '#type' => 'container',
                '#title' => $this->t('Right inner'),
                '#description' => $this->t('Right inner settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['right_inner']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;
                    if (isset($default_settings['breakpoints'][$breakpoint_name]['right_inner'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['right_inner'][$property_name];
                    }
                  }
                  $form['breakpoints'][$breakpoint_name]['right_inner'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['right_inner']['#type'] = 'fieldset';
                }
              }
            }

            // Right outer.
            if (isset($breakpoint_settings['right_outer']['properties']) && !empty($breakpoint_settings['right_outer']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['right_outer'] = [
                '#type' => 'container',
                '#title' => $this->t('Right outer'),
                '#description' => $this->t('Right outer settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['right_outer']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;
                    if (isset($default_settings['breakpoints'][$breakpoint_name]['right_outer'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['right_outer'][$property_name];
                    }
                  }
                  $form['breakpoints'][$breakpoint_name]['right_outer'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['right_outer']['#type'] = 'fieldset';
                }
              }
            }

            if ($breakpoint_has_ui) {
              $form['breakpoints'][$breakpoint_name]['#type'] = 'details';
            }
          }
        }
      }
    }

    return $form;
  }

}
