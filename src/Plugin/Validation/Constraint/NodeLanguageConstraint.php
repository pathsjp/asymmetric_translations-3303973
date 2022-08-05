<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\Core\Language\Language;
use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;

/**
 * Prevent a selected node to have a different language than the language field it was selected for.
 *
 * @Constraint(
 *   id = "AsymmetricTranslationNodeLanguage",
 *   label = @Translation("Prevent an asymmetric translation to have a selected node have a different language than the language field it was selected for.", context = "Validation"),
 *   type = "entity"
 * )
 */
class NodeLanguageConstraint extends Constraint {

  /**
   * Message shown when trying to select a node of the wrong language.
   *
   * @var string
   */
  protected $message = 'Node "%s" (%d) has language %s and thus cannot be selected as a translation for language %s.';

  public function generateMessage(Node $node, Language $target_language) {
    return sprintf(
      $this->message,
      $node->getTitle(),
      $node->id(),
      $node->language()->getName(),
      $target_language->getName()
    );
  }

  /**
   * {@inheritdoc}
   */
}
