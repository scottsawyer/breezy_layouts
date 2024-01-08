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
    $breakpoint_group = $config->get('breakpoint_group');

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
    ];

    return parent::buildForm($form, $form_state);
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
    $config->set('enabled', TRUE);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
