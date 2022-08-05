<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NodeLanguage constraint.
 */
class SingleNodeUsageConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    $storage = \Drupal::entityTypeManager()->getStorage('asymmetric_node_translation');
    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      if (!$entity->hasTranslation($language->getId())) {
        continue;
      }

      $translation = $entity->getTranslation($language->getId());

      if (!$nid = $translation->node->target_id) {
        continue;
      }
      if (!$node = Node::load($nid)) {
        continue;
      }

      // Validate
      foreach($ants = $storage->getTranslationEntitiesByNode($node) as $ant) {
        if ($ant->id() !== $entity->id()) {
          $this->context->addViolation($constraint->generateMessage($node));
        }
      }
    }
  }

}
