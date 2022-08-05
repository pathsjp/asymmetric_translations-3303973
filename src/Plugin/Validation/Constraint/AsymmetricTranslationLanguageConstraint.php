<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\Core\Language\Language;
use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;

/**
 * Prevent a selected node to have a different language than the language field it was selected for.
 *
 * @Constraint(
 *   id = "NodeAsymmetricTranslationLanguage",
 *   label = @Translation("Prevent a selected node to have a different language than the language field it was selected for.", context = "Validation"),
 *   type = "entity"
 * )
 */
class AsymmetricTranslationLanguageConstraint extends Constraint {

  /**
   * Message shown when trying to change the language of a node that has translations
   *
   * @var string
   */
  protected $message = 'The language of node "%s" (%d) cannot be changed because translations have been assigned to it.';

  public function generateMessage(Node $node) {
    return sprintf(
      $this->message,
      $node->getTitle(),
      $node->id()
    );
  }

  /**
   * {@inheritdoc}
   */
}
