<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;

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
    $form['#theme'] = 'confirm_form';
    $form[$this->getFormName()] = ['#type' => 'hidden', '#value' => 1];
    $form['#title'] = $this->getQuestion();
    $form['description'] = $this->getDescription();

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm'),
    ];
    return $this->buildDialogForm($form, $form_state);
  }

}
