<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Core\Entity\EntityInterface;
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
   * Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface definition.
   *
   * @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface
   */
  protected $variant = NULL;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $instance->tailwindClasses = $container->get('breezy_layouts.tailwind_classes');

    return $instance;
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

    $element = $form_state->get('element');
    $type = $element['#type'];

    $form['element'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Element settings'),
      '#weight' => -50,
    ];
    $form['element']['type'] = [
      '#type' => 'hidden',
      '#value' => $type,
    ];
    $form['element']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('title') ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array $form, FormStateInterface $form_state) {
    $element_properties = $form_state->getValues();

    return $element_properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntities(EntityInterface $entity) {
    if ($entity instanceof BreezyLayoutsVariantInterface) {
      $this->variant = $entity;
    }
    else {
      throw new \Exception('Entity type must be a webform or webform submission');
    }
    return $this;
  }

  /**
   * Reset variant entity.
   */
  public function resetEntities() {
    $this->variant = NULL;
  }


}
