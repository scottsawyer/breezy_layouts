<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface form BreezyLayoutsElementPluginManager.
 */
interface BreezyLayoutsElementPluginManagerInterface {

  /**
   * Build a BreezyLayouts element.
   *
   * @param array $element
   *   An associative array containing an element with a #type property.
   */
  public function initializeElement(array &$element);

  /**
   * Build a BreezyLaouts element.
   *
   * @param array $element
   *   An associative array containing an element with a #type property.
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see hook_webform_element_alter()
   * @see hook_webform_element_ELEMENT_TYPE_alter()
   * @see \Drupal\webform\WebformSubmissionForm::prepareElements
   */
  public function buildElement(array &$element, array $form, FormStateInterface $form_state);

  /**
   * Get valid definitions.
   *
   * @var bool $container
   *   Whether the element is a container.
   *
   * @return array
   *   An array of valid plugin definitions.
   */
  public function getValidDefinitions(bool $container) : array ;

  /**
   * Is an element's plugin id.
   *
   * @param array $element
   *   A element.
   *
   * @return string
   *   An element's $type has a corresponding plugin id, else
   *   fallback 'element' plugin id.
   */
  public function getElementPluginId(array $element);

  /**
   * The fallback plugin id.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param array $configuration
   *   The plugin configuration.
   *
   * @return string
   *   The fallback plugin id.
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []);

  /**
   * Get a Breezy Layouts element plugin instance for an element.
   *
   * @param array $element
   *   An associative array containing an element with a #type property.
   * @param \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $entity
   *   A Breezy Layouts Variant entity.
   *
   * @return \Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface
   *   A Breezy Layouts element plugin instance
   *
   * @throws \Exception
   *   Throw exception if entity type is not a Breezy Layouts Variant.
   */
  public function getElementInstance(array $element, EntityInterface $entity = NULL);

}
