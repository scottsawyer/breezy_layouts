<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface form BreezyLayoutsElementPluginManager.
 */
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

  /**
   * Is an element's plugin id.
   *
   * @param array $element
   *   A element.
   *
   * @return string
   *   An element's $type has a corresponding plugin id, else
   *   fallback 'element' plugin id.
   */
  public function getElementPluginId(array $element);

  /**
   * Get a webform element plugin instance for an element.
   *
   * @param array $element
   *   An associative array containing an element with a #type property.
   * @param \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $entity
   *   A Breezy Layouts Variant entity.
   *
   * @return \Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface
   *   A Breezy Layouts element plugin instance
   *
   * @throws \Exception
   *   Throw exception if entity type is not a Breezy Layouts Variant.
   */
  public function getElementInstance(array $element, EntityInterface $entity = NULL);

}
