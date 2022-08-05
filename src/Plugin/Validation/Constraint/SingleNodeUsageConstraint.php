<?php

namespace Drupal\asymmetric_translations\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;

/**
 * Prevent a node to be referenced by more than one asymmetric translation.
 *
 * @Constraint(
 *   id = "AsymmetricTranslationSingleNodeUsage",
 *   label = @Translation("Prevent a node to be referenced by more than one asymmetric translation.", context = "Validation"),
 *   type = "entity"
 * )
 */
class SingleNodeUsageConstraint extends Constraint {

  /**
   * Message shown when trying to select a node of the wrong language.
   *
   * @var string
   */
  protected $message = 'Node "%s" (%d) already has been selected as a translation for <a href="/node/%d/translations" target="_blank">another node</a>.';

  public function generateMessage(Node $node) {
    return sprintf(
      $this->message,
      $node->getTitle(),
      $node->id(),
      $node->id()
    );
  }

  /**
   * {@inheritdoc}
   */
}
