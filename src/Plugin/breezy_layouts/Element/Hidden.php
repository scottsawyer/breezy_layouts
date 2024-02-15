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
  protected function defineDefaultProperties() {
    return [
      'title' => '',
      'default_value' => '',
      'property' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $logger = \Drupal::logger('hidden');
    $form = parent::form($form, $form_state);

    /**/
    $form['element']['default_value'] = [
      '#type' => 'breezy_layouts_property_select',
      '#title' => $this->t('Property value'),
      '#property' => $form_state->get('property'),
      '#parents' => [$form['#parents'], 'element', 'default_value'],
    ];
    /**/
    /*
    $form['element']['default_value'] = [
      '#type' => 'select',
      '#title' => $this->t('Value'),
      '#default_value' => 'm-3',
      '#required' => TRUE,
      '#attributes' => [
        'class' => [''],
      ],
      '#options' => $this->tailwindClasses->getClassOptions($form_state->get('property')),
    ];
    /**/

    return $form;
  }

}
