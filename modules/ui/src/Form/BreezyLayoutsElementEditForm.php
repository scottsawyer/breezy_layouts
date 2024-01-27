<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Edit element form.
 */
class BreezyLayoutsElementEditForm extends BreezyLayoutsElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_element_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $key = NULL, $parent_key = NULL, $type = NULL) {
    $parent_array = json_decode($parent_key);
    $this->element = $breezy_layouts_variant->getElementConfiguration($parent_array, $key);
    if ($this->element == NULL) {
      throw new NotFoundHttpException();
    }

    $form['#title'] = $this->t('Edit element');

    $form = parent::buildForm($form, $form_state, $breezy_layouts_variant, $key, $parent_key, $type);
    return $form;
  }

}
