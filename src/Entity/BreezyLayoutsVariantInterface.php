<?php

namespace Drupal\breezy_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Breezy Layout Variant entities.
 */
interface BreezyLayoutsVariantInterface extends ConfigEntityInterface {

  /**
   * Get plugin id.
   *
   * @return string
   *   The plugin id.
   */
  public function getPluginId();

  /**
   * Set plugin id.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return
   */
  public function setPluginId($pluginId);

}
