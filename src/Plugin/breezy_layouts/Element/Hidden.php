<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a hidden element.
 *
 * @BreezyLayoutsElement(
 *   id = "hidden",
 *   label = @Translation("Hidden"),
 *   description = @Translation("Provides a hidden element."),
 *   hidden = FALSE,
 *   multiple = FALSE,
 * )
 */
class Hidden extends BreezyLayoutsElementBase implements BreezyLayoutsElementInterface {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Value'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['$form_state->get(property): ' . $form_state->get('property')],
      ],
      '#options' => $this->tailwindClasses->getClassOptions($form_state->get('property')),
    ];

    return $form;
  }

}
