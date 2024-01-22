<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for BreezyLayoutsElement plugins.
 */
interface BreezyLayoutsElementInterface extends ConfigurableInterface, ContainerFactoryPluginInterface, PluginFormInterface {

  /**
   * Retrieves the plugin's label.
   *
   * @return string
   *   The plugin's human-readable and translated label.
   */
  public function label();

  /**
   * Retrieves the plugin's description.
   *
   * @return string|null
   *   The plugin's translated description; or NULL if it has none.
   */
  public function getDescription();

  /**
   * Provides a form to configure the element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function form(array $form, FormStateInterface $form_state);

  /**
   * Set variant entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to set.
   *
   * @return mixed
   *
   * @thows \Exception
   */
  public function setEntities(EntityInterface $entity);
  /**
   * Reset variant entity.
   */
  public function resetEntities();

}
