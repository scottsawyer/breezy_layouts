<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\breezy_layouts\Form\BreezyLayoutsDeleteFormBase;
use Drupal\breezy_layouts\Service\BreezyLayoutsElementPluginManagerInterface;
use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\breezy_layouts\Utility\BreezyLayoutsElementHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting elements.
 */
class BreezyLayoutsElementDeleteForm extends BreezyLayoutsDeleteFormBase {

  /**
   * The BreezyLayoutsElementPluginManager.
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
   * An element.
   *
   * @var \Drupal\breezy_layouts\Plugin\BreezyLayouts\Element\BreezyLayoutsElementInterface
   */
  protected $breezyLayoutsElement;

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
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->elementManager = $container->get('plugin.manager.breezy_layouts.element');
    return $instance;
  }

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
    $element_plugin = $this->getElementPlugin();
    return $this->t('Delete the %element element from the %variant', [
      '%element' => $this->getElementTitle(),
      '%variant' => $this->variant->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    $element = $this->element;
    return [
      'title' => [
        '#markup' => $this->t('This will delete %element.', [
          '%element' => $element['element']['title'] ?? 'this element',
        ]),
      ],
    ];
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
  public function buildForm(array $form, FormStateInterface $form_state, BreezyLayoutsVariantInterface $breezy_layouts_variant = NULL, $type = NULL, $key = NULL, $parent_key = NULL) {
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
    $form = $this->buildDialogConfirmForm($form, $form_state);
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->variant->deleteElement($this->key, $this->parentKey);
    $this->variant->save();

    $query = [];
    $form_state->setRedirectUrl($this->variant->toUrl('edit-form', ['query' => $query]));
  }

  /**
   * Return the element plugin associated with this form.
   *
   * @return \Drupal\breezy_layouts\Plugin\BreezyLayouts\Element\BreezyLayoutsElementInterface
   *   An element.
   */
  protected function getElementPlugin() {
    return $this->elementManager->getElementInstance($this->element);
  }

  /**
   * Get the element title from the element.
   *
   * @return string
   *   The element title.
   */
  protected function getElementTitle() {
    $element = $this->getElementPlugin();
    return BreezyLayoutsElementHelper::getElementTitle($element);
  }

}
