<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

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
 *   ui = FALSE,
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
    $form = parent::form($form, $form_state);

    unset($form['element']['required']);

    $form['element']['default_value'] = [
      '#type' => 'breezy_layouts_property_select',
      '#title' => $this->t('Property value'),
      '#property' => $form_state->get('property'),
      '#parents' => [$form['#parents'], 'element', 'default_value'],
    ];

    return $form;
  }

}
