<?php

namespace Drupal\breezy_layouts\Element;

use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Template\Attribute;

/**
 * Provides an element to assist in creating multiple elements.
 *
 * @FormElement("breezy_layouts_multiple")
 */
class BreezyLayoutsMultiple extends FormElement {

  /**
   * Value indicating a element accepts an unlimited number of values.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#access' => TRUE,
      '#key' => NULL,
      '#header' => NULL,
      '#header_label' => '',
      '#element' => [
        '#type' => 'breezy_layouts_property_select',
        '#title' => $this->t('Value'),
        '#title_display' => 'invisible',
        '#placeholder' => $this->t('Enter value…'),
      ],
      '#cardinality' => FALSE,
      '#min_items' => NULL,
      '#item_label' => $this->t('item'),
      '#no_items_message' => $this->t('No items entered. Please add items below.'),
      '#empty_items' => 1,
      '#add_more' => TRUE,
      '#add_more_items' => 1,
      '#add_more_button_label' => $this->t('Add'),
      '#add_more_input' => TRUE,
      '#add_more_input_label' => $this->t('more items'),
      '#sorting' => TRUE,
      '#operations' => TRUE,
      '#add' => TRUE,
      '#ajax_attributes' => [],
      '#table_attributes' => [],
      '#table_wrapper_attributes' => [],
      '#remove' => TRUE,
      '#process' => [
        [$class, 'processWebformMultiple'],
      ],
      '#theme_wrappers' => ['form_element'],
      // Add '#markup' property to add an 'id' attribute to the form element.
      // @see template_preprocess_form_element()
      '#markup' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      if (!isset($element['#default_value'])) {
        return [];
      }
      elseif (!is_array($element['#default_value'])) {
        return [$element['#default_value']];
      }
      else {
        return $element['#default_value'];
      }
    }
    elseif (is_array($input) && isset($input['items'])) {
      return static::convertValuesToItems($element, $input['items']);
    }
    else {
      return [];
    }
  }

  /**
   * Process items and build multiple elements widget.
   */
  public static function processBreezyLayoutsMultiple(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;

    // Set min items based on when the element is required.
    if (!isset($element['#min_items']) || $element['#min_items'] === '') {
      $element['#min_items'] = (empty($element['#required'])) ? 0 : 1;
    }

    // Make sure min items does not exceed cardinality.
    if (!empty($element['#cardinality']) && $element['#min_items'] > $element['#cardinality']) {
      $element['#min_items'] = $element['#cardinality'];
    }

    // Make sure empty items does not exceed cardinality.
    if (!empty($element['#cardinality']) && $element['#empty_items'] > $element['#cardinality']) {
      $element['#empty_items'] = $element['#cardinality'];
    }

    // If the number of default values exceeds the min items and has required
    // sub-elements, set empty items to 0.
    if (isset($element['#default_value'])
      && is_array($element['#default_value'])
      && count($element['#default_value']) >= $element['#min_items']
      && (static::hasRequireElement($element['#element']))) {
      $element['#empty_items'] = 0;
    }

    // Add validate callback that extracts the array of items.
    $element += ['#element_validate' => []];
    array_unshift($element['#element_validate'], [get_called_class(), 'validateWebformMultiple']);

    // Get unique key used to store the current number of items.
    $number_of_items_storage_key = static::getStorageKey($element, 'number_of_items');

    // Store the number of items which is the number of
    // #default_values + number of empty_items.
    if ($form_state->get($number_of_items_storage_key) === NULL) {
      if (empty($element['#default_value']) || !is_array($element['#default_value'])) {
        $number_of_default_values = 0;
      }
      else {
        $number_of_default_values = count($element['#default_value']);
      }
      $number_of_empty_items = (int) $element['#empty_items'];
      $number_of_items = $number_of_default_values + $number_of_empty_items;

      // Make sure number of items is greated than min items.
      $min_items = (int) $element['#min_items'];
      $number_of_items = ($number_of_items < $min_items) ? $min_items : $number_of_items;

      // Make sure number of (default) items does not exceed cardinality.
      if (!empty($element['#cardinality']) && $number_of_items > $element['#cardinality']) {
        $number_of_items = $element['#cardinality'];
      }

      $form_state->set($number_of_items_storage_key, $number_of_items);
    }

    $number_of_items = $form_state->get($number_of_items_storage_key);

    $table_id = implode('_', $element['#parents']) . '_table';

    // Disable add operation when #cardinality is met
    // and make sure to limit the number of items.
    if (!empty($element['#cardinality']) && $number_of_items >= $element['#cardinality']) {
      $element['#add'] = FALSE;
      $number_of_items = $element['#cardinality'];
      $form_state->set($number_of_items_storage_key, $number_of_items);
    }

    // Add wrapper to the element.
    $ajax_attributes = $element['#ajax_attributes'];
    $ajax_attributes['id'] = $table_id;
    $element += ['#prefix' => '', '#suffix' => ''];
    $element['#ajax_prefix'] = '<div' . new Attribute($ajax_attributes) . '>';
    $element['#ajax_suffix'] = '</div>';
    $element['#prefix'] = $element['#prefix'] . $element['#ajax_prefix'];
    $element['#suffix'] = $element['#ajax_suffix'] . $element['#suffix'];

    // DEBUG:
    // Disable Ajax callback by commenting out the below callback and wrapper.
    $ajax_settings = [
      'callback' => [get_called_class(), 'ajaxCallback'],
      'wrapper' => $table_id,
      'progress' => ['type' => 'none'],
    ];

    // Initialize, prepare, and finalize sub-elements.
    static::initializeElement($element, $form_state, $complete_form);

    // Build (single) element header.
    $header = static::buildElementHeader($element);

    // Build (single) element rows.
    $row_index = 0;
    $weight = 0;
    $rows = [];

    if (!$form_state->isProcessingInput() && isset($element['#default_value']) && is_array($element['#default_value'])) {
      $default_values = $element['#default_value'];
    }
    elseif ($form_state->isProcessingInput() && isset($element['#value']) && is_array($element['#value'])) {
      $default_values = $element['#value'];
    }
    else {
      $default_values = [];
    }

    // When adding/removing elements we don't need to set any default values.
    $action_key = static::getStorageKey($element, 'action');
    if ($form_state->get($action_key)) {
      $form_state->set($action_key, FALSE);
      $default_values = [];
    }

    foreach ($default_values as $key => $default_value) {
      // If #key is defined make sure to set default value's key item.
      if (!empty($element['#key']) && !isset($default_value[$element['#key']])) {
        $default_value[$element['#key']] = $key;
      }
      $rows[$row_index] = static::buildElementRow($table_id, $row_index, $element, $default_value, $weight++, $ajax_settings);
      $row_index++;
    }

    while ($row_index < $number_of_items) {
      $rows[$row_index] = static::buildElementRow($table_id, $row_index, $element, NULL, $weight++, $ajax_settings);
      $row_index++;
    }

    // Build table.
    $table_wrapper_attributes = $element['#table_wrapper_attributes'];
    $table_wrapper_attributes['class'][] = 'breezy-layouts-multiple-table';
    if (count($element['#element']) > 1) {
      $table_wrapper_attributes['class'][] = 'breezy-layouts-multiple-table-responsive';
    }
    $element['items'] = [
        '#prefix' => '<div' . new Attribute($table_wrapper_attributes) . '>',
        '#suffix' => '</div>',
      ] + $rows;

    // Display table if there are any rows.
    if ($rows) {
      $element['items'] += [
          '#type' => 'table',
          '#header' => $header,
          '#attributes' => $element['#table_attributes'],
        ] + $rows;

      // Add sorting to table.
      if ($element['#sorting']) {
        $element['items']['#tabledrag'] = [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'breezy-layouts-multiple-sort-weight',
          ],
        ];
      }
    }
    elseif (!empty($element['#no_items_message'])) {
      // @todo Add message element.
      /*
      $element['items'] += [
        '#type' => 'webform_message',
        '#message_message' => $element['#no_items_message'],
        '#message_type' => 'info',
        '#attributes' => ['class' => ['breezy-layouts-multiple-table--no-items-message']],
      ];
      /**/
    }

    // Build add more actions.
    if ($element['#add_more'] && (empty($element['#cardinality']) || ($number_of_items < $element['#cardinality']))) {
      $element['add'] = [
        '#prefix' => '<div class="breezy-layouts-multiple-add js-breezy-layouts-multiple-add container-inline">',
        '#suffix' => '</div>',
      ];
      $element['add']['submit'] = [
        '#type' => 'submit',
        '#value' => $element['#add_more_button_label'],
        '#limit_validation_errors' => [],
        '#submit' => [[get_called_class(), 'addItemsSubmit']],
        '#ajax' => $ajax_settings,
        '#name' => $table_id . '_add',
      ];
      $max = ($element['#cardinality']) ? $element['#cardinality'] - $number_of_items : 100;
      $element['add']['more_items'] = [
        '#type' => 'number',
        '#title' => $element['#add_more_button_label'] . ' ' . $element['#add_more_input_label'],
        '#title_display' => 'invisible',
        '#min' => 1,
        '#max' => $max,
        '#default_value' => $element['#add_more_items'],
        '#field_suffix' => $element['#add_more_input_label'],
        '#error_no_message' => TRUE,
        '#access' => $element['#add_more_input'],
      ];
    }

    //$element['#attached']['library'][] = 'webform/webform.element.multiple';

    return $element;

  }

  /**
   * Initialize element.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   An associative array containing the structure of the form.
   */
  protected static function initializeElement(array &$element, FormStateInterface $form_state, array &$complete_form) {
    // Track element child keys.
    $element['#child_keys'] = Element::children($element['#element']);

    if (!$element['#child_keys']) {
      // Apply multiple element's required/optional #states to the
      // individual element.
      if (isset($element['#_webform_states'])) {
        $element['#element'] += ['#states' => []];
        $element['#element']['#states'] = array_intersect_key(
          WebformElementHelper::getStates($element),
          ['required' => 'required', 'optional' => 'optional']
        );
      }
    }
    else {
      // Initialize, prepare, and finalize composite sub-elements.
      // Get composite element required/options states from visible/hidden states.
      $required_states = WebformElementHelper::getRequiredFromVisibleStates($element);
      static::initializeElementRecursive($element, $form_state, $complete_form, $element['#element'], $required_states);
    }
  }

  /**
   * Initialize, prepare, and finalize composite sub-elements recursively.
   *
   * @param array $element
   *   The main element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   An associative array containing the structure of the form.
   * @param array $sub_elements
   *   The sub element.
   * @param array $required_states
   *   An associative array of required states from the main element's
   *   visible/hidden states.
   */
  protected static function initializeElementRecursive(array $element, FormStateInterface $form_state, array &$complete_form, array &$sub_elements, array $required_states) {
    $child_keys = Element::children($sub_elements);

    // Exit immediate if the sub elements has no children.
    if (!$child_keys) {
      return;
    }

    // Determine if the sub elements are the main element for each table cell.
    $is_root = ($element['#element'] === $sub_elements) ? TRUE : FALSE;

    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.breezy_layouts.element');
    foreach ($child_keys as $child_key) {
      $sub_element = &$sub_elements[$child_key];

      $element_plugin = $element_manager->getElementInstance($sub_element);

      // If the element's #access is FALSE, apply it to all sub elements.
      if (isset($element['#access']) && $element['#access'] === FALSE) {
        $sub_element['#access'] = FALSE;
      }

      // If #header and root input then hide the sub element's #title.
      if ($element['#header']
        && ($is_root && $element_plugin->isInput($sub_element))
        && !isset($sub_element['#title_display'])) {
        $sub_element['#title_display'] = 'invisible';
      }

      // Initialize the composite sub-element.
      $element_manager->initializeElement($sub_element);

      // Build the composite sub-element.
      $element_manager->buildElement($sub_element, $complete_form, $form_state);

      // Custom validate required sub-element because they can be hidden
      // via #access or #states.
      // @see \Drupal\webform\Element\WebformCompositeBase::validateWebformComposite
      if ($required_states && !empty($sub_element['#required'])) {
        unset($sub_element['#required']);
        $sub_element['#_required'] = TRUE;
        if (!isset($sub_element['#states'])) {
          $sub_element['#states'] = [];
        }
        $sub_element['#states'] += $required_states;
      }

      if (is_array($sub_element)) {
        static::initializeElementRecursive($element, $form_state, $complete_form, $sub_element, $required_states);
      }
    }
  }


  /**
   * Get unique key used to store the number of items for an element.
   *
   * @param array $element
   *   An element.
   * @param string $name
   *   The storage key's name.
   *
   * @return string
   *   A unique key used to store the number of items for an element.
   */
  public static function getStorageKey(array $element, $name) {
    return 'breezy_layouts_multiple__' . $element['#name'] . '__' . $name;
  }

  /**
   * Convert an array containing of values (elements or _item_ and weight) to an array of items.
   *
   * @param array $element
   *   The multiple element.
   * @param array $values
   *   An array containing of item and weight.
   *
   * @return array
   *   An array of items.
   *
   * @throws \Exception
   *   Throws unique key required validation error message as an exception.
   */
  public static function convertValuesToItems(array $element, array $values = []) {
    // Sort the item values.
    if ($element['#sorting']) {
      // @todo Add sorting.
      //uasort($values, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    }

    // Now build the associative array of items.
    $items = [];
    foreach ($values as $value) {
      $item = static::convertValueToItem($value);

      // Never add an empty item.
      if (static::isEmpty($item)) {
        continue;
      }

      // If #key is defined use it as the $items key.
      if (!empty($element['#key']) && isset($item[$element['#key']])) {
        $key_name = $element['#key'];
        $key_value = $item[$key_name];
        unset($item[$key_name]);
        $items[$key_value] = $item;
      }
      else {
        $items[] = $item;
      }
    }

    return $items;
  }

  /**
   * Convert value array containing (elements or _item_ and weight) to an item.
   *
   * @param array $value
   *   The multiple value array.
   *
   * @return array
   *   An item array.
   */
  public static function convertValueToItem(array $value) {
    if (isset($value['_item_'])) {
      return $value['_item_'];
    }
    else {
      // Get hidden (#access: FALSE) elements in the '_handle_' column and
      // add them to the $value.
      // @see \Drupal\webform\Element\WebformMultiple::buildElementRow
      if (isset($value['_hidden_']) && is_array($value['_hidden_'])) {
        $value += $value['_hidden_'];
      }
      unset($value['weight'], $value['_operations_'], $value['_hidden_']);
      return $value;
    }
  }

  /**
   * Check if array is empty.
   *
   * @param string|array $value
   *   An item.
   *
   * @return bool
   *   FALSE if item is an empty string or an empty array.
   */
  public static function isEmpty($value = NULL) {
    if (is_null($value)) {
      return TRUE;
    }
    elseif (is_string($value)) {
      return ($value === '') ? TRUE : FALSE;
    }
    elseif (is_array($value)) {
      return !array_filter($value, function ($item) {
        return !static::isEmpty($item);
      });
    }
    else {
      return FALSE;
    }
  }

  /**
   * Determine if any sub-element is required.
   *
   * @param array $element
   *   An element.
   *
   * @return bool
   *   TRUE if any sub-element is required.
   */
  protected static function hasRequireElement(array $element) {
    $required_properties = [
      '#required' => TRUE,
      '#_required' => TRUE,
    ];
    return BreezyLayoutsElementHelper::hasProperties($element, $required_properties);
  }
}
