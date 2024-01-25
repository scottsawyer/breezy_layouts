<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
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
   * @param string $parent_key
   *   The parent key.
   *
   * @return array
   *   An array of properties for a given key.
   */
  public function getProperties(string $parent_key) {
    $parent_array = BreezyLayoutsElementHelper::formKeyToArray($parent_key);
    $configuration = $this->getConfiguration();
    $properties = NestedArray::getValue($configuration, $parent_array);
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
   * @param array $property
   *   The configured property.
   * @param int $delta
   *   The row weight.
   *
   * @return array
   *   The property in a row format.
   */
  public function getPropertyRow(array $property, int $delta) {
    $row = [];

    $key = key($property);
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

    $row['operations'] = [
      '#markup' => $this->t('Operation'),
    ];

    return $row;
  }

  /**
   * Build properties table.
   *
   * @param string $parent_key
   *
   */



}
