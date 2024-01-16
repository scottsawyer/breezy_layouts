<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariant;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an abstract element form base.
 */
abstract class BreezyLayoutsElementFormBase extends FormBase {

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
  protected $tailwindClassService;

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
    $instance->tailwindClassService = $container->get('breezy_layouts.tailwind_classes');
    $instance->variant = BreezyLayoutsVariant::create(['id' => '_variant_temp_form']);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $variant = NULL) {
    $form['#prefix'] = '<div id="breezy-layouts-ui-element-ajax-wrapper';
    $form['#suffix'] = '</div>';

    // @todo: Check if we're in a modal.
    return $form;
  }

  /**
   * Never trigger validation.
   */
  public function noValidate(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
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
   * Build element type row.
   *
   * @param \Drupal\breezy_layouts\Plugin\breezy_layouts\Element\BreezyLayoutsElementInterface $element
   *   A Breezy Layout Element plugin.
   * @param \Drupal\Core\Url $url
   *   A url.
   * @param string $label
   *   Operation label.
   *
   * @return array
   *   A renderable array containing the element type row.
   */
  protected function buildRow(BreezyLayoutsElementInterface $element, Url $url, $label) {
    $row = [];

    return $row;
  }

}
