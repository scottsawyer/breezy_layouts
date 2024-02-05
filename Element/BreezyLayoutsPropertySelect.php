<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a property select element.
 *
 * @FormElement("breezy_layouts_property_select")
 */
class BreezyLayoutsPropertySelect extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processBreezyLayoutsPropertySelect'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (isset($element['#default_value'])) {
        return $element['#default_value'];
      }
      else {
        return [];
      }
    }
    elseif (!empty($input['options'])) {
      return $input['options'];
    }
    else {
      return [];
    }
  }

  /**
   * Process a breezy_layouts property select element.
   */
  public static function processBreezyLayoutsPropertySelect(&$element, FormStateInterface $form_state, &$complete_form) {
    if (isset($element['#property'])) {
      $property = $element['#property'];
      $tailwind_class_service = \Drupal::service('breezy_layouts.tailwind_classes');
      $options = $tailwind_class_service->getClassOptions($property);

      $has_options = (count($options)) ? TRUE : FALSE;

      $element['property'] = [
        '#type' => 'select',
        '#title' => $element['#title'],
        '#options' => $options,
        '#default_value' => (isset($element['#default_value']) && !is_string($element['#default_value'])) ? $element['#default_value'] : [],
        '#access' => $has_options,
      ];
    }

  }


}
