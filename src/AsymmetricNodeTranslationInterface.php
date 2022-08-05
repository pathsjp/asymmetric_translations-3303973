<?php

namespace Drupal\asymmetric_translations;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\node\Entity\Node;

/**
 * Provides an interface defining a asymmetric node translation entity type.
 */
interface AsymmetricNodeTranslationInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the asymmetric node translation creation timestamp.
   *
   * @return int
   *   Creation timestamp of the asymmetric node translation.
   */
  public function getCreatedTime();

  /**
   * Sets the asymmetric node translation creation timestamp.
   *
   * @param int $timestamp
   *   The asymmetric node translation creation timestamp.
   *
   * @return \Drupal\asymmetric_translations\AsymmetricNodeTranslationInterface
   *   The called asymmetric node translation entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the referenced node for the current translation.
   *
   * @return Node
   *   Referenced node
   */
  public function getNode();

  /**
   * Sets the referenced node for the current language.
   *
   * @param Node $node
   *   Node for the current language.
   *
   * @return \Drupal\asymmetric_translations\AsymmetricNodeTranslationInterface
   *   The called asymmetric node translation entity.
   */
  public function setNode(Node $node);

}
