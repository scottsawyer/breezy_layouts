<?php

namespace Drupal\breezy_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

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

}
