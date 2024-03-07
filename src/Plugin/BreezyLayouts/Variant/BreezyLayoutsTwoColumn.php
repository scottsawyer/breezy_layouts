<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Utility\BreezyLayoutsBreakpointHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Breezy Layouts Two Column Layout Variant plugin.
 *
 * @BreezyLayoutsVariantPlugin(
 *   id = "breezy_two_column",
 *   label = @Translation("Breezy two column"),
 *   description = @Translation("Provides a variant plugin for Breezy two
 *   column layout"),
 *   layout = "breezy-two-column",
 *   container = TRUE,
 *   wrapper = TRUE,
 * )
 */
class BreezyLayoutsTwoColumn extends BreezyLayoutsVariantPluginBase implements BreezyLayoutsVariantPluginInterface {

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
        'left' => [],
        'right' => [],
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

        $form['breakpoints'][$breakpoint_name]['left'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Left region'),
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
          'left',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['left']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['left']['add_property'] = $this->addPropertyLink($variant, $parent_array);

        $form['breakpoints'][$breakpoint_name]['right'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Right region'),
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
          'right',
          'properties',
        ];
        $properties = $this->getProperties($parent_array);
        $form['breakpoints'][$breakpoint_name]['right']['properties'] = $this->buildPropertiesTable($parent_array, $properties);

        $form['breakpoints'][$breakpoint_name]['right']['add_property'] = $this->addPropertyLink($variant, $parent_array);

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
            if (isset($breakpoint_settings['left']['properties']) && !empty($breakpoint_settings['left']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['left'] = [
                '#type' => 'container',
                '#title' => $this->t('Left'),
                '#description' => $this->t('Left settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['left']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;

                    if (isset($default_settings['breakpoints'][$breakpoint_name]['left'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['left'][$property_name];
                    }
                  }

                  $form['breakpoints'][$breakpoint_name]['left'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['left']['#type'] = 'fieldset';
                }
              }
            }

            if (isset($breakpoint_settings['right']['properties']) && !empty($breakpoint_settings['right']['properties'])) {
              $form['breakpoints'][$breakpoint_name]['right'] = [
                '#type' => 'container',
                '#title' => $this->t('Right'),
                '#description' => $this->t('Right settings'),
              ];
              $container_has_ui = FALSE;
              foreach ($breakpoint_settings['right']['properties'] as $property_name => $property_values) {
                if (isset($property_values['element']) && !empty($property_values['element'])) {
                  $property = $property_values;
                  $element = $property_values['element'];
                  $default_value = NULL;
                  if ($this->elementHasUi($property)) {
                    $container_has_ui = TRUE;
                    if (isset($default_settings['breakpoints'][$breakpoint_name]['right'][$property_name])) {
                      $default_value = $default_settings['breakpoints'][$breakpoint_name]['right'][$property_name];
                    }
                  }
                  $form['breakpoints'][$breakpoint_name]['right'][$property_name] = $this->buildFormElement($element, $prefix, $default_value);
                }
                if ($container_has_ui) {
                  // If there are any elements with a UI, make the container a fieldset.
                  $breakpoint_has_ui = TRUE;
                  $form['breakpoints'][$breakpoint_name]['right']['#type'] = 'fieldset';
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
