<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Add Element form.
 */
class BreezyLayoutsElementAddForm extends BreezyLayoutsElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_element_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $type = NULL) {
    $property = $this->getRequest()->query->get('property');
    $parent_key = $this->getRequest()->query->get('parent');

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('title') ?? '',
    ];
    $form['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Key'),
      '#description' => $this->t('A unique identifier for this field.  Must only contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [
        'label' => '<br>' . $this->t('Key'),
        'source' => ['title'],
        'replace_pattern' => 'a-z0-9_',
        'error' => $this->t('Must only contain lowercase letters, numbers, and underscores.'),
      ],
      '#required' => TRUE,
      '#parents' => ['key'],
    ];

    $form_state->set('property', $property);

    $form = parent::buildForm($form, $form_state, $breezy_layouts_variant, $type);

    // Get the plugin configuration.
    $element_plugin_configuration = ['property' => $property];
    $element = $this->elementManager->createInstance($type, $element_plugin_configuration);
    $element_form = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['element_plugin_configuration'] = $element->form($element_form, $form_state);
    return $form;
  }
}
