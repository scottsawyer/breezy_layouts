<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a generic element, used as fallback.
 *
 * @BreezyLayoutsElement(
 *   id = "breezy_layouts_element",
 *   label = @Translation("Generic element"),
 *   description = @Translation("Provides a generic element"),
 *   hidden = TRUE,
 * )
 */
class BreezyLayoutsElement extends BreezyLayoutsElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return (!empty($element['#type']) && !in_array($element['#type'], ['submit'])) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['element'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];
    return $form;
  }

}
