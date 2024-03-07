<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a select element.
 *
 * @BreezyLayoutsElement(
 *   id = "select",
 *   label = @Translation("Select"),
 *   description = @Translation("Provides a select element."),
 *   hidden = FALSE,
 *   multiple = FALSE,
 *   ui = TRUE,
 * )
 */
class Select extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'multiple' => FALSE,
        'empty_option' => '',
        'empty_value' => '',
    ] + parent::defineDefaultProperties();
    return $properties;
  }

}
