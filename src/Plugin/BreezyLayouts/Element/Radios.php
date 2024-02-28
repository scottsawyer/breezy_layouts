<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides radio buttons.
 *
 * @BreezyLayoutsElement(
 *   id = "radios",
 *   label = @Translation("Radios"),
 *   description = @Translation("Provides radio buttons element."),
 *   hidden = FALSE,
 *   multiple = FALSE,
 * )
 */
class Radios extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
        // Form display.
        'options_description_display' => 'description',
        'options__properties' => [],
        // Wrapper.
        'wrapper_type' => 'fieldset',
      ] + parent::defineDefaultProperties();
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    // Unset empty string as default option to prevent '' === '0' issue.
    // @see \Drupal\Core\Render\Element\Radio::preRenderRadio
    if (isset($element['#default_value'])
      && $element['#default_value'] === ''
      && !isset($element['#options'][$element['#default_value']])) {
      unset($element['#default_value']);
    }
  }

}
