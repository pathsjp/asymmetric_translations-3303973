<?php

namespace Drupal\at_auto_fix_entity_references\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\asymmetric_translations\Entity\AsymmetricNodeTranslation;
use Drupal\Component\Utility\Html;
use Drupal\Core\Language\Language;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AutoFixNodeReferences implements EventSubscriberInterface {

  private $field_types = [];

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      'asymmetric_translations.node_cloned' => 'handleNodeClonedEvent',
      'asymmetric_translations.node_saved' => 'handleNodeSavedEvent',
      'asymmetric_translations.paragraph_saved' => 'handleParagraphSavedEvent',
    ];
  }

  /**
   * The language must be passed as a parameter because paragraphs do not have the correct language persé.
   *
   * TODO: Replace Event with specific Interface
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   * @param Language $language
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleNodeClonedEvent(Event $event) {
    $entity = $event->entity;
    $language = $event->language;

    $this->recursivelyFixNodeReferences($entity, $language, TRUE);
  }

  /**
   * The language must be passed as a parameter because paragraphs do not have the correct language persé.
   *
   * TODO: Replace Event with specific Interface
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   * @param Language $language
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleNodeSavedEvent(Event $event) {
    $entity = $event->entity;
    $language = $event->language;

    $this->recursivelyFixNodeReferences($entity, $language, FALSE);
  }

  /**
   * The language must be passed as a parameter because paragraphs do not have the correct language persé.
   *
   * TODO: Replace Event with specific Interface
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   * @param Language $language
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleParagraphSavedEvent(Event $event) {
    $entity = $event->entity;
    $language = $event->language;

    $this->recursivelyFixNodeReferences($entity, $language, FALSE);
  }

  /**
   * The language must be passed as a parameter because paragraphs do not have the correct language persé.
   *
   * TODO: Replace Event with specific Interface
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param Language $language
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function recursivelyFixNodeReferences(EntityInterface $entity, Language $language, $recursive = TRUE) {
    foreach ($entity->getFields() as $field_name => $field) {
      $type = $field->getFieldDefinition()->getType();

      $this->field_types[$type] = isset($this->field_types[$type]) ? $this->field_types[$type] + 1 : 1;

      //echo "$field_name ($type)" . PHP_EOL;

      switch ($type) {
        case 'entity_reference_revisions':
          if (!$recursive) {
            break;
          }

          foreach ($entity->get($field_name)->referencedEntities() as $paragraph) {
            $this->recursivelyFixNodeReferences($paragraph, $language, $recursive);
          }
          break;

        case 'entity_reference':
          if ($field->getFieldDefinition()->getSettings()['target_type'] !== 'node') {
            break;
          }

          $entity->set($field_name, $this->replaceEntityReferenceFieldValues($field, $language));

          break;

        case 'text_long':
          $entity->set($field_name, $this->replaceLinkitNodeReferences($field, $language));
          break;

        case 'link':
          $entity->set($field_name, $this->replaceLinkFieldValues($field, $language));

          break;
      }
    }
  }

  private function getNodeTranslation($nid, Language $language) {
    $query = \Drupal::entityQuery('asymmetric_node_translation')
                    ->condition('node', $nid);

    if (!$ant_results = $query->execute()) {
      return false;
    }

    $ant = AsymmetricNodeTranslation::load(reset($ant_results));

    if ($ant->language()->getId() === $language->getId()) {
      return false;
    }
    if (!$ant->hasTranslation($language->getId())) {
      return false;
    }

    $ant_translation = $ant->getTranslation($language->getId());
    if ($node_translation = $ant_translation->getNode()) {
      return $node_translation->id();
    }

    return false;
  }

  /**
   * @param $field
   * @param Language $language
   * @return array
   */
  private function replaceEntityReferenceFieldValues($field, Language $language): array {
    $values = $field->getValue();

    foreach ($values as $vkey => $value) {
      $nid = $value['target_id'];
      if ($nid_t = $this->getNodeTranslation($nid, $language)) {
        $values[$vkey]['target_id'] = $nid_t;
      }
    }

    return $values;
  }

  /**
   * @param $field
   * @param Language $language
   * @return array
   */
  private function replaceLinkFieldValues($field, Language $language) {
    $values = $field->getValue();

    foreach ($values as $vkey => $value) {
      if (!isset($value['uri']) || !preg_match('/^entity:node\/(\d+)$/', $value['uri'], $matches)) {
        continue;
      }

      $nid = $matches[1];
      if ($nid_t = $this->getNodeTranslation($nid, $language)) {
        $values[$vkey]['uri'] = 'entity:node/' . $nid_t;
      }
    }

    return $values;
  }

  /**
   * Replace linkit node references, for example:
   * <a data-entity-substitution="canonical" data-entity-type="node" data-entity-uuid="e28f7d0f-bfc0-4755-bd5a-ae58c03fa017" href="/node/17">
   *
   * In the end these references will be replaced by the LinkitFilter.php, so we just need to replace the uuid and the href.
   * The code below has been copied partly from the the LinkitFilter.
   */
  private function replaceLinkitNodeReferences($field, Language $language) {
    $values = $field->getValue();

    foreach ($values as $vkey => $value) {
      if (!isset($value['value'])) {
        continue;
      }

      if (strpos($value['value'], 'data-entity-type') === FALSE || strpos($value['value'], 'data-entity-uuid') === FALSE) {
        continue;
      }

      $dom = Html::load($value['value']);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//a[@data-entity-type and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $entity_type = $element->getAttribute('data-entity-type');
          $uuid = $element->getAttribute('data-entity-uuid');

          // Skip empty attributes to prevent loading of non-existing
          // content type.
          if ($entity_type !== 'node' || $uuid === '') {
            continue;
          }

          //echo "Load node for " . $element->getAttribute('href') . " (" . $uuid . ")" . PHP_EOL;

          if (!$node = \Drupal::service('entity.repository')->loadEntityByUuid('node', $uuid)) {
            //echo "Node not found" . PHP_EOL;
            continue;
          }

          if (!$nid_t = $this->getNodeTranslation($node->id(), $language)) {
            //echo "No translation ID found" . PHP_EOL;
            continue;
          }
          if (!$translation = Node::load($nid_t)) {
            //echo "No translation object found" . PHP_EOL;
            continue;
          }

          $element->setAttribute('href', '/node/' . $nid_t);
          $element->setAttribute('data-entity-uuid', $translation->uuid());

        } catch (\Exception $e) {
          //echo "Unable to process html; message: " . $e->getMessage() . PHP_EOL;
        }
      }

      $values[$vkey]['value'] = Html::serialize($dom);
    }

    return $values;
  }

}
