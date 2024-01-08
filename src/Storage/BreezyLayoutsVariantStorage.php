<?php

namespace Drupal\breezy_layouts\Storage;

use Drupal\Core\Config\Entity\ConfigEntityStorage;

/**
 * Defines the Breezy Layouts Variant storage.
 */
class BreezyLayoutsVariantStorage extends ConfigEntityStorage implements BreezyLayoutsVariantStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadValid() {
    $query = $this->getQuery()->condition('status', 1);

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }
    return $this->loadMultiple($result);
  }

}
