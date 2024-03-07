<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Component\Utility\Html;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariant;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Form\BreezyLayoutsDialogFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an abstract element form base.
 */
abstract class BreezyLayoutsElementFormBase extends FormBase {

  use BreezyLayoutsDialogFormTrait;

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   */
  protected $elementManager;

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClasses;

  /**
   * Placeholder variant entity.
   *
   * @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface
   */
  protected $variant;

  /**
   * The element.
   *
   * @var array
   */
  protected $element = [];

  /**
   * The CSS property.
   *
   * @var string
   */
  protected $property;

  /**
   * The element key.
   *
   * @var string
   */
  protected $key;

  /**
   * The element parent key.
   *
   * @var string
   */
  protected $parentKey;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->elementManager = $container->get('plugin.manager.breezy_layouts.element');
    $instance->tailwindClasses = $container->get('breezy_layouts.tailwind_classes');
    $instance->variant = BreezyLayoutsVariant::create(['id' => '_variant_temp_form']);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $key = NULL, $parent_key = NULL, $type = NULL) {
    // Override an element's default value using the $form_state.
    if ($form_state->get('default_value')) {
      $this->element['#default_value'] = $form_state->get('default_value');
    }

    $this->property = $this->getRequest()->query->get('property');
    $this->parentKey = $this->getRequest()->query->get('parent');

    $this->key = $key;
    $this->variant = $breezy_layouts_variant;

    $element_plugin = $this->getElementPlugin();

    $form['#prefix'] = '<div id="' . $this->getWrapperId() . '">';
    $form['#suffix'] = '</div>';

    // @todo: Check if we're in a modal.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['#parents'] = [];
    $form['properties'] = [
      '#parents' => ['properties'],
      '#tree' => TRUE,
    ];
    $subform_state = SubformState::createForSubform($form['properties'], $form, $form_state);
    $subform_state->set('element', $this->element);
    $subform_state->set('property', $this->property);
    $form['properties'] = $element_plugin->buildConfigurationForm($form['properties'], $subform_state);

    // Set parent key.
    $form['parent_key'] = [
      '#type' => 'value',
      '#value' => $parent_key,
    ];

    /*
    // Set element type.
    $form['properties']['element']['type'] = [
      '#type' => 'item',
      '#title' => $this->t('Type'),
      'label' => [
        '#markup' => $element_plugin->label(),
      ],
      '#weight' => -100,
      '#parents' => ['type'],
    ];
    /**/

    $form['properties']['property'] = [
      '#type' => 'value',
      '#value' => $this->property,
      //'#parents' => ['properties'],
    ];

    $form['properties']['element']['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Key'),
      '#description' => $this->t('A unique identifier for this field.  Must only contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [
        'label' => '<br>' . $this->t('Key'),
        'exists' => [$this, 'exists'],
        'source' => ['properties', 'element', 'title'],
        'replace_pattern' => '[^a-z0-9_]+',
        'error' => $this->t('Must only contain lowercase letters, numbers, and underscores.'),
      ],
      '#required' => TRUE,
      '#parents' => ['key'],
      '#disabled' => ($key) ? TRUE : FALSE,
      '#default_value' => $key ?: '',
      '#weight' => -97,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 99,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save element'),
    ];
    return $this->buildDialogForm($form, $form_state);
  }

  /**
   * Never trigger validation.
   */
  public function noValidate(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $parent_key = $form_state->getValue('parent_key');
    $key = $form_state->getValue('key');

    $element_plugin = $this->getElementPlugin();

    // Submit element configuration.
    // Generally, elements will not be processing any submitted properties.
    // It is possible that a custom element might need to call a third-party API
    // to 'register' the element.
    $subform_state = SubformState::createForSubform($form['properties'], $form, $form_state);
    $element_plugin->submitConfigurationForm($form, $subform_state);

    // Add/update the element to the variant form.
    $properties = $element_plugin->getConfigurationFormProperties($form, $subform_state);

    // Make sure entire variant form is saved in its current state.
    $this->variant->setElementProperties($key, $properties, $parent_key);
    $this->variant->save();


    if ($this->requestStack->getCurrentRequest()->query->get('destination')) {
      $redirect_destination = $this->getRedirectDestination();
      $destination = $redirect_destination->get();
      $destination .= (strpos($destination, '?') !== FALSE ? '&' : '?') . 'update=' . $key;
      $redirect_destination->set($destination);
    }

    $query = ['update' => $key];
    $form_state->setRedirectUrl($this->variant->toUrl('edit-form', ['query' => $query]));

  }

  /**
   * Validated ajax form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   */
  public function validateAjaxForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Maps the parent child relationship of css properties.
   *
   * Certain CSS properties only make sense at certain levels (parent / child).
   *
   * @param string|null $level
   *   The level of the parent child tree.
   *
   * @return array
   *   An array of properties that apply at the provided level.
   */
  protected function cssPropertyMap($level = NULL) : array {
    $properties = $this->tailwindClasses->getPropertyMap();


    return $properties;
  }

  /**
   * Get element plugin.
   */
  public function getElementPlugin() {
    return $this->elementManager->getElementInstance($this->element, $this->variant);
  }

  /**
   * Add modal dialog support to a form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $settings
   *   Ajax settings.
   *
   * @return array
   *   The form with modal dialog support.
   */
  protected function buildDialogForm(array &$form, FormStateInterface $form_state, array $settings = []) {
    return $this->buildAjaxForm($form, $form_state, $settings);
  }

  /**
   * Determines if the element key already exists.
   *
   * @param string $key
   *   The element key.
   *
   * @return bool
   *   TRUE if the element key, FALSE otherwise.
   */
  public function exists($key) {
    // @todo Add a check for used keys.
    //$elements = $this->webform->getElementsInitializedAndFlattened();
    //return (isset($elements[$key])) ? TRUE : FALSE;
    return FALSE;
  }

  /**
   * Remove option callback.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The options portion of the form.
   */
  public function removeOptionCallback(array &$form, FormStateInterface $form_state) {
    return $form['properties']['element']['options'];
  }

  /**
   * Remove option submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function removeOptionSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $num_lines = $form_state->get('num_lines');
    $removed_lines = $form_state->get('removed_lines');
    $remaining_lines = $num_lines - count($removed_lines);

    if (($num_lines >= 1 && $remaining_lines > 1)
      && str_starts_with($trigger['#name'], '_remove_')) {
      $indexToRemove = str_replace('_remove_', '', $trigger['#name']);
      unset($form['options']['options'][$indexToRemove]);
      $options = $form_state->getvalue(['options', 'options']);
      unset($options[$indexToRemove]);
      $form_state->setValue(['options', 'options'], $options);
      $removed_lines[] = $indexToRemove;
      $form_state->set('removed_lines', $removed_lines);
    }
    $form_state->setRebuild();
  }

  /**
   * Callback for "add_option" button.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The form component.
   */
  public function addOptionCallback(array &$form, FormStateInterface $form_state) {
    return $form['properties']['element']['options'];
  }

  /**
   * Submit handler for the "add_option" button.
   *
   * Increments the max counter and causes a rebuild.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addOptionSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->set('num_lines', $form_state->get('num_lines') + 1);
    $form_state->setRebuild();
  }

}
