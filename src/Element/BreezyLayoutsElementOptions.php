<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a form element for managing breezy_layouts element options.
 *
 * @FormElement("breezy_layouts_element_options")
 */
class BreezyLayoutsElementOptions extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class();
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processAjaxForm'],
      ],
      '#theme_wrapper' => ['form_element'],
      '#options_description' => FALSE,
    ];
  }


}
