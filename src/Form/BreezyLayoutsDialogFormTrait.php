<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait form dialog forms.
 */
trait BreezyLayoutsDialogFormTrait {

  use BreezyLayoutsAjaxFormTrait;

  /**
   * {@inheritdoc}
   */
  protected function isAjax() {
    return $this->isDialog();
  }

  /**
   * Add modal dialog support to a form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $settings
   *   Ajax settings.
   *
   * @return array
   *   The breezy layouts variant form with modal dialog support.
   */
  protected function buildDialogForm(array &$form, FormStateInterface $form_state, array $settings = []) {
    return $this->buildAjaxForm($form, $form_state, $settings);
  }


  /**
   * Add modal dialog support to a confirm form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The Variant with modal dialog support.
   */
  protected function buildDialogConfirmForm(array &$form, FormStateInterface $form_state) {
    if (!$this->isDialog() || $this->isOffCanvasDialog()) {
      return $form;
    }

    $this->buildDialogForm($form, $form_state);

    // Replace 'Cancel' link button with a close dialog button.
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#validate' => ['::noValidate'],
      '#submit' => ['::noSubmit'],
      '#weight' => 100,
      '#ajax' => [
        'callback' => '::cancelAjaxForm',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function cancelAjaxForm(array &$form, FormStateInterface $form_state) {
    $response = $this->createAjaxResponse($form, $form_state);
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

  /**
   * Validate callback to clear validation errors.
   */
  public function noValidate(array &$form, FormStateInterface $form_state) {
    // Clear all validation errors.
    $form_state->clearErrors();
  }

  /**
   * Empty submit callback used to only have the submit button to use an #ajax submit callback.
   */
  public function noSubmit(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * Close dialog.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool|\Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages.
   */
  public function closeDialog(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }

}
