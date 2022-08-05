<?php

namespace Drupal\asymmetric_translations\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when node references must be fixed in an entity.
 */
class NodeSavedEvent extends Event {

  const EVENT_NAME = 'asymmetric_translations.node_saved';

  public EntityInterface $entity;

  public Language $language;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of the user logged in.
   */
  public function __construct(EntityInterface $entity, Language $language) {
    $this->entity = $entity;
    $this->language = $language;
  }

}
