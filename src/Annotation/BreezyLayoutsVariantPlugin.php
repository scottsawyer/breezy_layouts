<?php

namespace Drupal\breezy_layouts\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Breezy Layout Variant Plugin.
 *
 * @Annotation
 */
class BreezyLayoutsVariantPlugin extends Plugin {

  /**
   * The plugin label.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin description.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The layout plugin.
   *
   * @var string
   */
  public $layout;

  /**
   * Has a customizable "container".
   *
   * @var bool
   */
  public $container = FALSE;

  /**
   * Has a customizable "wrapper".
   *
   * @var bool
   */
  public $wrapper = FALSE;

}
