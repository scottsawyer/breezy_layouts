<?php

namespace Drupal\breezy_layouts\Utility;

use Drupal\Component\Render\MarkupInterface;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementBase;
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
   * Determine if element or sub-element has properties.
   *
   * @param array $element
   *   An element.
   * @param array $properties
   *   Element properties.
   *
   * @return bool
   *   TRUE if element or sub-element has any property.
   */
  public static function hasProperties(array $element, array $properties) {
    foreach ($element as $key => $value) {
      // Recurse through sub-elements.
      if (static::isElement($value, $key)) {
        if (static::hasProperties($value, $properties)) {
          return TRUE;
        }
      }
      // Return TRUE if property exists and property value is NULL or equal.
      elseif (array_key_exists($key, $properties) && ($properties[$key] === NULL || $properties[$key] === $value)) {
        return TRUE;
      }
    }
    return FALSE;
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

  /**
   * Flatten a nested array of elements.
   *
   * @param array $elements
   *   An array of elements.
   *
   * @return array
   *   A flattened array of elements.
   */
  public static function getFlattened(array $elements) {
    $flattened_elements = [];
    foreach ($elements as $key => &$element) {
      if (!self::isElement($element, $key)) {
        continue;
      }

      $flattened_elements[$key] = self::getProperties($element);
      $flattened_elements += self::getFlattened($element);
    }
    return $flattened_elements;
  }

  /**
   * Set form state required error for a specified element.
   *
   * @param array $element
   *   An element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $title
   *   OPTIONAL. Required error title.
   */
  public static function setRequiredError(array $element, FormStateInterface $form_state, $title = NULL) {
    if (isset($element['#required_error'])) {
      $form_state->setError($element, $element['#required_error']);
    }
    elseif ($title) {
      $form_state->setError($element, t('@name field is required.', ['@name' => $title]));
    }
    elseif (isset($element['#title'])) {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element['#title']]));
    }
    else {
      $form_state->setError($element);
    }
  }

  /**
   * Get an element's #states.
   *
   * @param array $element
   *   An element.
   *
   * @return array
   *   An associative array containing an element's states.
   */
  public static function &getStates(array &$element) {
    // Processed elements store the original #states in '#_breezy_layouts_states'.
    // @see \Drupal\webform\WebformSubmissionConditionsValidator::buildForm
    //
    // Composite and multiple elements use a custom states wrapper
    // which will change '#states' to '#_breezy_layouts_states'.
    // @see \Drupal\webform\Utility\WebformElementHelper::fixStatesWrapper
    if (!empty($element['#_breezy_layouts_states'])) {
      return $element['#_breezy_layouts_states'];
    }
    elseif (!empty($element['#states'])) {
      return $element['#states'];
    }
    else {
      // Return empty states variable to prevent the below notice.
      // 'Only variable references should be returned by reference'.
      $empty_states = [];
      return $empty_states;
    }
  }

  /**
   * Get required #states from an element's visible #states.
   *
   * This method allows composite and multiple to conditionally
   * require sub-elements when they are visible.
   *
   * @param array $element
   *   An element.
   *
   * @return array
   *   An associative array containing 'visible' and 'invisible' selectors
   *   and triggers.
   */
  public static function getRequiredFromVisibleStates(array $element) {
    $states = BreezyLayoutsElementHelper::getStates($element);
    $required_states = [];
    if (!empty($states['visible'])) {
      $required_states['required'] = $states['visible'];
    }
    if (!empty($states['invisible'])) {
      $required_states['optional'] = $states['invisible'];
    }
    return $required_states;
  }

  /**
   * Convert element or property to a string.
   *
   * This method is used to prevent 'Array to string conversion' errors.
   *
   * @param array|string|MarkupInterface $element
   *   An element, render array, string, or markup.
   *
   * @return string
   *   The element or property to a string.
   */
  public static function convertToString($element) {
    if (is_array($element)) {
      return (string) \Drupal::service('renderer')->renderPlain($element);
    }
    else {
      return (string) $element;
    }
  }


}
