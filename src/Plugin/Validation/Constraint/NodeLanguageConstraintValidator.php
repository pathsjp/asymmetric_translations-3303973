<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NodeLanguage constraint.
 */
class NodeLanguageConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

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
      if ($node->language()->getId() !== $translation->language()->getId()) {
        $this->context->addViolation($constraint->generateMessage($node, $translation->language()));
      }
    }
  }

}
