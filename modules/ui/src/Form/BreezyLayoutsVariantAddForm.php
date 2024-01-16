<?php

namespace Drupal\breezy_layouts_ui\Form;

use Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for creating BreezyLayoutsVariant entities.
 */
class BreezyLayoutsVariantAddForm extends FormBase {

  /**
   * Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface
   * definition
   *
   * @var \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface
   */
  protected $variantPluginManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new BreezyLayoutsVariantAddForm object.
   *
   * @param \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager
   *    The variant plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->variantPluginManager = $variant_plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\breezy_layouts\Service\BreezyLayoutsVariantPluginManagerInterface $variant_plugin_manager */
    $variant_plugin_manager = $container->get('plugin.manager.breezy_layouts.variant');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static($variant_plugin_manager, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'breezy_layouts_variant_add_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => '\Drupal\breezy_layouts\Entity\BreezyLayoutsVariant::load',
      ],
    ];
    $form['plugin_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose a layout'),
      '#required' => TRUE,
      '#empty_option' => $this->t('- Select -'),
      '#options' => $this->getVariantPluginOptions(),
      '#default_value' => $form_state->getValue('plugin_id') ?? '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $variant_data = [
      'label' => $values['label'],
      'id' => $values['id'],
      'plugin_id' => $values['plugin_id'],
    ];
    $variant = $this->entityTypeManager->getStorage('breezy_layouts_variant')->create($variant_data);
    $variant->save();
    $form_state->setRedirect('entity.breezy_layouts_variant.edit_form', ['breezy_layouts_variant' => $variant->id()]);

  }

  /**
   * Get variant plugin options.
   *
   * @return array
   *   An array of variant plugin options.
   */
  protected function getVariantPluginOptions() {
    $variant_plugin_options = [];
    $variant_plugins = $this->variantPluginManager->getValidDefinitions();
    foreach ($variant_plugins as $id => $definition) {
      $variant_plugin_options[$id] = $definition['label'];
    }
    return $variant_plugin_options;
  }

}
