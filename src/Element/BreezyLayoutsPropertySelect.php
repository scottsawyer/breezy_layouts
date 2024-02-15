<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\NestedArray;
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
      '#property' => NULL,
      '#title_display' => 'before',
      '#process' => [
        [$class, 'processBreezyLayoutsPropertySelect'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderBreezyLayoutsPropertySelect'],
      ],
      '#theme' => 'select',
      '#theme_wrappers' => ['form_element'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {

    if ($input === FALSE) {
      if (isset($element['#default_value'])) {
        $default_value = $element['#default_value'];
        return $default_value;
      }
      else {
        return '';
      }
    }
    elseif (isset($input['value'])) {
      return $input['value'];
    }
    else {
      return '';
    }
  }


  /**
   * Process a breezy_layouts_property_select element.
   */
  public static function processBreezyLayoutsPropertySelect(&$element, FormStateInterface $form_state, &$complete_form) {
    //$logger = \Drupal::logger('processBreezyLayoutsPropertySelect');
    if (isset($element['#property'])) {
      //$logger->warning('$element: <pre>' . print_r($element, TRUE) . '</pre>');
      $property = $element['#property'];
      $tailwind_class_service = \Drupal::service('breezy_layouts.tailwind_classes');
      $options = $tailwind_class_service->getClassOptions($property);

      $has_options = (count($options)) ? TRUE : FALSE;

      $default_value = $element['#default_value'] ?? '';
      /**/
      $element['value'] = [
        '#type' => 'select',
        //'#title' => $element['#title'],
        //'#title_display' => $element['#title_display'],
        '#empty_option' => t('-- Select --'),
        '#options' => $options,
        '#property' => $property,
        '#default_value' => $default_value,
        //'#access' => TRUE,
        //'#value' => $default_value,
      ];
      /**/
      //$element['#options'] = $options;
      //$element['#default_value'] = $default_value;
    }

    // Add validate callback.
    $element += ['#element_validate' => []];
    array_unshift($element['#element_validate'], [get_called_class(), 'validateBreezyLayoutsPropertySelectValue']);

    return $element;
  }

  /**
   * Prepares a BreezyLayoutsPropertySelect element.
   */
  public function preRenderBreezyLayoutsPropertySelect($element) {
    Element::setAttributes($element, [
      'id',
      'name' => 'breezy',
      'size',
    ]);
    static::setAttributes($element, ['form-select']);
    return $element;
  }

  /**
   * Validates a BreezyLayoutsPropertySelect element.
   */
  public static function validateBreezyLayoutsPropertySelectValue(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = NestedArray::getValue($form_state->getValues(), $element['#parents']);

    if (empty($value['value'])) {
      // @todo Add error.
    }

    $form_state->setValueForElement($element['value'], NULL);

    $element['#value'] = $value['value'];
    $form_state->setValueForElement($element, $value['value']);
  }

}
