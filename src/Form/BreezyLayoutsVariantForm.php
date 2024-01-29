<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\breezy_layouts\Form\BreezyLayoutsEntityAjaxFormTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for editing Breezy Layouts Variant entities.
 */
class BreezyLayoutsVariantForm extends EntityForm implements ContainerInjectionInterface {

  use BreezyLayoutsEntityAjaxFormTrait;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface
   * definition
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface
   */
  protected $variantPluginManager;

  /**
   * Constructs a new BreezyLayoutsVariantForm form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *    The config factory.
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager
   *   The variant plugin manager.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *    The breakpoint manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LayoutPluginManagerInterface $layout_plugin_manager, BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager, BreakpointManagerInterface $breakpoint_manager) {
    $this->configFactory = $config_factory;
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->variantPluginManager = $variant_plugin_manager;
    $this->breakpointManager = $breakpoint_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager */
    $layout_plugin_manager = $container->get('plugin.manager.core.layout');
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager */
    $variant_plugin_manager = $container->get('plugin.manager.breezy_layouts.variant');
    return new static($config_factory, $layout_plugin_manager, $variant_plugin_manager, $breakpoint_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $input = $form_state->getUserInput();
    $plugin_form_wrapper = 'plugin-form-wrapper';

    /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant */
    $variant = $this->entity;
    $form_state->set('variant', $variant);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $variant->label(),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $variant->id(),
      '#machine_name' => [
        'exists' => '\Drupal\breezy_layouts\Entity\BreezyLayoutsVariant::load',
      ],
      '#disabled' => !$variant->isNew(),
    ];

    $plugin_id = $variant->getPluginId();
    $plugin_configuration = $input['plugin_configuration'] ?? $variant->getPluginConfiguration();

    if (!$plugin_id) {
      $form['plugin_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Choose a layout'),
        '#required' => TRUE,
        '#empty_option' => $this->t('- Select -'),
        '#options' => $this->getVariantPluginOptions(),
        '#default_value' => $form_state->getValue('plugin_id') ?? '',
        '#ajax' => [
          'callback' => '::pluginIdCallback',
          'wrapper' => $plugin_form_wrapper,
          'event' => 'change',
        ],
      ];
    }
    else {
      $form_state->set('plugin_id', $plugin_id);
      $form_state->setValue('plugin_id', $plugin_id);
      $form['plugin_id_display'] = [
        '#type' => 'item',
        '#title' => $this->t('@layout', ['@layout' => $plugin_id]),
      ];
      $form['plugin_id'] = [
        '#type' => 'hidden',
        '#value' => $plugin_id,
      ];

    }

    if ($form_state->getValue('plugin_id')) {
      $plugin_id = $form_state->getValue('plugin_id');
      $form_state->set('plugin_id', $plugin_id);
    }

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $variant->get('status') ?? '',
    ];

    if (!empty($plugin_id)) {
      if (!$plugin_configuration) {
        $plugin_configuration = [];
      }
      if (!isset($plugin_configuration['_entity'])) {
        $plugin_configuration['_entity'] = $variant->id();
      }
      /** @var \Drupal\breezy_layouts\Plugin\breezy_layouts\Variant\BreezyLayoutsVariantPluginInterface $plugin */
      $plugin = $this->variantPluginManager->createInstance($plugin_id, $plugin_configuration);
      $form['layout'] = [
        '#type' => 'hidden',
        '#value' => $plugin->getLayoutId(),
      ];
      $plugin_form = [
        '#type' => 'container',
        '#attributes' => [
          'id' => $plugin_form_wrapper,
        ],
        '#tree' => TRUE,
      ];
      $form['plugin_configuration'] = $plugin->buildConfigurationForm($plugin_form, $form_state);
    }

    //$form = parent::buildForm($form, $form_state);

    return $this->buildAjaxForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $form = parent::actionsElement($form, $form_state);
    $form['submit']['#value'] = $this->t('Save elements');
    unset($form['delete']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant */
    $variant = $this->entity;
    $status = $variant->save();

    // @todo Include a friendly message.
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    if ($this->entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $values = array_diff_key($values, $this->entity->getPluginCollections());
    }

    $entity
      ->set('label', $values['label'])
      ->set('status', $values['status'])
      ->set('plugin_id', $values['plugin_id']);

    if ($plugin_id = $values['plugin_id']) {
      $layout = $this->variantPluginManager->getLayout($plugin_id);
      if ($layout) {
        $entity->set('layout', $layout);
      }
    }

    if (isset($values['plugin_configuration']) && !empty($values['plugin_configuration']) && !empty($values['plugin_id'])) {
      // @todo Merge $form_state with $plugin_configuration.
      //$plugin_configuration = $this->getPluginConfiguration($values['plugin_id'], $form['plugin_configuration'], $form_state);
      $plugin_configuration = $entity->getPluginConfiguration();
      $entity->set('plugin_configuration', $plugin_configuration);
    }
  }

  /**
   * Get plugin configuration.
   *
   * @param string $pluginId
   *   The plugin id.
   * @param array $formField
   *   The form field.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return array
   *   The array of configuration.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getPluginConfiguration(string $pluginId, array $formField, FormStateInterface $formState) : array {
    /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant */
    $variant = $this->entity;
    $configuration = ['_entity' => $variant->id()];
    /** @var \Drupal\breezy_layouts\Plugin\breezy_layouts\Variant\BreezyLayoutsVariantPluginInterface $plugin */
    $plugin = $this->variantPluginManager->createInstance($pluginId, $configuration);

    $plugin->submitConfigurationForm($formField, $formState);

    return $plugin->getConfiguration();
  }

  /**
   * Get variant plugin options.
   *
   * @return array
   *   An array of variant plugin options.
   */
  protected function getVariantPluginOptions() {
    $variant_plugin_options = [];
    $variant_plugins = $this->variantPluginManager->getValidDefinitions();
    foreach ($variant_plugins as $id => $definition) {
      $variant_plugin_options[$id] = $definition['label'];
    }
    return $variant_plugin_options;
  }

  /**
   * Plugin id callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The portion of the form to return.
   */
  public function pluginIdCallback(array &$form, FormStateInterface $form_state) {
    return $form['plugin_configuration'];
  }

}
