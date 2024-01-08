<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides the form to delete a BreezyLayoutsVariant config entity.
 */
class BreezyLayoutsVariantDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %label', ['%label' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.breezy_layouts_variant.collection');
  }

}
