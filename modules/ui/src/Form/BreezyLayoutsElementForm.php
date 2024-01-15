<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariant;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for configuring an element.
 */
class BreezyLayoutsElementForm extends FormBase {

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   */
  protected $elementManager;

  /**
   * use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClasses;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->elementManager = $container->get('plugin.manager.breezy_layouts.element');
    $instance->tailwindClasses = $container->get('breezy_layouts.tailwind_classes');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_element_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $variant = NULL, $key = NULL, $parent_key = NULL, $type = NULL) {
    // Override an element's default value using the $form_state.
    if ($form_state->get('default_value')) {
      $this->element['#default_value'] = $form_state->get('default_value');
    }

    // Set default values.
    // Get plugin instance.
    // Get plugin form.

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
