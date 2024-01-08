<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Variant;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for BreezyLayoutsVariant plugins.
 */
interface BreezyLayoutsVariantPluginInterface extends ConfigurableInterface, ContainerFactoryPluginInterface, PluginFormInterface {

  /**
   * Retrieves the plugin's label.
   *
   * @return string
   *   The plugin's human-readable and translated label.
   */
  public function label();

  /**
   * Retrieves the plugin's description.
   *
   * @return string|null
   *   The plugin's translated description; or NULL if it has none.
   */
  public function getDescription();

  /**
   * Retrieves the layout plugin.
   *
   * @return string
   *   The layout plugin name.
   */
  public function getLayoutId();

  /**
   * Determines if the plugin has a layout form.
   *
   * @return bool
   *   True if the plugin has a layout form.
   */
  public function hasLayoutForm();

}
