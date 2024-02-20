<?php

namespace Drupal\breezy_layouts\Service;

/**
 * Provides an interface for VariantManager.
 */
interface VariantManagerInterface {

  /**
   * Get BreezyLayoutVariant options for layout.
   *
   * @param string $layout
   *   The layout.
   *
   * @return array
   *   An array of BreezyLayoutsVariant options.
   */
  public function getVariantOptionsForLayout(string $layout) : array ;

}
