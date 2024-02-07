<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Plugin\PluginBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base plugin for BreezyLayoutsElement plugin.
 *
 * @package Drupal\breezy_layouts\Plugin\breezy_layouts\Element.
 */
class BreezyLayoutsElementBase extends PluginBase implements BreezyLayoutsElementInterface {

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassesServiceInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface
   */
  protected $tailwindClasses;

  /**
   * Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface definition.
   *
   * @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface
   */
  protected $variant = NULL;

  /**
   * The element.
   *
   * @var array
   */
  protected $element = [];

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   * definition.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   */
  protected $elementManager;

  /**
   * An associative array of an element's default properties names and values.
   *
   * @var array
   */
  protected $defaultProperties;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsTailwindClassServiceInterface $tailwind_classes */
    $instance->tailwindClasses = $container->get('breezy_layouts.tailwind_classes');

    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $elementManager */
    $instance->elementManager = $container->get('plugin.manager.breezy_layouts.element');

    return $instance;
  }

  /**
   * Define an element's default properties.
   *
   * @return array
   *   An associative array contain an the element's default properties.
   */
  protected function defineDefaultProperties() {
    $properties = [
      'title' => '',
      'default_value' => '',
      'required' => FALSE,
      'attributes' => [],
    ];
    $properties += $this->defineDefaultBaseProperties();
    return $properties;
  }

  /**
   * Define default multiple properties used by most elements.
   *
   * @return array
   *   An associative array containing default multiple properties.
   */
  protected function defineDefaultMultipleProperties() {
    return [
      'multiple' => FALSE,
      'multiple__header_label' => '',
      'multiple__min_items' => NULL,
      'multiple__empty_items' => 1,
      'multiple__add_more' => TRUE,
      'multiple__add_more_items' => 1,
      'multiple__add_more_button_label' => (string) $this->t('Add'),
      'multiple__add_more_input' => TRUE,
      'multiple__add_more_input_label' => (string) $this->t('more items'),
      'multiple__item_label' => (string) $this->t('item'),
      'multiple__no_items_message' => '<p>' . $this->t('No items entered. Please add items below.') . '</p>',
      'multiple__sorting' => TRUE,
      'multiple__operations' => TRUE,
      'multiple__add' => TRUE,
      'multiple__remove' => TRUE,
    ];
  }

  /**
   * Define default base properties used by all elements.
   *
   * @return array
   *   An associative array containing base properties used by all elements.
   */
  protected function defineDefaultBaseProperties() {
    return [

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    if (!isset($this->defaultProperties)) {
      $properties = $this->defineDefaultProperties();
      // Apply default format settings to element edit form properties.
      // This approach prevents having to refactor how default formats
      // are handled.
      $this->defaultProperties = $properties;
    }
    return $this->defaultProperties;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'property' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return (!empty($element['#type'])) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasProperty($property_name) {
    $default_properties = $this->getDefaultProperties();
    return array_key_exists($property_name, $default_properties);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperty($property_name) {
    $default_properties = $this->getDefaultProperties();
    return (array_key_exists($property_name, $default_properties)) ? $default_properties[$property_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementProperty(array $element, $property_name) {
    return $element["#$property_name"] ?? $this->getDefaultProperty($property_name);
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return $this->pluginDefinition['hidden'];
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array &$element) {
    // Set element options.
    if (isset($element['#options'])) {
      $element['#options'] = WebformOptions::getElementOptions($element);
    }

    // Set #admin_title to #title without any HTML markup.
    if (!empty($element['#title']) && empty($element['#admin_title'])) {
      $element['#admin_title'] = strip_tags($element['#title']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    $attributes_property = ($this->hasWrapper($element)) ? '#wrapper_attributes' : '#attributes';

    // Enable webform template preprocessing enhancements.
    // @see \Drupal\webform\Utility\WebformElementHelper::isWebformElement
    $element['#breezy_layouts_element'] = TRUE;

    // Add .breezy-layouts-has-field-prefix and .breezy-layouts-has-field-suffix class.
    if (!empty($element['#field_prefix'])) {
      $element[$attributes_property]['class'][] = 'breezy-layouts-has-field-prefix';
    }
    if (!empty($element['#field_suffix'])) {
      $element[$attributes_property]['class'][] = 'breezy-layouts-has-field-suffix';
    }


  }

  /**
   * {@inheritdoc}
   */
  public function finalize(array &$element) {
    // TODO: Implement finalize() method.
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue($element) {

  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $element = $form_state->get('element');
    if (is_null($element)) {
      throw new \Exception('Element must be defined in the $form_state.');
    }
    // The CSS property.
    $property = $form_state->get('property');

    // Element properties.
    $default_properties = $this->getDefaultProperties();

    $element_properties = $form_state->get('element') + $default_properties;

    // Set default and element properties.
    // Note: Storing this information in the variant's state allows modules to view
    // and alter this information using variant alteration hooks.
    $form_state->set('default_properties', $default_properties);
    $form_state->set('element_properties', $element_properties);

    $form = $this->form($form, $form_state);

    // Get default and element properties which can be altered by WebformElementHandlers.
    // @see \Drupal\webform\Plugin\WebformElement\WebformEntityReferenceTrait::form
    $element_properties = $form_state->get('element_properties');

    // Copy element properties to custom properties which will be determined
    // as the default values are set.
    $custom_properties = $element_properties;

    // Populate the form.
    $this->setConfigurationFormDefaultValueRecursive($form['element'], $custom_properties);

    if (isset($custom_properties['type'])) {
      $form['type'] = [
        '#type' => 'value',
        '#value' => $custom_properties['type'],
        '#parents' => ['properties', 'type'],
      ];
      unset($custom_properties['type']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $element = $form_state->get('element');
    $type = $element['#type'];

    $form['element'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Element settings'),
      '#weight' => -50,
    ];
    $form['element']['type'] = [
      '#type' => 'hidden',
      '#value' => $type,
    ];
    $form['element']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('title') ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationFormProperties(array $form, FormStateInterface $form_state) {
    $element_properties = $form_state->getValues();

    return $element_properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntities(EntityInterface $entity) {
    if ($entity instanceof BreezyLayoutsVariantInterface) {
      $this->variant = $entity;
    }
    else {
      throw new \Exception('Entity type must be a Breezy Layouts Variant.');
    }
    return $this;
  }

  /**
   * Reset variant entity.
   */
  public function resetEntities() {
    $this->variant = NULL;
  }

  /**
   * Set configuration webform default values recursively.
   *
   * @param array $form
   *   A varient render array.
   * @param array $element_properties
   *   The element's properties without hash prefix. Any property that is found
   *   in the variant will be populated and unset from
   *   $element_properties array.
   *
   * @return bool
   *   TRUE is the variant has any inputs.
   */
  protected function setConfigurationFormDefaultValueRecursive(array &$form, array &$element_properties) {
    $logger = \Drupal::logger('setConfigurationFormDefaultValueRecursive');
    $has_input = FALSE;

    foreach ($form as $property_name => &$property_element) {
      //$logger->notice('$property_name (before): ' . $property_name . '; $property_element: <pre>' . print_r($property_element, TRUE) . '</pre>');
      if (BreezyLayoutsElementHelper::property($property_name)) {
        continue;
      }
      $logger->alert('$property_name (after): ' . $property_name . '; $property_element: <pre>' . print_r($property_element, TRUE) . '</pre>');

      $is_input = $this->elementManager->getElementInstance($property_element)->isInput($property_element);
      if ($is_input) {
        $logger->notice('$is_input: TRUE - $property_name: ' . $property_name . '; $element_properties: <pre>' . print_r($element_properties, TRUE) . '</pre>');
        if (array_key_exists($property_name, $element_properties)) {
          // If this property exists, then set its default value.
          $this->setConfigurationFormDefaultValue($form, $element_properties, $property_element, $property_name);;
          $has_input = TRUE;
        }
      }
      else {
        // Recurse down this container and see if it's children have inputs.
        $logger->notice('!$is_input $property_name: ' . $property_name . ' - $property_element: <pre>' . print_r($property_element, TRUE) . '</pre>');
        $container_has_input = $this->setConfigurationFormDefaultValueRecursive($property_element, $element_properties);
        if ($container_has_input) {
          $has_input = TRUE;
        }
      }
      /**/
    }

    return $has_input;
  }

  /**
   * Set an element's configuration element default value.
   *
   * @param array $form
   *   An element's configuration form.
   * @param array $element_properties
   *   The element's properties without hash prefix.
   * @param array $property_element
   *   The form input used to set an element's property.
   * @param string $property_name
   *   THe property's name.
   */
  protected function setConfigurationFormDefaultValue(array &$form, array &$element_properties, array &$property_element, $property_name) {
    $logger = \Drupal::logger('setConfigurationFormDefaultValue');
    $default_value = '';
    if (isset($element_properties['element'][$property_name])) {
      $default_value = $element_properties['element'][$property_name];
    }
    $type = $property_element['#type'] ?? NULL;
    $logger->notice('$type: ' . $type . '; $default_value: <pre>' . print_r($default_value, TRUE) . '</pre>');

    switch ($type) {
      case 'radios':
      case 'select':
        if (isset($default_value['options'])) {
          $default_value = $default_value['options'];
        }
        if (!is_array($default_value) && isset($property_element['#options'])) {
          $flattened_options = OptGroup::flattenOptions($property_element['#options']);
          if (!isset($flattened_options[$default_value])) {
            $default_value = NULL;
          }
        }

        $property_element['#default_value'] = $default_value;

        break;

      default:
        // Convert default_value array into a comma delimited list.
        // This is applicable to elements that support #multiple #options.
        // @todo Check if element is composite.
        if (is_array($default_value) && $property_name === 'default_value') {
          $property_element['#default_value'] = implode(', ', $default_value);
        }
        elseif (is_bool($default_value) && $property_name === 'default_value') {
          $property_element['#default_value'] = $default_value ? 1 : 0;
        }
        elseif (is_null($default_value) && $property_name === 'default_value') {
          $property_element['#default_value'] = (string) $default_value;
        }
        else {
          $property_element['#default_value'] = $default_value;
        }
        break;
    }
    $property_element['#parents'] = ['properties', 'element', $property_name];
    unset($element_properties[$property_name]);
  }

}
