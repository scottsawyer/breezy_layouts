<?php

namespace Drupal\breezy_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;

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

  /**
   * Set element properties.
   *
   * @param string $key
   *   The element key.
   * @param array $properties
   *   The element properties.
   * @param string $parent_key
   *   The parent key.
   *
   * @return $this
   */
  public function setElementProperties($key, array $properties, $parent_key = '');


  /**
   * Build layout form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   An array of form elements.
   */
  public function buildLayoutForm($form, FormStateInterface $form_state);
}
