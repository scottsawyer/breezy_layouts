<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\breezy_layouts\Form\BreezyLayoutsDeleteFormBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form for deleting elements.
 */
class BreezyLayoutsElementDeleteForm extends BreezyLayoutsDeleteFormBase {

  /**
   * The webform element manager.
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface
   */
  protected $elementManager;

  /**
   * The Breezy Layouts Variant containing the element to be deleted.
   *
   * @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface
   */
  protected $variant;

  /**
   * A webform element.
   *
   * @var \Drupal\webform\Plugin\WebformElementInterface
   */
  protected $webformElement;

  /**
   * The element key.
   *
   * @var string
   */
  protected $key;

  /**
   * The parent key.
   *
   * @var array
   */
  protected $parentKey;

  /**
   * The element.
   *
   * @var array
   */
  protected $element;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_ui_element_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete the element from the %variant', [
      '%variant' => $this->variant->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->variant->toUrl('edit-form');
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $key = NULL, $parent_key = NULL) {
    $this->variant = $breezy_layouts_variant;
    if (!$parent_key) {
      $parent_key = $this->getRequest()->query->get('parent');
    }
    $parent_array = Json::decode($parent_key);
    $this->parentKey = $parent_array;
    if (!$key) {
      $key = $this->getRequest()->query->get('key');
    }
    $this->key = $key;
    $this->element = $breezy_layouts_variant->getElementConfiguration($parent_array, $key);
    if ($this->element === NULL) {
      throw new NotFoundHttpException();
    }

    $form = parent::buildForm($form, $form_state);
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->variant->deleteElement($this->key, $this->parentKey);
    $this->variant->save();

    $query = [];
    $query = ['reload' => 'true'];
    $form_state->setRedirectUrl($this->variant->toUrl(['edit-form', ['query' => $query]]));
  }

}
