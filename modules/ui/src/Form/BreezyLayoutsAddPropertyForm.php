<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add property form.
 */
class BreezyLayoutsAddPropertyForm extends FormBase {

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClassService;

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   */
  protected $elementManager;

  /**
   * The Varient form element parent key.
   *
   * @var string
   */
  protected $parentKey;


  /**
   * Constructs a new BreezyLayoutsAddPropertyForm.php
   *
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes
   *   Tailwind classes.
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $element_manager
   *   The element plugin manager.
   */
  public function __construct(BreezyLayoutsTailwindClassServiceInterface $tailwind_classes, BreezyLayoutsElementPluginManagerInterface $element_manager) {
    $this->tailwindClassService = $tailwind_classes;
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $tailwind_classes = $container->get('breezy_layouts.tailwind_classes');
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $element_manager */
    $element_manager = $container->get('plugin.manager.breezy_layouts.element');
    return new static(
      $tailwind_classes,
      $element_manager
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_add_property_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $parent_key = $this->getRequest('parent');
    $options = $this->tailwindClassService->getPropertyOptions();
    $input = $form_state->getUserInput();
    $property_wrapper_id = 'property-wrapper';

    $form['#attributes'] = [
      'id' => $property_wrapper_id,
    ];
    $form['property_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose property'),
      '#options' => $options,
      '#default_value' => $form_state->getValue('property_type') ?? '',
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::changePropertyType',
        'wrapper' => $property_wrapper_id,
      ],
    ];

    $property_type = $form_state->get('property_type');
    if ($form_state->getValue('property_type')) {
      $property_type = $form_state->getValue('property_type');
    }

    if ($property_type) {
      $form['elements'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select a form element'),
        '#description' => $this->t('Element type will render the property on the layout configuration form.'),
        '#options' => $this->getElementOptions(),
        '#default_value' => $form_state->getValue('elements') ?? '',
        '#required' => TRUE,
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Get element options.
   *
   * @return array
   *   An array of element plugins.
   */
  protected function getElementOptions() {
    $elements = [];
    $element_definitions = $this->elementManager->getValidDefinitions();
    foreach ($element_definitions as $id => $definition) {
      $elements[$id] = $definition['label'];
    }
    return $elements;
  }

  /**
   * Callback when property type is changed.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function changePropertyType(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
