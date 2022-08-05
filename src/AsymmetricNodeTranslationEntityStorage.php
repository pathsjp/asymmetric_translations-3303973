<?php

namespace Drupal\asymmetric_translations;

use Drupal\asymmetric_translations\Entity\AsymmetricNodeTranslation;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\Language;
use Drupal\node\Entity\Node;

/**
 * Provides an interface for Asymmetric Node Translation storage.
 */
class AsymmetricNodeTranslationEntityStorage extends SqlContentEntityStorage implements AsymmetricNodeTranslationEntityStorageInterface {

  /**
   * Find translation entities by node
   *
   * @param Node $node
   *
   * @return array
   */
  public function getTranslationEntitiesByNode(Node $node) {
    // Note that we explicity not filter by language here, because we want to be able to find any translation
    $results = \Drupal::entityQuery('asymmetric_node_translation')
      ->condition('node', $node->id())
      ->execute();

    if (!$results) {
      return [];
    }

    return $this->loadMultiple($results);
  }

  /**
   * Find translation entity by node.
   * In some case we can simply expect a node to have one (or none) entity
   *
   * @param Node $node
   *
   * @return NULL|AsymmetricNodeTranslation
   */
  public function getTranslationEntityByNode(Node $node) {
    $results = $this->getTranslationEntitiesByNode($node);
    return reset($results);
  }

  /**
   * Find translation entity by node and return it in the same language as the provided node.
   *
   * @param Node $node
   * @return mixed|null
   */
  public function getTranslationEntityTranslationByNode(Node $node) {
    if (!$ant = $this->getTranslationEntityByNode($node)) {
      return NULL;
    }

    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      if (!$ant->hasTranslation($language->getId())) {
        continue;
      }
      
      $ant_translation = $ant->getTranslation($language->getId());
      if ($ant_translation->node->target_id === $node->id()) {
        return $ant_translation;
      }
    }

    return NULL;
  }

  public function addTranslation(Language $language, AsymmetricNodeTranslation $ant, Node $node) {
    // Either load the current translation or create a new one
    if ($ant->hasTranslation($language->getId())) {
      $ant_translation = $ant->getTranslation($language->getId());
    }
    else {
      $ant_translation = $ant->addTranslation($language->getId());
    }

    // Set the newly referenced node to the translation
    $ant_translation->node = $node;

    $violations = $ant_translation->validate();
    if ($violations->count()) {
      foreach ($violations as $violation) {
        \Drupal::messenger()->addError($violation->getMessage());
      }

      return false;
    }

    return true;
  }

}
