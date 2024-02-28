<?php

namespace Drupal\breezy_layouts\Plugin\BreezyLayouts\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;

/**
 * Provides a base class for 'options' element.
 */
abstract class OptionsBase extends BreezyLayoutsElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [
      'options' => [],
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element) {
    if (!isset($element['#default_value'])) {
      return;
    }

    // Compensate for #default_value not being an array, for elements that
    // allow for multiple #options to be selected/checked.
    if ($this->hasMultipleValues($element) && !is_array($element['#default_value'])) {
      $element['#default_value'] = [$element['#default_value']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTableColumn(array $element) {
    $key = $element['#breezy_layouts_key'];
    $columns = parent::getTableColumn($element);
    $columns['element__' . $key]['sort'] = !$this->hasMultipleValues($element);
    return $columns;
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $options_wrapper_id = $this->getOptionsWrapperId();

    $form['element']['options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Element options'),
      '#open' => TRUE,
      '#attributes' => [
        'id' => $options_wrapper_id,
      ],
    ];

    $form['element']['options']['options'] = [
      '#type' => 'breezy_layouts_options',
      '#title' => $this->t('Options'),
      '#required' => TRUE,
      '#property' => $form_state->get('property'),
      '#parents' => [$form['#parents'], 'element', 'options'],
    ];

    return $form;

  }

  /**
   * Build options table.
   *
   * @param array $options
   *   The options array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The options table.
   */
  public function buildOptionsTable(array $options, FormStateInterface $form_state) {
    $rows = [];

    $num_lines = $form_state->get('num_lines');
    $removed_lines = $form_state->get('removed_lines');
    $delta = count($options);
    for ($i = 0; $i < $num_lines; $i++) {
      if (in_array($i, $removed_lines)) {
        continue;
      }

      if (!empty($options)) {
        $rows[$i] = $this->buildOptionsTableRow($options[$i], $i, $delta, $form_state);
      }
      else {
        $rows[$i] = $this->buildOptionsTableRow([], $i, $delta, $form_state);
      }
    }

    $table = [
      '#type' => 'table',
      '#sort' => TRUE,
      '#header' => $this->getOptionsTableHeader(),
      '#attributes' => [
        'class' => ['breezy-layouts-options-form', 'num-lines-' . $num_lines],
      ],
      '#num_lines' => $num_lines,
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'row-parent-key',
          'source' => 'row-key',
          'hidden' => TRUE,
          'limit' => FALSE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'row-weight',
        ],
      ],
    ] + $rows;

    return $table;
  }

  /**
   * Build options table header.
   *
   * @return array
   *   An array of table header items.
   */
  protected function getOptionsTableHeader() {
    $header = [];
    $header['value'] = $this->t('Option value');
    $header['label'] = $this->t('Option label');
    $header['weight'] = $this->t('Weight');
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * Build options table row.
   *
   * @param array $item
   *   An array of items to build a row.
   * @param int $i
   *   The row number.
   * @param int $delta
   *   The row delta.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   A row representing an option for the table.
   */
  protected function buildOptionsTableRow(array $item, int $i, int $delta, FormStateInterface $form_state) {
    $row = [];

    $row_class = ['draggable'];
    $row['#attributes']['class'] = $row_class;
    $row['value'] = [
      '#type' => 'breezy_layouts_property_select',
      '#property' => $form_state->get('property'),
    ];
    /*
    $row['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Option value'),
      '#required' => TRUE,
      '#options' => $this->tailwindClasses->getClassOptions($form_state->get('property')),
    ];
    /**/
    $row['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Option label'),
      '#default_value' => '',

    ];
    $row['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for option'),
      '#title_display' => 'invisible',
      '#default_value' => '',
      '#wrapper_attributes' => ['class' => ['breezy-layouts-tabledrag-hide']],
      '#attributes' => ['class' => ['row-weight']],
      '#delta' => $delta,
    ];

    $row['operations'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#name' => '_remove_' . $i,
      '#limit_validation_errors' => [],
      '#submit' => ['::removeOptionSubmit'],
      '#ajax' => [
        'callback' => '::removeOptionCallback',
        'wrapper' => $this->getOptionsWrapperId(),
      ],
    ];

    return $row;
  }

  /**
   * Options wrapper id.
   *
   * @return string
   *   The options wrapper id.
   */
  protected function getOptionsWrapperId() {
    return 'breezy-layouts-ajax-options-wrapper';
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    $is_wrapper_fieldset = in_array($element['#type'], ['checkboxes', 'radios']);
    if ($is_wrapper_fieldset) {
      // Issue #2396145: Option #description_display for webform element fieldset
      // is not changing anything.
      // @see core/modules/system/templates/fieldset.html.twig
      $is_description_display = (isset($element['#description_display'])) ? TRUE : FALSE;
      $has_description = (!empty($element['#description'])) ? TRUE : FALSE;
      if ($is_description_display && $has_description) {
        $description = BreezyLayoutsElementHelper::convertToString($element['#description']);
        switch ($element['#description_display']) {
          case 'before':
            $element += ['#field_prefix' => ''];
            $element['#field_prefix'] = '<div class="description">' . $description . '</div>' . $element['#field_prefix'];
            unset($element['#description']);
            unset($element['#description_display']);
            break;

          case 'invisible':
            $element += ['#field_suffix' => ''];
            $element['#field_suffix'] .= '<div class="description visually-hidden">' . $description . '</div>';
            unset($element['#description']);
            unset($element['#description_display']);
            break;
        }
      }
    }

    parent::prepare($element, $webform_submission);

    // Options description display must be set to trigger the description display.
    if ($this->hasProperty('options_description_display') && empty($element['#options_description_display'])) {
      $element['#options_description_display'] = $this->getDefaultProperty('options_description_display');
    }

    // Options display must be set to trigger the options display.
    if ($this->hasProperty('options_display') && empty($element['#options_display'])) {
      $element['#options_display'] = $this->getDefaultProperty('options_display');
    }

    // Make sure submitted value is not lost if the element's #options were
    // altered after the submission was completed.
    // This only applies to the main webforom element with a #webform_key
    // and not a webform composite's sub elements.
    $is_completed = $webform_submission && $webform_submission->isCompleted();
    $has_default_value = (isset($element['#default_value']) && $element['#default_value'] !== '' && $element['#default_value'] !== NULL);
    if ($is_completed && $has_default_value && !$this->isOptionsOther() && isset($element['#webform_key'])) {
      if ($element['#default_value'] === $webform_submission->getElementData($element['#webform_key'])) {
        $options = OptGroup::flattenOptions($element['#options']);
        $default_values = (array) $element['#default_value'];
        foreach ($default_values as $default_value) {
          if (!isset($options[$default_value])) {
            $element['#options'][$default_value] = $default_value;
          }
        }
      }
    }

    // If the element is #required and the #default_value is an empty string
    // we need to unset the #default_value to prevent the below error.
    // 'An illegal choice has been detected'.
    if (!empty($element['#required']) && isset($element['#default_value']) && $element['#default_value'] === '') {
      unset($element['#default_value']);
    }

    // Process custom options properties.
    if ($this->hasProperty('options__properties')) {
      // Unset #options__properties that are not array to prevent errors.
      if (isset($element['#options__properties'])
        && !is_array($element['#options__properties'])) {
        unset($element['#options__properties']);
      }
      $this->setElementDefaultCallback($element, 'process');
      $element['#process'][] = [get_class($this), 'processOptionsProperties'];
    }
  }


}
