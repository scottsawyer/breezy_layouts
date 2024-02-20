<?php

namespace Drupal\breezy_layouts\Plugin\Layout;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\breezy_layouts\Service\VariantManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Layout class for Breezy Layouts.
 */
class BreezyLayouts extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Config Breakpoints.
   *
   * @var array
   */
  protected $breakpoints;

  /**
   * Layouts configuration.
   *
   * @var array
   */
  protected $layoutsConfig;

  /**
   * Drupal\breakpoint\BreakpointManagerInterface definition.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Drupal\breezy_layouts\Service\VariantManagerInterface definition.
   *
   * @var \Drupal\breezy_layouts\Service\VariantManagerInterface
   */
  protected $variantManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager service.
   * @param \Drupal\breezy_layouts\Service\VariantManagerInterface $variant_manager
   *   The variant manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, BreakpointManagerInterface $breakpoint_manager, VariantManagerInterface $variant_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->breakpointManager = $breakpoint_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->layoutsConfig = $this->configFactory->get('breezy_layouts.settings');
    //$this->breakpoints = $this->getBreakpoints();
    $this->variantManager = $variant_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager */
    $breakpoint_manager = $container->get('breakpoint.manager');
    /** @var \Drupal\breezy_layouts\Service\VariantManagerInterface $variant_manager */
    $variant_manager = $container->get('breezy_layouts.variant.manager');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $breakpoint_manager,
      $variant_manager,
      $entity_type_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'variant' => NULL,
        'variant_settings' => [],
        'classes' => [],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $layout_definition = $this->getPluginDefinition();
    $configuration = $this->getConfiguration();
    $variant_options = $this->variantManager->getVariantOptionsForLayout($layout_definition->get('id'));
    if (empty($variant_options)) {
      return $form;
    }

    $input = $form_state->getUserInput();

    $variant_wrapper_id = 'variant-settings-wrapper';

    $variant = NULL;
    if (isset($input['variant'])) {
      $variant = $input['variant'];
    }
    else {
      $variant = $configuration['variant'];
    }

    $form['#parents'] = [];

    $form['variant'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose variant'),
      '#options' => $variant_options,
      '#default_value' => $variant ?? '',
      '#empty_option' => $this->t('-- Select --'),
      '#ajax' => [
        'callback' => [$this, 'variantSelectCallback'],
        'wrapper' => $variant_wrapper_id,
      ],
    ];

    $form['variant_settings_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => $variant_wrapper_id,
      ],
    ];
    $variant_entity = NULL;
    if ($variant) {
      /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant_entity */
      $variant_entity = $this->entityTypeManager->getStorage('breezy_layouts_variant')->load($variant);
    }
    if ($variant_entity) {
      if (isset($user_input['variant_settings'])) {
        $variant_settings = $user_input['variant_settings'];
      }
      else {
        $variant_settings = $this->configuration['variant_settings'];
      }

      $form_state->set('default_settings', $variant_settings);
      $form['variant_settings_wrapper']['variant_settings'] = [
        '#parents' => ['variant_settings'],
      ];
      $form['variant_settings_wrapper']['variant_settings'] = $variant_entity->buildLayoutForm($form['variant_settings_wrapper']['variant_settings'], $form_state);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    /*
    $breakpoints = $this->breakpoints;
    $regions = $form_state->getValue('regions');
    foreach ($regions as $region_name) {
      foreach ($breakpoints as $bp => $breakpoint) {
        $column_count = $regions[$region_name][$bp]['size'] + $regions[$region_name][$bp]['offset'];
        if ($column_count > 12) {
          $form_state->setErrorByName("regions][$region_name][$bp]['size']", $this->t("The total columns, including offsets, can not total more than 12."));
        }
      }
    }
    /**/
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $logger = \Drupal::logger('BreezyLayouts::submitConfigurationForm');
    $variant = $form_state->getValue('variant');
    $this->configuration['variant'] = $variant;
    $variant_entity = NULL;
    if ($variant) {
      /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant_entity */
      $variant_entity = $this->entityTypeManager->getStorage('breezy_layouts_variant')->load($variant);
    }
    if ($variant_entity) {
      $variant_settings = $form_state->getValue('variant_settings');
      $this->configuration['variant_settings'] = $variant_settings;
      $classes = $variant_entity->buildLayoutClasses($variant_settings);
      $logger->notice('$classes: <pre>' . print_r($classes, TRUE) . '</pre>');
      $this->configuration['classes'] = $classes;
    }
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Variant select callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The portion of the form to return.
   */
  public function variantSelectCallback(array $form, FormStateInterface $form_state) {
    return $form['layout_settings']['variant_settings_wrapper'];
  }

}
