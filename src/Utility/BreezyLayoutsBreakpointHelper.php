<?php

namespace Drupal\breezy_layouts\Utility;

/**
 * Provides breakpoint helper functions.
 */
class BreezyLayoutsBreakpointHelper {

  /**
   * Sanitizes breakpoint names.
   *
   * @param string $original_name
   *   The original breakpoint name.
   *
   * @return string
   *   The sanitized breakpoint name.
   */
  public static function getSanitizedBreakpointName(string $original_name) {
    // Breakpoints may be represented with a dot ".", which is illegal as a
    // key.
    // Convert the dot to double underscore, but convert back.
    // @todo Create a method that properly sanitizes separators.
    return str_replace('.', '__', $original_name);
  }

  /**
   * Unsanitizes breakpoint name.
   *
   * @param string $breakpoint_name
   *   The breakpoint name.
   *
   * @return string
   *   The original breakpoint name.
   */
  public static function getOriginalBreakpointName(string $breakpoint_name) {
    return str_replace('__', '.', $breakpoint_name);
  }

}
