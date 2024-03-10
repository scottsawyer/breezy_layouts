<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariant;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Plugin\BreezyLayouts\Element\BreezyLayoutsElementInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\breezy_layouts\Form\BreezyLayoutsDialogFormTrait;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add property form.
 */
class BreezyLayoutsPropertyAddForm extends FormBase {

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
  public function getFormId() {
    return 'breezy_layouts_ui_add_element_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $type = NULL) {
    $parent_key = $this->getRequest()->query->get('parent');

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'breezy_layouts/breezy_layouts.ajax';

    $input = $form_state->getUserInput();
    $property_wrapper_id = 'property-wrapper';

    $form['#attributes'] = [
      'id' => $property_wrapper_id,
    ];

    $form['property_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Choose property'),
      '#description' => $this->t('Enter a CSS property name. Only one property may be added. Example: margin, padding, display.'),
      '#default_value' => $form_state->getValue('property_type') ?? '',
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::changePropertyType',
        'wrapper' => $property_wrapper_id,
        'event' => 'autocompleteclose',
      ],
      '#autocomplete_route_name' => 'breezy_layouts.property_controller',
    ];

    $property_type = $form_state->get('property_type');
    if ($form_state->getValue('property_type')) {
      $property_type = $form_state->getValue('property_type');
    }



    if ($property_type) {
      $form['elements'] = [
        '#type' => 'table',
        '#title' => $this->t('Choose element'),
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
            'class' => ['breezy-layouts-ajax-link'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
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
            'class' => ['breezy-layouts-ajax-link', 'button'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-dialog-options' => $dialog_options,
          ],
        ];
        $form['elements'][$element_type] = $row;
      }
    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $property_type = $form_state->getValue('property_type');
    $properties = $this->tailwindClasses->getPropertyOptions();
    if (!array_key_exists($property_type, $properties)) {
      $form_state->setErrorByName('property_type', 'Invalid property type.');
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
    $form_state->setRebuild();
  }

  /**
   * Submit form #ajax callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An Ajax response that display validation error messages or redirects
   *   to a URL
   */
  public function submitAjaxForm(array &$form, FormStateInterface $form_state) {
    // Remove #id from wrapper so that the form is still wrapped in a <div>
    // and triggerable.
    // @see js/webform.element.details.toggle.js
    $form['#prefix'] = '<div>';

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#breezy-layouts-ui-element-ajax-wrapper', $form));
    return $response;
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
      if ($definition['hidden'] === TRUE) {
        continue;
      }
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

  /**
   * Checks if this a property is valid.
   *
   * @param string $property_name
   *   The property name.
   *
   * @return bool
   *   True is the property name exists.
   */
  protected function isValidProperty(string $property_name = NULL) {
    if (!$property_name || empty($property_name)) {
      return FALSE;
    }
    $properties = $this->tailwindClasses->getPropertyOptions();
    return array_key_exists($property_name, $properties);
  }

}
