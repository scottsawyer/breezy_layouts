<?php

namespace Drupal\breezy_layouts;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a list builder for BreezyLayoutsVariant entities.
 */
class BreezyLayoutsVariantListBuilder extends ConfigEntityListBuilder {

  /**
   * Builds header row.
   *
   * @return array
   *   An array of table header items.
   */
  public function buildHeader() {

    return [
      'label' => $this->t('Label'),
      'id' => $this->t('Machine name'),
      'layout' => $this->t('Layout'),
      'status' => $this->t('Enabled'),
    ] + parent::buildHeader();
  }

  /**
   * Builds a row.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   An array of row items.
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\breezy_layouts\Entity\BreezyLayoutsVariantInterface $entity */

    $row = [
      'label' => $entity->label(),
      'id' => $entity->id(),
      'layout' => $entity->getPluginId(),
      'status' => $entity->isEnabled() ? $this->t('Yes') : $this->t('No'),
    ];

    return $row + parent::buildRow($entity);
  }

}