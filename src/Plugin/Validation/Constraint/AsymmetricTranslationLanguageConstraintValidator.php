<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NodeLanguage constraint.
 */
class AsymmetricTranslationLanguageConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /* @var Node $node */
    $node =& $entity;

    $storage = \Drupal::entityTypeManager()->getStorage('asymmetric_node_translation');

    if (!$ant = $storage->getTranslationEntityTranslationByNode($node)) {
      return;
    }

    if ($node->language()->getId() !== $ant->language()->getId()) {
      $this->context->addViolation($constraint->generateMessage($node));
    }
  }

}
