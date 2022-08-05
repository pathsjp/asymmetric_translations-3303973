<?php

namespace Drupal\asymmetric_translations;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides a view controller for a asymmetric node translation entity type.
 */
class AsymmetricNodeTranslationViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);
    // The asymmetric node translation has no entity template itself.
    unset($build['#theme']);
    return $build;
  }

}
