<?php

namespace Drupal\breezy_layouts\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Breezy Layouts Element plugin.
 */
class BreezyLayoutsElement extends Plugin {

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
   * The element is hidden.
   *
   * @var bool
   */
  public $hidden = FALSE;

  /**
   * Element allows multiple.
   *
   * @var bool
   */
  public $multiple = FALSE;

  /**
   * Is container.
   *
   * @var bool
   */
  public $container = FALSE;

}
