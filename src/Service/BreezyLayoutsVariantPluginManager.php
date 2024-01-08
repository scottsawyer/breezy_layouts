<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\breezy_layouts\Annotation\BreezyLayoutsVariantPlugin;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Variant\BreezyLayoutsVariantPluginInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Provides a manager for Breezy Layouts Variants plugins.
 */
class BreezyLayoutsVariantPluginManager extends DefaultPluginManager implements BreezyLayoutsVariantPluginManagerInterface {

  use DependencySerializationTrait;

  /**
   * Drupal\Core\Layout\LayoutPluginManagerInterface definition.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutPluginManager;

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
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, LayoutPluginManagerInterface $layout_plugin_manager) {
    parent::__construct('Plugin/breezy_layouts/Variant', $namespaces, $module_handler, BreezyLayoutsVariantPluginInterface::class, BreezyLayoutsVariantPlugin::class);
    $this->setCacheBackend($cache_backend, 'breezy_layouts_variant_plugin');
    $this->alterInfo('breezy_layouts_variant_plugin');
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function validateRequirements(string $plugin_id): bool {
    $plugin = $this->getDefinition($plugin_id);
    if (isset($plugin['layout'])) {
      $required_layout = $plugin['layout'];
      $layout_options = $this->layoutPluginManager->getLayoutOptions();
      // Plugins require a layout, check if the layout exists.
      foreach ($layout_options as $name => $definition) {
        if (isset($definition[$required_layout])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidDefinitions(): array {
    $definitions = [];
    foreach ($this->getDefinitions() as $id => $definition) {
      if ($this->validateRequirements($id)) {
        $definitions[$id] = $definition;
      }
    }
    return $definitions;
  }

}
