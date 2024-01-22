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
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $key = NULL, $parent_key = NULL, $type = NULL) {
    $this->property = $this->getRequest()->query->get('property');
    $this->parentKey = $this->getRequest()->query->get('parent');

    $this->key = $key;
    $this->element['#type'] = $type;
    $this->variant = $breezy_layouts_variant;

    $element_plugin = $this->getElementPlugin();

    $form['test'] = [
      '#type' => 'container',
    ];
    $form['test']['property'] = [
      '#markup' => '$property: ' . $this->property . '<br>',
      '#allowed_tags' => ['br'],
    ];
    $form['test']['type'] = [
      '#markup' => '$type: ' . $type,
    ];
    $form_state->set('property', $this->property);

    $form = parent::buildForm($form, $form_state, $breezy_layouts_variant, $key, $this->parentKey, $type);

    /**
    // Get the plugin configuration.
    $element_plugin_configuration = ['property' => $this->property];
    $element = $this->elementManager->createInstance($type, $element_plugin_configuration);
    $element_form = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $form['element_plugin_configuration'] = $element->form($element_form, $form_state);
    /**/
    return $form;
  }
}
