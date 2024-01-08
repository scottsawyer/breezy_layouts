<?php

namespace Drupal\breezy_layouts\Storage;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines the interface for Breezy Layouts Variant storage.
 */
interface BreezyLayoutsVariantStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Loads the valid Breezy Layouts Variant config entities.
   *
   * @return \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface[]
   */
  public function loadValid();

}
