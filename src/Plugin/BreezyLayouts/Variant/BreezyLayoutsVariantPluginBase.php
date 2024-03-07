<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;

/**
 * Provides a base variant plugin class.
 *
 * @package Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant
 */
abstract class BreezyLayoutsVariantPluginBase extends PluginBase implements ContainerFactoryPluginInterface, BreezyLayoutsVariantPluginInterface {

  use DependencySerializationTrait;

  /**
   * Plugin configuration form state key.
   *
   * @const string
   */
  const CONFIGURATION_FORM_STATE_KEY = 'breezy_layouts.breezy_layouts_variant';

  /**
   * The parent config entity.
   *
   * @var string
   */
  protected $parentEntity;

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
   * Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
   */
  protected $elementPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, BreezyLayoutsElementPluginManagerInterface $element_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->elementPluginManager = $element_plugin_manager;
    $this->configFactory = $config_factory;
    $this->breezyLayoutsSettings = $config_factory->get('breezy_layouts.settings');
    $this->configuration += $this->defaultConfiguration();
    if (array_key_exists('_entity', $configuration)) {
      $this->parentEntity = $configuration['_entity'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['has_layout_form' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutId() {
    return $this->pluginDefinition['layout'];
  }

  /**
   * {@inheritdoc}
   */
  public function hasContainer() {
    return $this->pluginDefinition['container'];
  }

  /**
   * {@inheritdoc}
   */
  public function hasWrapper() {
    return $this->pluginDefinition['wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    return $this->parentEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function hasLayoutForm() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function layoutForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Builds a form element.
   *
   * @param array $element_definition
   *   The element definition array.
   * @param string $prefix
   *   A prefix (for the breakpoint).
   * @param mixed $default_value
   *   The default value for the field.
   *
   * @return array
   *   A renderable element.
   *
   * @see \Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper
   */
  protected function buildFormElement(array $element_definition, string $prefix = '', $default_value = NULL) {
    return BreezyLayoutsElementHelper::buildFormElement($element_definition, $prefix, $default_value);
  }

  /**
   * If the element has a UI.
   *
   * Used to control layout form container visibility.
   *
   * @param array $property
   *   The property array.
   *
   * @return bool
   *   TRUE if the element has a UI.
   */
  protected function elementHasUi(array $property) {
    $element_plugin = $this->elementPluginManager->getElementInstance($property);
    if ($element_plugin) {
      return $element_plugin->hasUi();
    }
    return FALSE;
  }

  /**
   * Get prefix for breakpoint.
   *
   * @param string $breakpoint_name
   *   The breakpoint name.
   *
   * @return string
   *   The prefix set for the breakpoint name.
   */
  protected function getPrefixForBreakpoint(string $breakpoint_name) {
    $breakpoints = $this->breezyLayoutsSettings->get('breakpoints');
    if (isset($breakpoints[$breakpoint_name])) {
      return $breakpoints[$breakpoint_name]['prefix'];
    }
    return '';
  }

  /**
   * Get elements from configuration.
   *
   * @param string $parent_key
   *   The parent key.
   *
   * @return array
   *   An array of elements for a given key.
   */
  protected function getElements(string $parent_key) {
    $elements = [];

    return $elements;
  }

  /**
   * Get properties from configuration.
   *
   * @param array $parent_key
   *   The parent key.
   *
   * @return array
   *   An array of properties for a given key.
   */
  public function getProperties(array $parent_key) {
    $configuration = $this->getConfiguration();
    $properties = NestedArray::getValue($configuration, $parent_key);
    if ($properties) {
      return $properties;
    }
    return [];
  }

  /**
   * Build properties table.
   *
   * @param array $parent_key
   *   An array of parents.
   * @param array $properties
   *   An array of properties.
   *
   * @return array
   *   An array representing a variant properties table.
   */
  public function buildPropertiesTable(array $parent_key, array $properties = []) {

    $rows = [];
    if (!empty($properties)) {
      $delta = count($properties);
      $properties = $this->getOrderableElements($properties, $parent_key);
      foreach ($properties as $key => $property) {
        $rows[$key] = $this->getPropertyRow($key, $property, $delta, $parent_key);
      }
    }
    $table = [
      '#type' => 'table',
      '#sort' => TRUE,
      '#header' => $this->getPropertiesTableHeader(),
      '#empty' => $this->t('Add CSS properties.'),
      '#attributes' => [
        'class' => ['breezy-layouts-properties-form', implode('-', $parent_key)],
      ],
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'row-parent-key',
          'source' => 'row-key',
          'hidden' => TRUE,
          'limit' => FALSE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'row-weight',
        ],
      ],
    ] + $rows;

    return $table;
  }

  /**
   * Build variant properties table header.
   *
   * @return array
   *   An array of table header items.
   */
  protected function getPropertiesTableHeader() {
    $header = [];
    $header['title'] = $this->t('Title');
    $header['key'] = $this->t('Key');
    $header['property'] = $this->t('CSS Property');
    $header['type'] = $this->t('Field type');
    $header['weight'] = $this->t('Weight');
    $header['parent'] = [
      'data' => $this->t('Parent'),
      'class' => ['tabledrag-hide'],
    ];
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * Get property row.
   *
   * Builds a row for the Variant plugin properties table.
   *
   * @param string $key
   *   The property key.
   * @param array $property
   *   The configured property.
   * @param int $delta
   *   The row weight.
   * @param array $parent_key
   *   The parent key array.
   *
   * @return array
   *   The property in a row format.
   */
  public function getPropertyRow(string $key, array $property, int $delta, array $parent_key) {

    $row = [];

    $title = $property['element']['title'] ?? 'missing';
    $type = $property['element']['type'] ?? 'missing';
    $property_name = $property['property'] ?? 'missing';

    $row_class = ['draggable'];

    $row['#attributes']['data-breezy-layouts-key'] = $key;
    $row['#attributes']['data-breezy-layouts-type'] = $type;

    $row['#attributes']['class'] = $row_class;

    $row['title'] = [
      '#markup' => $title,
    ];

    $row['key'] = [
      '#markup' => $key,
    ];

    $row['property'] = [
      '#markup' => $property_name,
      '#allowed_tags' => ['pre'],
    ];

    $row['type'] = [
      '#markup' => $type,
    ];
    $weight_parents = array_merge($parent_key, [$key, 'weight']);
    $row['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for @title', ['@title' => $title]),
      '#description' => $this->t('@weight', ['@weight' => print_r($weight_parents, TRUE)]),
      '#title_display' => 'invisible',
      '#default_value' => $property['weight'] ?? 0,
      '#wrapper_attributes' => ['class' => ['breezy-layouts-tabledrag-hide']],
      '#attributes' => [
        'class' => ['row-weight'],
      ],
      '#delta' => $delta,
      //'#parents' => $weight_parents,
    ];

    $row['parent'] = [
      '#wrapper_attributes' => ['class' => ['breezy-layouts-tabledrag-hide', 'tabledrag-hide']],
    ];

    $key_parents = array_merge($parent_key, [$key, 'key']);

    $row['parent']['key'] = [
      '#parents' => $key_parents,
      '#type' => 'hidden',
      '#value' => $key,
      '#attributes' => [
        'class' => ['row-key'],
      ],
    ];

    $key_parents_parent = array_merge($parent_key, [$key, 'parent_key']);

    $row['parent']['parent_key'] = [
      '#parents' => $key_parents_parent,
      '#type' => 'hidden',
      '#default_value' => json_encode($key_parents_parent),
      '#attributes' => [
        'class' => ['row-parent-key'],
      ],
    ];

    $query = [
      'key' => $key,
      'property' => $property_name,
      'parent' => Json::encode($parent_key),
    ];

    $element_edit_url = Url::fromRoute('entity.breezy_layouts_ui.element.edit_form', [
      'breezy_layouts_variant' => $this->parentEntity,
      'type' => $type,
    ],
      [
        'query' => $query,
      ]);
    $element_delete_url = Url::fromRoute('entity.breezy_layouts_ui.element.delete_form', [
      'breezy_layouts_variant' => $this->parentEntity,
      'type' => $type,
    ],
      [
        'query' => $query,
      ]);
    $row['operations'] = [
      '#type' => 'operations',
      '#prefix' => '<div class="breezy-layouts-dropbutton">',
      '#suffix' => '</div>',
    ];
    $row['operations']['#links']['edit'] = [
      'title' => $this->t("Edit"),
      'url' => $element_edit_url,
      'attributes' => [
        'class' => ['breezy-layouts-ajax-link'],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
        'data-dialog-options' => Json::encode(['width' => 550]),
      ],
    ];
    $row['operations']['#links']['delete'] = [
      'title' => $this->t("Delete"),
      'url' => $element_delete_url,
      'attributes' => [
        'class' => ['breezy-layouts-ajax-link'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 550]),
      ],
    ];

    return $row;
  }

  /**
   * Get Variant elements as an associative array of orderable elements.
   *
   * @param array $properties
   *   The properties array.
   *
   * @return array
   *   An associative array of orderable elements.
   */
  protected function getOrderableElements(array $properties) {
    $weights = [];
    foreach ($properties as $property_key => &$property) {
      if (!isset($weights[$property_key])) {
        $property['weight'] = $weights[$property_key] = 0;
      }
      else {
        $property['weight'] = ++$weights[$property_key];
      }
    }

    return $properties;
  }

  /**
   * Add property link.
   *
   * @param \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $variant
   *   The variant entity.
   * @param array $parent_key
   *   The parent key.
   *
   * @return array
   *   The add property link.
   */
  protected function addPropertyLink(BreezyLayoutsVariantInterface $variant, array $parent_key) {
    $parent = json_encode($parent_key);
    return [
      '#type' => 'link',
      '#title' => $this->t('Add property'),
      '#url' => Url::fromRoute('entity.breezy_layouts_ui.property.add', ['breezy_layouts_variant' => $variant->id()], ['query' => ['parent' => $parent]]),
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => $this->getDialogOptions(),
      ],
    ];
  }

    /**
   * Get dialog options.
   *
   * @param array $options
   *   An array of options.
   *
   * @return string
   *   A JSON encoded string of dialog options.
   */
  public function getDialogOptions(array $options = []) {
    $default_options = [
      'width' => 800,
    ];
    return json_encode(array_merge($options, $default_options));
  }

  /**
   * Plugin callback.
   *
   * Callback when a breakpoint is enabled.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   *
   * @return array
   *   The plugin configuration portion of the form array.
   */
  public function pluginCallback(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $breakpoint_name = $trigger['#breakpoint_name'];
    $input = $form_state->getUserInput();
    $plugin_configuration = $input['plugin_configuration'];
    $enabled = $plugin_configuration['breakpoints'][$breakpoint_name]['enabled'];
    if ($form_state->getFormObject() instanceof \Drupal\Core\Entity\EntityFormInterface) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $form_state->getFormObject()->getEntity();
      $stored_configuration = $entity->getPluginConfiguration();
      NestedArray::setValue($stored_configuration, ['breakpoints', $breakpoint_name, 'enabled'], $enabled);
      $entity->set('plugin_configuration', $stored_configuration);
      $entity->save();
    }
    return $form['plugin_configuration']['breakpoints'][$breakpoint_name];
  }

  /**
   * Variant save.
   *
   * Merges $form_state with $plugin_configuration.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param array $form_values
   *   The form values.
   *
   * @return array
   *   The merged form values / configuration.
   *
   * @see \Drupal\breezy_layouts\Form\BreezyLayoutsVariantForm.
   */
  public function mergeFormState(array $configuration, array $form_values) {
    $breakpoints = $configuration['breakpoints'];
    $config_values = [];
    foreach ($breakpoints as $breakpoint_name => $breakpoint_settings) {
      if (!$breakpoint_settings['enabled']) {
        continue;
      }
      // Get $properties (fields) from $config.
      $config_values[$breakpoint_name] = $breakpoint_settings;
    }

    return NestedArray::mergeDeepArray($form_values['breakpoints'], $config_values);
  }

  /**
   * {@inheritdoc}
   */
  public function buildLayoutClasses(array $layout_settings) {
    $classes = [];
    if (empty($layout_settings) || !isset($layout_settings['breakpoints'])) {
      return $classes;
    }
    foreach ($layout_settings['breakpoints'] as $breakpoint_name => $breakpoint_settings) {

      $prefix = $this->getPrefixForBreakpoint($breakpoint_name);
      foreach ($breakpoint_settings as $element_name => $element_settings) {
        foreach ($element_settings as $key => $value) {
          if (is_array($value)) {
            // Checkboxes can have arrays of classes.
            $classes[$element_name][] = implode(' ' . $prefix, $value);
          }
          else {
            $classes[$element_name][] = $prefix . $value;
          }
        }
      }
    }
    return $classes;
  }

}
