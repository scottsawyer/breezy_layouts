<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the configuration form.
 */
class BreezyLayoutsSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'breezy_layouts.settings';

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Drupal\Core\Layout\LayoutPluginManagerInterface definition.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface;
   */
  protected $layoutPluginManager;

  /**
   * Constructs a new settings form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *    The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler extension.
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *    The breakpoint manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, LayoutPluginManagerInterface $layout_plugin_manager, BreakpointManagerInterface $breakpoint_manager) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->breakpointManager = $breakpoint_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager */
    $layout_plugin_manager = $container->get('plugin.manager.core.layout');
    return new static($config_factory, $module_handler, $layout_plugin_manager, $breakpoint_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $layouts = $config->get('layouts');
    $breakpoints_wrapper_id = 'breakpoints-wrapper';
    $breakpoint_group = $config->get('breakpoint_group');
    if ($form_state->getValue('breakpoint_group')) {
      $breakpoint_group = $form_state->getValue('breakpoint_group');
    }

    $form['layouts'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Select layouts'),
      '#description' => $this->t('Select the layouts to create variants for.'),
      '#options' => $this->layoutPluginManager->getLayoutOptions(),
      '#default_value' => $layouts,
    ];

    $form['breakpoint_group'] = [
      '#type' => 'select',
      '#title' => $this->t('Breakpoint group'),
      '#description' => $this->t('Select the breakpoints that should be included in each layout configuration.'),
      '#default_value' => $breakpoint_group ?? '',
      '#options' => $this->breakpointManager->getGroups(),
      '#empty_option'=>$this->t('- Select -'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'changeBreakpointGroup'],
        'wrapper' => $breakpoints_wrapper_id,
      ],
    ];

    $form['breakpoints'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#title' => $this->t('Breakpoints'),
      '#prefix' => '<div id="' . $breakpoints_wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    if ($breakpoint_group) {
      $breakpoint_group_breakpoints = $this->breakpointManager->getBreakpointsByGroup($breakpoint_group);

      $form['breakpoints']['#type'] = 'fieldset';

      foreach ($breakpoint_group_breakpoints as $breakpoint_name => $breakpoint) {
        $breakpoints = $form_state->getValue('breakpoints');
        if (!$breakpoints) {
          $breakpoints = $config->get('breakpoints');
        }

        $breakpoint_name = str_replace('.', '__', $breakpoint_name);
        $form['breakpoints'][$breakpoint_name] = [
          '#type' => 'fieldset',
          '#title' => $breakpoint->getLabel(),
        ];

        $form['breakpoints'][$breakpoint_name]['prefix'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Breakpoint prefix'),
          '#default_value' => $breakpoints[$breakpoint_name]['prefix'] ?? '',
        ];

      }
    }

    $form['cdn'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('CDN'),
      '#description' => $this->t('Load CSS library from a CDN'),
      '#tree' => TRUE,
    ];
    $cdn = $config->get('cdn');
    $form['cdn']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#default_value' => $cdn['enabled'] ?? FALSE,
    ];
    $form['cdn']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Library CDN URL'),
      '#default_value' => $cdn['url'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="cdn[enabled]"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="cdn[enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $layouts = $form_state->getValue('layouts');
    $config->set('layouts', $layouts);
    $breakpoint_group = $form_state->getValue('breakpoint_group');
    $config->set('breakpoint_group', $breakpoint_group);
    $breakpoints = $form_state->getValue('breakpoints');
    $config->set('breakpoints', $breakpoints);
    $cdn = $form_state->getValue('cdn');
    $config->set('cdn', [
      'enabled' => $cdn['enabled'] ?? FALSE,
      'url' => $cdn['url'] ?? '',
    ]);
    $config->save();
    parent::submitForm($form, $form_state);
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
    return $form['breakpoints'];
  }
}
