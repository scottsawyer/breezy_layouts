<?php

namespace Drupal\breezy_layouts\Service;

/**
 * Provides an interface for Breezy Layouts Plugin Manager.
 */
interface BreezyLayoutsVariantPluginManagerInterface {

  /**
   * Validates requirements.
   *
   * @param string $pluginId
   *   The plugin id.
   *
   * @return bool
   *   If the plugin requirements are validated.
   */
  public function validateRequirements(string $pluginId): bool;

  /**
   * Get valid definition.
   *
   * @return array
   *   An array of valid definitions.
   */
  public function getValidDefinitions() : array;

}