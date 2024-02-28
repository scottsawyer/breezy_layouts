<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Ajax\BreezyLayoutsHtmlCommand;

/**
 * Trait for BreezyLayoutsVariant entity ajax support.
 */
trait BreezyLayoutsEntityAjaxFormTrait {

  use BreezyLayoutsAjaxFormTrait;

  /**
   * {@inheritdoc}
   */
  protected function isAjax() {
    return TRUE;
  }

  /**
   * Replace form via an Ajax response.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An Ajax response that replaces a form.
   */
  protected function replaceForm(array $form, FormStateInterface $form_state) {
    // Display messages first by prefixing it the form and setting its weight
    // to -1000.
    $form = [
        'status_messages' => [
          '#type' => 'status_messages',
          '#weight' => -1000,
        ],
      ] + $form;

    // Remove wrapper.
    unset($form['#prefix'], $form['#suffix']);

    $response = new AjaxResponse();
    $response->addCommand(new BreezyLayoutsHtmlCommand('#' . $this->getWrapperId(), $form));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    if (!$this->getEntity()->isNew()) {
      $actions['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::noSubmit'],
        '#validate' => ['::noSubmit'],
        '#attributes' => ['class' => ['breezy-layouts-ajax-refresh']],
        '#weight' => 100,
      ];
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function cancelAjaxForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $entity_id = $entity->id();

    // Must complete reload the entity to make sure all changes are reflected.
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);
    $entity_storage->resetCache([$entity_id]);
    $entity = $entity_storage->load($entity_id);

    // Get form object.
    $form_object = $this->entityTypeManager->getFormObject($entity_type, $this->operation);

    // Set form entity.
    $form_object->setEntity($entity);

    // Set form state.
    $form_state = new FormState();
    $form_state->setFormState([]);
    $form_state->setUserInput([]);

    // Build form.
    /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
    $form_builder = \Drupal::service('form_builder');
    $form = $form_builder->buildForm($form_object, $form_state);

    // Return replace form as response.
    return $this->replaceForm($form, $form_state);
  }

}
