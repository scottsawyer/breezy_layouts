<?php

namespace Drupal\breezy_layouts\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a manager class for BreezyLayoutsVariants.
 */
class VariantManager implements VariantManagerInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new VariantManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantOptionsForLayout(string $layout) : array {
    $variant_options = [];
    /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariant[] $variants */
    $variants = $this->entityTypeManager->getStorage('breezy_layouts_variant')->loadByProperties(['layout' => $layout, 'status' => TRUE]);
    if ($variants) {
      foreach ($variants as $variant) {
        $variant_options[$variant->id()] = $variant->label();
      }
    }
    return $variant_options;
  }
}
