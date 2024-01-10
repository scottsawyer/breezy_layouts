<?php

namespace Drupal\breezy_layouts\Service;

/**
 * Provides an interface for BreezyLayoutsTailwindClassService.
 */
interface BreezyLayoutsTailwindClassServiceInterface {

  /**
   * Get margin classes.
   *
   * @return string[]
   *   The margin classes.
   */
  public function getMargin();

  /**
   * Get padding classes.
   *
   * @return string[]
   *   The padding classes.
   */
  public function getPadding();

  /**
   * Get align-item classes.
   *
   * @return string[]
   *   The align item classes.
   */
  public function getAlignItems();

  /**
   * Get justify content classes.
   *
   * @return string[]
   *   The justify-content classes.
   */
  public function getJustifyContent();

  /**
   * Get gap classes.
   *
   * @return string[]
   *   The gap classes.
   */
  public function getGap();

  /**
   * Get flex basis classes.
   *
   * @return string[]
   *   The flex basis classes.
   */
  public function getFlexBasis();

  /**
   * Get flex direction classes.
   *
   * @return string[]
   *   The flex-direction classes.
   */
  public function getFlexDirection();

  /**
   * Get order classes.
   *
   * @return string[]
   *   The order classes.
   */
  public function getOrder();

  /**
   * Get class options.
   *
   * @param string $property
   *   The css property name.
   *
   * @return array
   *   An array of class options.
   */
  public function getClassOptions(string $property) : array;

  /**
   * Get property options.
   *
   * @return array
   *   An array of css property options.
   */
  public function getPropertyOptions() : array;
}
