<?php

namespace Drupal\breezy_layouts\Utility;

use Drupal\Core\Render\Element;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;

/**
 * Helper class for Breezy Layouts Element.
 */
class BreezyLayoutsElementHelper {

  /**
   * Ignored element properties.
   *
   * @var array
   */
  public static $ignoredProperties = [
    // Properties that will allow code injection.
    '#allowed_tags' => '#allowed_tags',
    // Properties that will break webform data handling.
    '#tree' => '#tree',
    '#array_parents' => '#array_parents',
    '#parents' => '#parents',
    // Properties that will cause unpredictable rendering.
    '#weight' => '#weight',
    // Callbacks are blocked to prevent unwanted code executions.
    '#access_callback' => '#access_callback',
    '#ajax' => '#ajax',
    '#after_build' => '#after_build',
    '#element_validate' => '#element_validate',
    '#lazy_builder' => '#lazy_builder',
    '#post_render' => '#post_render',
    '#pre_render' => '#pre_render',
    '#process' => '#process',
    '#submit' => '#submit',
    '#validate' => '#validate',
    '#value_callback' => '#value_callback',
    // Element specific callbacks.
    '#file_value_callbacks' => '#file_value_callbacks',
    '#date_date_callbacks' => '#date_date_callbacks',
    '#date_time_callbacks' => '#date_time_callbacks',
    '#captcha_validate' => '#captcha_validate',
  ];

  /**
   * Determine if a breezy layouts element is a specified #type.
   *
   * @param array $element
   *   A breezy layouts element.
   * @param string|array $type
   *   An element type.
   *
   * @return bool
   *   TRUE if a breezy layouts element is a specified #type.
   */
  public static function isType(array $element, $type) {
    if (!isset($element['#type'])) {
      return FALSE;
    }

    if (is_array($type)) {
      return in_array($element['#type'], $type);
    }
    else {
      return ($element['#type'] === $type);
    }
  }

  /**
   * Determine if an element and its key is a renderable array.
   *
   * @param array|mixed $element
   *   An element.
   * @param string $key
   *   The element key.
   *
   * @return bool
   *   TRUE if an element and its key is a renderable array.
   */
  public static function isElement($element, $key) {
    return (Element::child($key) && is_array($element));
  }

  /**
   * Remove all properties from a render element.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   A render element with no properties.
   */
  public static function removeProperties(array $element) {
    foreach ($element as $key => $value) {
      if (static::property($key)) {
        unset($element[$key]);
      }
    }
    return $element;
  }

  /**
   * Checks if the key is string and a property.
   *
   * @param string $key
   *   The key to check.
   *
   * @return bool
   *   TRUE of the key is string and a property., FALSE otherwise.
   */
  public static function property($key) {
    return ($key && is_string($key) && $key[0] == '#');
  }

  /**
   * Gets properties of a structured array element (keys beginning with '#').
   *
   * @param array $element
   *   An element array to return properties for.
   *
   * @return array
   *   An array of property keys for the element.
   */
  public static function properties(array $element) {
    // Prevent "Exception: Notice: Trying to access array offset on value
    // of type int" by removing all numeric keys.
    // This issue is triggered when an element's YAML #option have numeric keys.
    foreach ($element as $key => $value) {
      if (is_int($key)) {
        unset($element[$key]);
      }
    }
    return Element::properties($element);
  }

  /**
   * Get an associative array containing a render element's properties.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   An associative array containing a render element's properties.
   */
  public static function getProperties(array $element) {
    $properties = [];
    foreach ($element as $key => $value) {
      if (static::property($key)) {
        $properties[$key] = $value;
      }
    }
    return $properties;
  }

  /**
   * Set a property on all elements and sub-elements.
   *
   * @param array $element
   *   A render element.
   * @param string $property_key
   *   The property key.
   * @param mixed $property_value
   *   The property value.
   */
  public static function setPropertyRecursive(array &$element, $property_key, $property_value) {
    $element[$property_key] = $property_value;
    foreach (Element::children($element) as $key) {
      self::setPropertyRecursive($element[$key], $property_key, $property_value);
    }
  }

  /**
   * Form key to array.
   *
   * @param string $form_key
   *   The form form key.
   *
   * @param array
   *   The parent key converted to an array.
   */
  public static function formKeyToArray(string $form_key) {
    return preg_split('/[\[]/', str_replace(']', '', $form_key));
  }

  /**
   * Get the element title from the element.
   *
   * @param \Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface $element
   *   The element.
   *
   * @return string
   *   The element title.
   */
  public static function getElementTitle(BreezyLayoutsElementInterface $element) {
    $title = '';
    $configuration = $element->getConfiguration();
    if (isset($configuration['element']['title'])) {
      $title = $configuration['element']['title'];
    }
    return $title;
  }

}
