<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Variant;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
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

  /**
   * If the plugin supports a customizable "container".
   *
   * @return bool
   *   True if the plugin supports customizing a container element.
   */
  public function hasContainer();

  /**
   * If the plugin supports a customizable "wrapper" element.
   *
   * @return bool
   *   True if the plugin supports customizing a wrapper element.
   */
  public function hasWrapper();

  /**
   * Layout form.
   *
   * @param array $form
   *   The layout form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The layout form.
   */
  public function layoutForm(array $form, FormStateInterface $form_state);

  /**
   * Build layout classes
   *
   * @param array $layout_settings
   *   The settings submitted from the layout configuration form.
   *
   * @return array
   *   An array of classes keyed by the element.
   */
  public function buildLayoutClasses(array $layout_settings);

}
