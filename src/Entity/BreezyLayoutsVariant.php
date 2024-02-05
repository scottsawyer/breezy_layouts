<?php

namespace Drupal\breezy_layouts\Entity;

use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface;

/**
 * Defines the BreezyLayoutsVariant config entity.
 *
 * @ConfigEntityType(
 *   id = "breezy_layouts_variant",
 *   label = @Translation("Breezy layout variant"),
 *   label_collection = @Translation("Breezy layout variants"),
 *   label_plural = @Translation("Breezy layout variants"),
 *   label_count = @PluralTranslation(
 *      singular = "@count variant",
 *      plural = "@count variants",
 *    ),
 *   admin_permission = "administer breezy layout variants",
 *   config_prefix = "breezy_layouts_variant",
 *   handlers = {
 *      "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *      },
 *     "storage" = "Drupal\breezy_layouts\Storage\BreezyLayoutsVariantStorage",
 *     "list_builder" = "Drupal\breezy_layouts\BreezyLayoutsVariantListBuilder",
 *     "form" = {
 *       "add" = "Drupal\breezy_layouts\Form\BreezyLayoutsVariantForm",
 *       "edit" = "Drupal\breezy_layouts\Form\BreezyLayoutsVariantForm",
 *       "delete" = "Drupal\breezy_layouts\Form\BreezyLayoutsVariantDeleteForm",
 *     },
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "status",
 *     "layout",
 *     "plugin_id",
 *     "plugin_configuration",
 *   },
 *   links = {
 *     "collection" = "/admin/config/content/breezy-layouts/variants",
 *     "edit-form" = "/admin/config/content/breezy-layouts/variants/{breezy_layouts_variant}",
 *     "delete-form" = "/admin/config/content/breezy-layouts/variants/{breezy_layouts_variant}/delete"
 *   }
 * )
 */
class BreezyLayoutsVariant extends ConfigEntityBase implements BreezyLayoutsVariantInterface {

  /**
   * The variant id.
   *
   * @var string
   */
  protected $id;

  /**
   * The variant label.
   *
   * @var string
   */
  protected $label;

  /**
   * The variant status.
   *
   * @var boolean
   */
  protected $status;

  /**
   * The layout the variant applies to.
   *
   * @var string
   */
  protected $layout;

  /**
   * The breakpoint group.
   *
   * @var string
   */
  protected $breakpointGroup;

  /**
   * The plugin ID.
   *
   * @var string
   */
  protected $plugin_id;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $plugin_configuration;

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginId($pluginId) {
    $this->plugin_id = $pluginId;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginConfiguration() {
    return $this->plugin_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginConfiguration(array $configuration) {
    $this->plugin_configuration = $configuration;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return $this->status;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->status = $enabled;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    if (!$this->isEnabled()) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setBreakpointGroup($breakpoint_group) {
    $this->breakpointGroup = $breakpoint_group;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBreakpointGroup() {
    return $this->breakpointGroup;
  }

  /**
   * Get element configuration.
   *
   * @param array $parent_key
   *   The element parent key.
   * @param mixed $key
   *   The element form key.
   *
   * @return array|null
   *   An array containing an initialized element.
   */
  public function getElementConfiguration(array $parent_key, string $key) {
    // Find the element configuration based on the key.
    $variant_plugin_configuration = $this->getPluginConfiguration();
    $existing_properties = NestedArray::getValue($variant_plugin_configuration, $parent_key);

    return $existing_properties[$key] ?? NULL;

  }

  /**
   * Get element id from key.
   *
   * @param mixed $key
   *   The form key.
   *
   * @return string|null
   *   The element plugin id.
   */
  public function getElementPluginId($key) {

  }

  /**
   * {@inheritdoc}
   */
  public function setElementProperties($key, array $properties, $parent_key = '') {
    $parent_array = json_decode($parent_key);
    if (empty($parent_array)) {
      return $this;
    }
    // Get variant plugin, determine where the $key and $parent_key goes, inject the new element, set it's properties.
    $plugin_configuration = $this->getPluginConfiguration();
    $existing_properties = NestedArray::getValue($plugin_configuration, $parent_array);
    if (is_array($existing_properties)) {
      $existing_properties[$key] = $properties;
    }
    else {
      $existing_properties = [$key => $properties];
    }
    NestedArray::setValue($plugin_configuration, $parent_array, $existing_properties);
    $this->setPluginConfiguration($plugin_configuration);
    return $this;
  }

  /**
   * Set element properties.
   *
   * @param array $elements
   *   An associative nested array of elements.
   * @param string $key
   *   The element's key.
   * @param array $properties
   *   An associative array of properties.
   * @param array $parent_key
   *   (optional) The element's parent key. Only used for new elements.
   *
   * @return bool
   *   TRUE when the element's properties has been set. FALSE when the element
   *   has not been found.
   */
  protected function setElementPropertiesRecursive(array &$elements, $key, array $properties, array $parent_key = []) {
    foreach ($parent_key as $parent_array_key) {
      if (array_key_exists($parent_array_key, $elements)) {

      }
    }
    foreach ($elements as $element_key => &$element) {
      // Make sure the element key is a string.
      $element_key = (string) $element_key;

      if (!BreezyLayoutsElementHelper::isElement($element, $element_key)) {
        continue;
      }

      if ($element_key === $key) {
        $element = $properties + BreezyLayoutsElementHelper::removeProperties($element);
        return TRUE;
      }

      if ($element_key === $parent_key) {
        $element[$key] = $properties;
        return TRUE;
      }

      if ($this->setElementPropertiesRecursive($element, $key, $properties, $parent_key)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Delete element.
   *
   * @param string $key
   *   The element key.
   * @param array $parent_key
   *   The element parent.
   */
  public function deleteElement($key, array $parent_key) {
    $configuration = $this->getPluginConfiguration();
    NestedArray::unsetValue($configuration, $parent_key, $key);
    $this->setPluginConfiguration($configuration);
  }

}
