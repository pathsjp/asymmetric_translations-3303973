<?php

namespace Drupal\asymmetric_translations;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the asymmetric node translation entity type.
 */
class AsymmetricNodeTranslationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view asymmetric node translation');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit asymmetric node translation', 'administer asymmetric node translation'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete asymmetric node translation', 'administer asymmetric node translation'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create asymmetric node translation', 'administer asymmetric node translation'], 'OR');
  }

}
