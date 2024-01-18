<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base plugin for BreezyLayoutsElement plugin.
 *
 * @package Drupal\breezy_layouts\Plugin\breezy_layouts\Element.
 */
class BreezyLayoutsElementBase extends PluginBase implements BreezyLayoutsElementInterface {

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassesServiceInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClasses;

  /**
   * Constructs a new Hidden element plugin object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes
   *   The tailwind classes service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreezyLayoutsTailwindClassServiceInterface $tailwind_classes) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
    $this->tailwindClasses = $tailwind_classes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $tailwind_classes = $container->get('breezy_layouts.tailwind_classes');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $tailwind_classes
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'property' => '',
    ];
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
   * Default properties.
   *
   * @return array
   *   An array of element render properties.
   */
  protected function getDefaultProperties() {
    $properties = [
      'title' => '',
      'title_display' => '',
      'description' => '',
      'description_display' => '',
      'required' => FALSE,
      'attributes' => [],
    ];
    return $properties;
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
  public function form(array $form, FormStateInterface $form_state) {


    return $form;
  }

}
