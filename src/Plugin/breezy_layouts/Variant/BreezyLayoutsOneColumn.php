<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Breezy Layouts One Column Layout Variant plugin.
 *
 * @BreezyLayoutsVariantPlugin(
 *   id = "breezy_one_column",
 *   label = @Translation("Breezy one column"),
 *   description = @Translation("Provides a variant plugin for Breezy one column layout"),
 *   layout = "breezy-one-column",
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreakpointManagerInterface $breakpoint_manager, BreezyLayoutsTailwindClassServiceInterface $tailwind_classes) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
    $this->breakpointManager = $breakpoint_manager;
    $this->tailwindClasses = $tailwind_classes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $tailwind_classes = $container->get('breezy_layouts.tailwind_classes');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $breakpoint_manager,
      $tailwind_classes
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
      'overrides' => [],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $variant = $form_state->get('variant');
    $breakpoints_wrapper_id = 'breakpoints-wrapper';

    $form['container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Container'),
      '#tree' => TRUE,
    ];
    $form['container']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Contain content'),
      '#default_value' => $this->configuration['container']['enabled'] ?? FALSE,
    ];
    $form['container']['centered'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Center container'),
      '#default_value' => $this->configuration['container']['centered'] ?? FALSE,
    ];
    $form['container']['allow_overrides'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow overrides'),
      '#default_value' => $this->configuration['container']['allow_overrides'] ?? FALSE,
    ];

    $breakpoint_group = FALSE;

    if ($this->configuration['breakpoint_group']) {
      $breakpoint_group = $this->configuration['breakpoint_group'];
      $form_state->set('breakpoint_group', $breakpoint_group);
    }

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
        $breakpoint_name = str_replace('.', '__', $breakpoint_name);
        $breakpoint_wrapper_id = 'breakpoints-' . $breakpoint_name;
        $form['breakpoints'][$breakpoint_name] = [
          '#type' => 'details',
          '#title' => $breakpoint->getLabel(),
          '#tree' => TRUE,
          '#prefix' => '<div id="' . $breakpoint_wrapper_id . '">',
          '#suffix' => '</div>',
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

        $form['breakpoints'][$breakpoint_name]['wrapper'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Wrapper'),
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        // Display properties.
        $form['breakpoints'][$breakpoint_name]['wrapper']['properties'] = [
          '#type' => 'table',
          '#sort' => TRUE,
          '#header' => [$this->t('Sort'), $this->t('Property'), $this->t('Operations')],
          '#num_lines' => '',
          '#tabledrag' => [
            [
              'action' => 'match',
              'relationship' => 'parent',
              'group' => 'row-parent-key',
              'source' => 'row-key',
              'hidden' => TRUE,
              'limit' => FALSE,
            ],
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'row-weight',
          ],
        ];
        $dialog_options = [
          'width' => 800,
        ];

        $parent_key = 'plugin_configuration[breakpoints][' . $breakpoint_name . '][wrapper]';
        $form['breakpoints'][$breakpoint_name]['wrapper']['add_property'] = [
          '#type' => 'link',
          '#title' => $this->t('Add property'),
          '#url' => Url::fromRoute('breezy_layouts_ui.property_form', ['variant', $variant->id()], ['parent' => $parent_key]),
          '#attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => json_encode($dialog_options),
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['wrapper']['gap'] = [
          '#type' => 'select',
          '#title' => $this->t('Gap'),
          '#description' => $this->t('Space between columns.'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['wrapper']['gap'] ?? 'gap-0',
          '#options' => $this->tailwindClasses->getClassOptions('gap'),
        ];

        $form['breakpoints'][$breakpoint_name]['wrapper']['additional_classes'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Additional wrapper classes'),
          '#description' => $this->t('Enter additional classes that will be added to the flex wrapper.'),
          '#default_value' => $this->configuration['breakpoints'][' . $breakpoint_name . ']['wrapper']['classes'] ?? '',
        ];

        $form['breakpoints'][$breakpoint_name]['main'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Main region'),
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['main']['additional_classes'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Additional classes'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['main']['classes'] ?? '',
        ];

      }
    }

    $form['overrides'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Overrides'),
      '#description' => $this->t('Allow editors to override configured options.'),
    ];
    $form['overrides']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
    ];

    $overrides_wrapper_id = 'overrides-wrapper';

    $component_options = [
      'container' => $this->t('Container'),
      'wrapper' => $this->t('Wrapper'),
      'regions' => $this->t('Region'),
    ];

    $form['overrides']['override_components'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Layout parts to allow overrides'),
      '#options' => $component_options,
      '#default_value' => $this->configuration['overrides']['override_components'] ?? '',
      '#states' => [
        'visible' => [
          'input[name="plugin_configuration[overrides][enabled]"]' => ['checked' => TRUE],
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'changeOverrides'],
        'wrapper' => $overrides_wrapper_id,
      ],
    ];

    $overrides = FALSE;

    if (isset($this->configuration['overrides']['override_components'])) {
      $overrides = $this->configuration['overrides']['override_components'];
      $form_state->set('overrides', $overrides);
    }

    $form['overrides']['components'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $overrides_wrapper_id,
      ],
    ];

    $overrides = $form_state->get('overrides');
    if ($form_state->getValue(['overrides', 'components'])) {
      $overrides = $form_state->getValue(['overrides', 'components']);
    }

    if (!empty($overrides)) {

      $override_property_options = $this->cssPropertyMap();
      foreach ($overrides as $override_key => $override_value) {
        if (empty($override_value)) {
          continue;
        }
        $form['overrides']['components'][$override_key] = [
          '#type' => 'details',
          '#title' => $component_options[$override_key],
        ];
        foreach ($override_property_options as $property_key => $property_option) {
          $num_lines_key = ['components', $override_key, $property_key, 'num_lines'];
          $num_lines = $form_state->get($num_lines_key);
          if ($num_lines === NULL) {
            $num_lines = 1;
            $form_state->set($num_lines_key, $num_lines);
          }
          $removed_lines_key = ['components', $override_key, $property_key, 'removed_lines'];
          $removed_lines = $form_state->get($num_lines_key);
          if ($removed_lines == NULL || !is_array($removed_lines)) {
            $removed_lines = [];
            $form_state->set($removed_lines_key, $removed_lines);
          }
          $form['overrides']['components'][$override_key][$property_key] = [
            '#type' => 'table',
            '#title' => $property_option['label'],
            '#header' => [$property_option['label'], 'Option label', 'Actions'],
            '#num_lines' => $num_lines,
            '#sort' => TRUE,
          ];

          for ($i = 0; $i < $num_lines; $i++) {
            if (in_array($i, $removed_lines)) {
              continue;
            }
            $this->tailwindClasses->getClassOptions($property_option['css_property']);
            $form['overrides']['components'][$override_key][$property_key][$i]['value'] = [
              '#type' => 'select',
              '#title' => $this->t('Value'),
              '#empty_option' => $this->t('- Select -'),
              '#options' => $this->tailwindClasses->getClassOptions($property_option['css_property']),
              '#default_value' => '',
            ];
            $form['overrides']['components'][$override_key][$property_key][$i]['label'] = [
              '#type' => 'textfield',
              '#title' => $this->t('Label'),
              '#default_value' => '',
            ];
            $form['overrides']['components'][$override_key][$property_key][$i]['operation'] = [
              '#type' => 'submit',
              '#value' => $this->t('Remove'),
              '#name' => implode('-', $removed_lines_key),

            ];
          }

        }
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

}
