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
   *
   * Validate that the language of the selected node is equal to the language of the assymmetric node translation currently being set.
   */
  public function validate($ant, Constraint $constraint) {
    if (!isset($ant)) {
      return;
    }

    // Check each available language
    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      // If the ANT doesn't have a translation in a specific language, we don't have to check it
      if (!$ant->hasTranslation($language->getId())) {
        continue;
      }

      // Load the translation of the ANT
      $ant_translation = $ant->getTranslation($language->getId());

      // Fetch the linked NID and load the Node
      if (!$nid = $ant_translation->node->target_id) {
        continue;
      }
      if (!$node = Node::load($nid)) {
        continue;
      }

      // Validate that the language of the linked Node is equal to the language of the ANT translation
      if ($node->language()->getId() !== $ant_translation->language()->getId()) {
        $this->context->addViolation($constraint->generateMessage($node, $ant_translation->language()));
      }
    }
  }

}
