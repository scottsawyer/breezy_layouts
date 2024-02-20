<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Render\Element\FormElement;
use Drupal\breezy_layouts\Utility\BreezyLayoutsOptionsHelper;

/**
 * Provides a BreezyLayouts element to assist in creation of options.
 *
 * @FormElement("breezy_layouts_options")
 */
class BreezyLayoutsOptions extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#label' => $this->t('option'),
      '#labels' => $this->t('options'),
      '#min_items' => 3,
      '#empty_items' => 1,
      '#add_more_items' => 1,
      '#options_text_maxlength' => 512,
      '#options_description' => FALSE,
      '#options_description_maxlength' => NULL,
      '#property' => NULL,
      '#process' => [
        [$class, 'processBreezyLayoutsOptions'],
      ],
      '#theme_wrapper' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (!isset($element['#default_value']) || $element['#default_value'] == '') {
        return [];
      }

      $options = (is_string($element['#default_value'])) ? Yaml::decode($element['#default_value']) : $element['#default_value'];

      if (!is_array($options)) {
        return [$options];
      }
      return static::convertOptionsToValues($options);
    }
    elseif (is_array($input) && isset($input['options'])) {
      return $input['options'];
    }
    else {
      return NULL;
    }

  }

  /**
   * Process options and build options widget.
   */
  public static function processBreezyLayoutsOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;
    // Add validate callback that extracts the associative array of options.
    $element += ['#element_validate' => []];
    array_unshift($element['#element_validate'], [get_called_class(), 'validateBreezyLayoutsOptions']);

    $t_args = ['@label' => isset($element['#label']) ? Unicode::ucfirst($element['#label']) : t('Options')];
    $properties = ['#label', '#labels', '#min_items', '#empty_items', '#add_more_items'];

    if (isset($element['#default_value']) && !is_array($element['#default_value'])) {
      $element['#default_value'] = [$element['#default_value']];
    }
    $element['options'] = array_intersect_key($element, array_combine($properties, $properties)) + [
        '#type' => 'breezy_layouts_multiple',
        '#header' => TRUE,
        //'#key' => 'value',
        '#default_value' => (isset($element['#default_value'])) ? static::convertOptionsToValues($element['#default_value'], $element['#options_description']) : [],
        '#add_more_input_label' => t('more @options', ['@options' => $element['#labels']]),
      ];

    /**/
    $element['options']['#element'] = [
      'option_value' => [
        '#type' => 'container',
        '#title' => t('@label value', $t_args),
        '#description' => t('A unique value stored in the database.'),
        'value' => [
          '#type' => 'breezy_layouts_property_select',
          '#property' => $element['#property'],
          '#title' => t('@label value', $t_args) . implode('|', $element['#parents']),
          '#title_display' => 'invisible',
          '#placeholder' => t('Enter value…'),
          '#attributes' => ['class' => ['js-breezy-layouts-options-sync']],
          '#error_no_message' => TRUE,
          '#parents' => $element['#parents'],
        ],
      ],
      'option_text' => [
        '#type' => 'container',
        '#title' => t('@label text', $t_args),
        '#help' => t('Text to be displayed on the form.'),
        'text' => [
          '#type' => 'textfield',
          '#title' => t('@label text', $t_args),
          '#title_display' => 'invisible',
          '#placeholder' => t('Enter text…'),
          '#error_no_message' => TRUE,
        ],
      ],
    ];
    /**/

    return $element;
  }

  /**
   * Convert options to values for breezy_layouts_multiple element.
   *
   * @param array $options
   *   An array of options.
   * @param bool $options_description
   *   Options has description.
   *
   * @return array
   *   An array of values.
   */
  public static function convertOptionsToValues(array $options = [], $options_description = FALSE) {
    $values = [];
    foreach ($options as $key => $vals) {
      if (isset($vals['value'])) {
        $values[] = [
          'value' => $vals['value'],
          'text' => $vals['text'] ?? $vals['value'],
        ];
      }
    }
    return $values;
  }

  /**
   * Validates BreezyLayoutsOptions element.
   */
  public static function validateBreezyLayoutsOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $options_value = NestedArray::getValue($form_state->getValues(), $element['options']['#parents']);

    $value = $options_value;

    // @todo Add error.

    $form_state->setValueForElement($element['options'], NULL);
    $element['#value'] = $value;
    $form_state->setValueForElement($element, $value);
  }


}
