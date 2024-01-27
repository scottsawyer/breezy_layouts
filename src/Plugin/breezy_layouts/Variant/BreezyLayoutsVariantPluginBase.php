<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;

/**
 * Provides a base variant plugin class.
 *
 * @package Drupal\breezy_layouts\Plugin\breezy_layouts\Variant
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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
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
   * Layout form.
   *
   * @param array $form
   *   The layout form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The layout form.
   */
  public function layoutForm(array $form, FormStateInterface $form_state) {
    return $form;
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
    //$parent_array = BreezyLayoutsElementHelper::formKeyToArray($parent_key);
    $configuration = $this->getConfiguration();
    //$properties = NestedArray::getValue($configuration, $parent_array);
    $properties = NestedArray::getValue($configuration, $parent_key);
    if ($properties) {
      return $properties;
    }
    return [];
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
   *
   * @return array
   *   The property in a row format.
   */
  public function getPropertyRow(string $key, array $property, int $delta) {

    $row = [];

    $title = $property['element']['title'] ?? 'missing';
    $type = $property['element']['type'] ?? 'missing';
    $property_name = $property['property'] ?? 'missing';

    $row_class = ['draggable'];

    $row['#attributes']['data-breezy-layouts-key'] = '';
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
    ];

    $row['type'] = [
        '#markup' => $type,
    ];

    $row['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for @title', ['@title' => $title]),
      '#title_display' => 'invisible',
      '#default_value' => $property['weight'] ?? 0,
      '#wrapper_attributes' => ['class' => ['breezy-layouts-tabledrag-hide']],
      '#attributes' => [
        'class' => ['row-weight'],
      ],
      '#delta' => $delta,
    ];

    /*
    $element_edit_url = Url::fromRoute('entity.breezy_layouts_ui.element.edit', [
      'breezy_layouts_variant' =>
    ]);
    /**/
    $row['operations'] = [
      '#markup' => $this->t('Operation'),
    ];

    return $row;
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
      foreach ($properties as $key => $property) {
        $rows[] = $this->getPropertyRow($key, $property, $delta);
      }
    }
    $table = [
      '#type' => 'table',
      '#sort' => TRUE,
      '#header' => $this->getPropertiesTableHeader(),
      '#empty' => $this->t('Add CSS properties.'),
      '#attributes' => [
        'class' => ['breezy-layouts-properties-form'],
      ],'#tabledrag' => [
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
    $header['operations'] = $this->t('Operations');
    return $header;
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

}
