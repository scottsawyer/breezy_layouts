<?php

namespace Drupal\breezy_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\breezy_layouts\BreezyLayoutsVariantEntityInterface;

/**
 * Defines the BreezyLayoutVariant config entity.
 *
 * @ConfigEntityType(
 *   id = "breezy_layout_variant",
 *   label = @Translation("Breezy layout variant"),
 *   label_collection = @Translation("Breezy layout variants"),
 *   label_plural = @Translation("Breezy layout variants"),
 *   admin_permission = "administer breezy layout variants",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "layout",
 *   }
 * )
 */
class BreezyLayoutsVariant extends ConfigEntityBase implements BreezyLayoutsVariantEntityInterface {

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


}
