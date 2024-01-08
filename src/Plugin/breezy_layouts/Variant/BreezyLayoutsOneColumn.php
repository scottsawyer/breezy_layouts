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
    $form_wrapper = 'variant-form-wrapper';
    $breakpoints_wrapper_id = 'breakpoints-wrapper';

    $form['#tree'] = TRUE;
    $form['#attributes']['id'] = $form_wrapper;

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
        'callback' => '::changeBreakpointGroup',
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
          '#default_value' => $breakpoints[$breakpoint_name]['enabled'] ?? FALSE,
        ];

        $form['breakpoints'][$breakpoint_name]['prefix'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Breakpoint prefix'),
          '#default_value' => $breakpoints[$breakpoint_name]['prefix'] ?? '',
          '#states' => [
            'visible' => [
              'input[name="breakpoints[' . $breakpoint_name . '][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        ];

        $form['breakpoints'][$breakpoint_name]['wrapper'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Wrapper'),
        ];

      }
    }

        return $form;
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
    $breakpoint_group = $form_state->getValue('breakpoint_group');
    $form_state->set('breakpoint_group', $breakpoint_group);
    $form_state->setRebuild();
    return $form['breakpoints'];
  }

}
