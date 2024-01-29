<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;

/**
 * Provides a base class for deleting.
 */
abstract class BreezyLayoutsDeleteFormBase extends ConfirmFormBase {

  use BreezyLayoutsDialogFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'breezy_layouts_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'confirmation';
    $form[$this->getFormName()] = ['#type' => 'hidden', '#value' => 1];
    return $form;
  }

}
