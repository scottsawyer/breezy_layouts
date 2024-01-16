<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add property form.
 */
class BreezyLayoutsElementAddForm extends BreezyLayoutsElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_add_element_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $variant = NULL) {
    $parent_key = $this->getRequest()->query->get('parent');

    $form = parent::buildForm($form, $form_state, $webform);

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
      $form['element'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select a form element'),
        '#description' => $this->t('Element type will render the property on the layout configuration form.'),
        '#options' => $this->getElementOptions(),
        '#default_value' => $form_state->getValue('elements') ?? '',
        '#required' => TRUE,
      ];
    }

    $element_plugin_id = $input['element_configuration'] ?? $variant->



    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [

      ],
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
