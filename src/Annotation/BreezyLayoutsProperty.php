<?php

namespace Drupal\breezy_layouts\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Breezy Layout Property Plugin.
 *
 * @Annotation
 */
class BreezyLayoutsProperty extends Plugin {

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
}
