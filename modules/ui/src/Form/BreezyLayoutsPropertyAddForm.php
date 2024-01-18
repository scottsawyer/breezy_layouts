<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add property form.
 */
class BreezyLayoutsPropertyAddForm extends BreezyLayoutsElementFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_add_element_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $type = NULL) {
    $parent_key = $this->getRequest()->query->get('parent');

    $form = parent::buildForm($form, $form_state, $breezy_layouts_variant);

    $options = $this->tailwindClasses->getPropertyOptions();
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
        '#type' => 'table',
        '#header' => [$this->t('Type'), $this->t('Description'), ''],
      ];

      $elements = $this->getElementOptions();
      foreach ($elements as $element_type => $element_definition) {
        $row = [];
        $dialog_options = Json::encode([
          'width' => 550,
        ]);
        $query = [
          'property' => $property_type,
        ];
        if ($parent_key) {
          $query['parent'] = $parent_key;
        }
        $url = Url::fromRoute('entity.breezy_layouts_ui.element.add_form', [
          'breezy_layouts_variant' => $breezy_layouts_variant->id(),
          'type' => $element_type,
        ],
        [
          'query' => $query,
        ]);
        $row['link'] = [
          '#type' => 'link',
          '#title' => $element_definition['label'],
          '#url' => $url,
          '#attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-render' => 'off_canvas',
            'data-dialog-options' => $dialog_options,
          ],
        ];

        $row['description'] = [
          '#markup' => $element_definition['description'],
        ];

        $row['operation'] = [
          '#type' => 'link',
          '#title' => $this->t('Add element'),
          '#url' => $url,
          '#attributes' => [
            'class' => ['use-ajax', 'button'],
            'data-dialog-type' => 'dialog',
            'data-dialog-render' => 'off_canvas',
            'data-dialog-options' => $dialog_options,
          ],
        ];
        $form['elements'][$element_type] = $row;
      }
    }

    unset($form['actions']);

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
      $elements[$id] = $definition;
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
