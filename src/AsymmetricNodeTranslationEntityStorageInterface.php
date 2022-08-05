<?php

namespace Drupal\asymmetric_translations;

use Drupal\asymmetric_translations\Entity\AsymmetricNodeTranslation;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\node\Entity\Node;

/**
 * Provides an interface for Asymmetric Node Translation storage.
 */
interface AsymmetricNodeTranslationEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Find translation entities by node
   *
   * @param Node $node
   *
   * @return array
   */
  public function getTranslationEntitiesByNode(Node $node);

}
