<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\Core\Form\FormStateInterface;
use Drupal\breakpoint\BreakpointManagerInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreakpointManagerInterface $breakpoint_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
    $this->breakpointManager = $breakpoint_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $breakpoint_manager
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
    $breakpoints_wrapper_id = 'breakpoints-wrapper';

    $form['container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Container'),
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
          '#type' => 'fieldset',
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

        /**
        $form['breakpoints'][$breakpoint_name]['wrapper']['classes'] = [

        ];
        /**/

        $form['breakpoints'][$breakpoint_name]['gap'] = [
          '#type' => 'select',
          '#title' => $this->t('Gap'),
          '#description' => $this->t('Space between columns.'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['gap'] ?? 'gap-0',
          '#options' => [
            'gap-0' => '0px',
            'gap-px' => '1px',
            'gap-0.5' => '2px',
            'gap-1' => '4px',
            'gap-2' => '8px',
            'gap-2.5' => '10px',
            'gap-3' => '12px',
            'gap-3.5' => '14px',
            'gap-4' => '16px',
            'gap-5' => '20px',
            'gap-6' => '24px',
            'gap-7' => '28px',
            'gap-8' => '32px',
            'gap-9' => '36px',
            'gap-10' => '40px',
            'gap-11' => '44px',
            'gap-12' => '48px',
          ],
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['columns'] = [
          '#type' => 'select',
          '#title' => $this->t('Columns'),
          '#description' => $this->t('Total number of columns available to a region.'),
          '#default_value' => $this->configuration['breakpoints'][$breakpoint_name]['columns'] ?? '',
          '#empty_option'=>$this->t('- Select -'),
          '#options' => [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            12 => '12',
          ],
          '#states' => [
            'visible' => [
              'input[name="plugin_configuration[breakpoints][' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#ajax' => [
            'callback' => [$this, 'columnsCallback'],
            'event' => 'change',
            'wrapper' => $breakpoints_wrapper_id,
          ]
        ];

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

}
