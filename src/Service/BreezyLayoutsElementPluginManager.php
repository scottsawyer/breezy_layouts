<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\breezy_layouts\Annotation\BreezyLayoutsElement;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
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
    parent::__construct('Plugin/breezy_layouts/Element', $namespaces, $module_handler, 'Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface', 'Drupal\breezy_layouts\Annotation\BreezyLayoutsElement');
    $this->setCacheBackend($cache_backend, 'breezy_layouts_element');
    $this->alterInfo('breezy_layouts_element');
  }

  /**
   * {@inheritdoc}
   */
  public function initializeElement(array &$element) {
    $element_plugin = $this->getElementInstance($element);
    $element_plugin->initialize($element);
  }

  /**
   * {@inheritdoc}
   */
  public function buildElement(array &$element, array $form, FormStateInterface $form_state) {
    // Get the form object.
    $form_object = $form_state->getFormObject();

    $element_plugin = $this->getElementInstance($element);
    $element_plugin->prepare($element);
    $element_plugin->finalize($element);
    $element_plugin->setDefaultValue($element);

    // Allow modules to alter the breezy_layouts element.
    // @see \Drupal\Core\Field\WidgetBase::formSingleElement()
    $hooks = ['breezy_layouts_element'];
    if (!empty($element['#type'])) {
      $hooks[] = 'breezy_layouts_element_' . $element['#type'];
    }
    $context = ['form' => $form];
    $this->moduleHandler->alter($hooks, $element, $form_state, $context);

    // Allow handlers to alter the breezy_layouts element.
    // @todo Allow altering the element.
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

  /**
   * {@inheritdoc}
   */
  public function getElementPluginId(array $element) {
    if (isset($element['#breezy_layouts_plugin_id']) && $this->hasDefinition($element['#breezy_layouts_plugin_id'])) {
      return $element['#breezy_layouts_plugin_id'];
    }
    elseif (isset($element['#type']) && $this->hasDefinition($element['#type'])) {
      return $element['#type'];
    }
    elseif (isset($element['element']['type']) && $this->hasDefinition($element['#type'])) {
      return $element['element']['type'];
    }

    return $this->getFallbackPluginId(NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'breezy_layouts_element';
  }

  /**
   * {@inheritdoc}
   */
  public function getElementInstance(array $element, EntityInterface $entity = NULL) {
    $plugin_id = $this->getElementPluginId($element);

    /** @var \Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface $element_plugin */
    $element_plugin = $this->createInstance($plugin_id);

    if ($entity) {
      $element_plugin->setEntities($entity);
    }
    else {
      $element_plugin->resetEntities();
    }
    return $element_plugin;
  }

}
