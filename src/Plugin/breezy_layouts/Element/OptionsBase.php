<?php

namespace Drupal\breezy_layouts\Plugin\breezy_layouts\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
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

    $options = $form_state->get('options');
    if (!$options) {
      $options = $form_state->getValue('options');
      if (!$options) {
        $options = [];
      }
    }

    $num_lines = $form_state->get('num_lines');
    if ($num_lines === NULL) {
      $num_lines = 4;
      if (count($options) > 3) {
        $num_lines = count($options);
      }
      $form_state->set('num_lines', $num_lines);
    }

    $removed_lines = $form_state->get('removed_lines');
    if ($removed_lines === NULL) {
      $removed_lines = [];
      $form_state->set('removed_lines', $removed_lines);
    }

    $form['element']['options']['options'] = $this->buildOptionsTable($options, $form_state);

    $form['element']['options']['add_option'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add option'),
      '#limit_validation_errors' => [],
      '#submit' => ['::addOptionSubmit'],
      '#ajax' => [
        'callback' => '::addOptionCallback',
        'wrapper' => $options_wrapper_id,
      ],
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

}
