<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\breezy_layouts\Annotation\BreezyLayoutsElement;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Provides a manager for Breezy Layouts Element plugins.
 */
class BreezyLayoutsElementPluginManager extends DefaultPluginManager implements BreezyLayoutsElementPluginManagerInterface {

  use DependencySerializationTrait;

  /**
   * Constructs a new BreezyLayoutsVariantPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/breezy_layouts/Element', $namespaces, $module_handler, BreezyLayoutsElementInterface::class, BreezyLayoutsElement::class);
    $this->setCacheBackend($cache_backend, 'breezy_layouts_element');
    $this->alterInfo('breezy_layouts_element');
  }

  /**
   * {@inheritdoc}
   */
  public function getValidDefinitions($container = FALSE) : array {
    $definitions = [];
    foreach ($this->getDefinitions() as $id => $definition) {
      if ($definition['container'] == $container) {
        $definitions[$id] = $definition;
      }
    }
    return $definitions;
  }
}
