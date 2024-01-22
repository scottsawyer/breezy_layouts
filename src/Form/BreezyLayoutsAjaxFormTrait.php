<?php

namespace Drupal\breezy_layouts\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\AnnounceCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;

/**
 * Trait form ajax support.
 */
trait BreezyLayoutsAjaxFormTrait {

  /**
   * {@inheritdoc}
   */
  protected function isAjax() {
    return $this->isDialog();
  }

  /**
   * Get default ajax callback settings.
   *
   * @return array
   *   An associative array containing default ajax callback settings.
   */
  protected function getDefaultAjaxSettings() {
    return [
      'disable-refocus' => TRUE,
      'effect' => 'fade',
      'speed' => 1000,
      'progress' => [
        'type' => 'throbber',
        'message' => '',
      ],
    ];
  }

  /**
   * Is the current request for an Ajax modal/dialog.
   *
   * @return bool
   *   TRUE if the current request is for an Ajax modal/dialog.
   */
  protected function isDialog() {
    $wrapper_format = $this->getRequest()
      ->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    return (in_array($wrapper_format, [
      'drupal_ajax',
      'drupal_modal',
      'drupal_dialog',
      'drupal_dialog.off_canvas',
    ])) ? TRUE : FALSE;
  }

  /**
   * Get the form's Ajax wrapper id.
   *
   * @return string
   *   The form's Ajax wrapper id.
   */
  protected function getWrapperId() {
    $form_id = (method_exists($this, 'getBaseFormId') ? $this->getBaseFormId() : $this->getFormId());
    return Html::getId($form_id . '-ajax');
  }


  /**
   * Add Ajax support to a form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $settings
   *   Ajax settings.
   *
   * @return array
   *   The form with Ajax callbacks.
   */
  protected function buildAjaxForm(array &$form, FormStateInterface $form_state, array $settings = []) {
    if (!$this->isAjax()) {
      return $form;
    }

    // Apply default settings.
    $settings += $this->getDefaultAjaxSettings();

    // Add Ajax callback to all submit buttons.
    foreach (Element::children($form) as $element_key) {
      if (!BreezyLayoutsElementHelper::isType($form[$element_key], 'actions')) {
        continue;
      }

      $actions = &$form[$element_key];
      foreach (Element::children($actions) as $action_key) {
        if (BreezyLayoutsElementHelper::isType($actions[$action_key], 'submit')) {
          $actions[$action_key]['#ajax'] = [
              'callback' => '::submitAjaxForm',
              'event' => 'click',
            ] + $settings;
        }
      }
    }

    // Add Ajax wrapper with wrapper content bookmark around the form.
    // @see Drupal.AjaxCommands.prototype.webformScrollTop
    $wrapper_id = $this->getWrapperId();
    $wrapper_attributes = [];
    $wrapper_attributes['id'] = $wrapper_id;
    $wrapper_attributes['class'] = ['webform-ajax-form-wrapper'];
    if (isset($settings['effect'])) {
      $wrapper_attributes['data-effect'] = $settings['effect'];
    }
    if (isset($settings['progress']['type'])) {
      $wrapper_attributes['data-progress-type'] = $settings['progress']['type'];
    }
    $wrapper_attributes = new Attribute($wrapper_attributes);

    $form['#form_wrapper_id'] = $wrapper_id;

    $form += ['#prefix' => '', '#suffix' => ''];
    $form['#prefix'] .= '<span id="' . $wrapper_id . '-content"></span>';
    $form['#prefix'] .= '<div' . $wrapper_attributes . '>';
    $form['#suffix'] = '</div>' . $form['#suffix'];

    // Add Ajax library which contains 'Scroll to top' Ajax command and
    // Ajax callback for confirmation back to link.
    //$form['#attached']['library'][] = 'webform/webform.ajax';

    // Add validate Ajax form.
    $form['#validate'][] = '::validateAjaxForm';

    return $form;
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
    //$response->addCommand(new HtmlCommand('#breezy-layouts-ui-element-ajax-wrapper', $form));
    $response->addCommand(new HtmlCommand('#' . $this->getWrapperId(), $form));
    return $response;
  }


  /**
   * Close dialog.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool|\Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages.
   */
  public function closeDialog(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand());
    return $response;
  }


}
