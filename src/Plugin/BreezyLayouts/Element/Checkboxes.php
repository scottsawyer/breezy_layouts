<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'checkboxes' element.
 *
 * @BreezyLayoutsElement(
 *   id = "checkboxes",
 *   label = @Translation("Checkboxes"),
 *   description = @Translation("Provides checkboxes element."),
 *   hidden = FALSE,
 *   multiple = TRUE,
 * )
 */
class Checkboxes extends OptionsBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'multiple' => TRUE,
      'multiple_error' => '',
      'options_description_display' => 'description',
      'options__properties' => [],
      'wrapper_type' => 'fieldset',
    ] + parent::defineDefaultProperties();
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsMultipleValues() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasMultipleValues(array $element) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Checkboxes require > 2 options.
    $form['element']['multiple']['#min'] = 2;

    return $form;
  }

}
