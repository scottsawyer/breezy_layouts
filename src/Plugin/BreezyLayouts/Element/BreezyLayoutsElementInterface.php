<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for BreezyLayoutsElement plugins.
 */
interface BreezyLayoutsElementInterface extends ConfigurableInterface, ContainerFactoryPluginInterface, PluginFormInterface {

  /**
   * Get default properties.
   *
   * @return array
   *   An associative array containing default element properties.
   */
  public function getDefaultProperties();

  /**
   * Retrieves the plugin's label.
   *
   * @return string
   *   The plugin's human-readable and translated label.
   */
  public function label();

  /**
   * Retrieves the plugin's description.
   *
   * @return string|null
   *   The plugin's translated description; or NULL if it has none.
   */
  public function getDescription();

  /**
   * Checks if the element carries a value.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the element carries a value.
   */
  public function isInput(array $element);

  /**
   * Provides a form to configure the element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function form(array $form, FormStateInterface $form_state);

  /**
   * Set variant entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to set.
   *
   * @return mixed
   *
   * @thows \Exception
   */
  public function setEntities(EntityInterface $entity);

  /**
   * Reset variant entity.
   */
  public function resetEntities();

  /**
   * Get an element's default property value.
   *
   * @param string $property_name
   *   An element's property name.
   *
   * @return mixed
   *   An element's default property value or NULL if default property does not
   *   exist.
   */
  public function getDefaultProperty($property_name);

  /**
   * Get an element's property value.
   *
   * @param array $element
   *   An element.
   * @param string $property_name
   *   An element's property name.
   *
   * @return mixed
   *   An element's property value, default value, or NULL if
   *   property does not exist.
   */
  public function getElementProperty(array $element, $property_name);

  /**
   * Determine if the element supports a specified property.
   *
   * @param string $property_name
   *   An element's property name.
   *
   * @return bool
   *   TRUE if the element supports a specified property.
   */
  public function hasProperty($property_name);


  /**
   * Checks if the element is hidden.
   *
   * @return bool
   *   TRUE if the element is hidden.
   */
  public function isHidden();

  /**
   * Initialize an element to be displayed, rendered, or exported.
   *
   * @param array $element
   *   An element.
   */
  public function initialize(array &$element);

  /**
   * Prepare an element to be rendered within a BreezyLayoutsVariant form.
   *
   * @param array $element
   *   An element.
   *
   * @see \Drupal\webform\Element\WebformCompositeBase::processWebformComposite
   */
  public function prepare(array &$element);

  /**
   * Finalize an element to be rendered within a BreezyLayoutsVariant form.
   *
   * @param array $element
   *   An element.
   *
   * @see \Drupal\webform\Element\WebformCompositeBase::processWebformComposite
   */
  public function finalize(array &$element);

  /**
   * Set an element's default value using saved data.
   *
   * The method allows the Variant's 'saved' #default_value to be different
   * from the element's #default_value.
   *
   * @param array $element
   *   An element.
   *
   * @see \Drupal\webform\Plugin\WebformElement\DateBase::setDefaultValue
   * @see \Drupal\webform\Plugin\WebformElement\EntityAutocomplete::setDefaultValue
   */
  public function setDefaultValue(array &$element);

  /**
   * Checks if the element value has multiple values.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if the element value has multiple values.
   */
  public function hasMultipleValues(array $element);

}
