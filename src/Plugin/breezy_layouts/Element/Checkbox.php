<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'checkbox' element.
 *
 * @BreezyLayoutsElement(
 *   id = "checkbox",
 *   label = @Translation("Checkbox"),
 *   description = @Translation("Provides a form element for a single checkbox"),
 * )
 */
class Checkbox extends BreezyLayoutsElementBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['single']['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Checkbox value'),
      '#default_value' => '',
      '#required' => TRUE,
      '#options' => $this->tailwindClasses->getClassOptions($form_state->get('property')),
    ];
    $form['single']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkbox label'),
      '#required' => TRUE,
      '#default_value' => '',
    ];

    return $form;
  }
}
