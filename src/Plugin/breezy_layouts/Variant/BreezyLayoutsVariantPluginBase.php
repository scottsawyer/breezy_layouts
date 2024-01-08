<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

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

}
