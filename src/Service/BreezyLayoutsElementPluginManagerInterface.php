<?php

namespace Drupal\breezy_layouts\Service;

interface BreezyLayoutsElementPluginManagerInterface {


  /**
   * Get valid definitions.
   *
   * @var bool $container
   *   Whether the element is a container.
   *
   * @return array
   *   An array of valid plugin definitions.
   */
  public function getValidDefinitions(bool $container) : array ;

}
