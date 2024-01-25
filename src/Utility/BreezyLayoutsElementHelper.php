<?php

namespace Drupal\breezy_layouts\Utility;

use Drupal\Core\Render\Element;

/**
 * Helper class for Breezy Layouts Element.
 */
class BreezyLayoutsElementHelper {

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
    // This issue is trigged when an element's YAML #option have numeric keys.
    foreach ($element as $key => $value) {
      if (is_int($key)) {
        unset($element[$key]);
      }
    }
    return Element::properties($element);
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

}
