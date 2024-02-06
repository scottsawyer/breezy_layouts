<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base "container" class.
 */
abstract class ContainerBase extends BreezyLayoutsElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [
        'title' => '',
        // Form validation.
        'required' => FALSE,
        // Attributes.
        'attributes' => [],
      ] + $this->defineDefaultBaseProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

}
