<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

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
      if (!isset($element['#default_value'])) {
        return [];
      }

      $options = $element['#default_value'];

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



  }

  /**
   * Convert values to options.
   */


}
