<?php

namespace Drupal\asymmetric_translations\Entity;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\asymmetric_translations\AsymmetricNodeTranslationInterface;
use Drupal\node\Entity\Node;

/**
 * Defines the asymmetric node translation entity class.
 *
 * @ContentEntityType(
 *   id = "asymmetric_node_translation",
 *   label = @Translation("Asymmetric Node Translation"),
 *   label_collection = @Translation("Asymmetric Node Translations"),
 *   handlers = {
 *     "storage" = "Drupal\asymmetric_translations\AsymmetricNodeTranslationEntityStorage",
 *     "view_builder" = "Drupal\asymmetric_translations\AsymmetricNodeTranslationViewBuilder",
 *     "list_builder" = "Drupal\asymmetric_translations\AsymmetricNodeTranslationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\asymmetric_translations\AsymmetricNodeTranslationAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\asymmetric_translations\Form\AsymmetricNodeTranslationForm",
 *       "edit" = "Drupal\asymmetric_translations\Form\AsymmetricNodeTranslationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   constraints = {
 *     "AsymmetricTranslationNodeLanguage" = {},
 *     "AsymmetricTranslationSingleNodeUsage" = {},
 *   },
 *   base_table = "asymmetric_node_translation",
 *   data_table = "asymmetric_node_translation_field_data",
 *   translatable = TRUE,
 *   admin_permission = "access asymmetric node translation overview",
 *   entity_keys = {
 *     "id" = "id",
 *     "langcode" = "langcode",
 *     "label" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/asymmetric-node-translations/add",
 *     "canonical" = "/asymmetric_node_translation/{asymmetric_node_translation}",
 *     "edit-form" = "/admin/content/asymmetric-node-translations/{asymmetric_node_translation}/edit",
 *     "delete-form" = "/admin/content/asymmetric-node-translations/{asymmetric_node_translation}/delete",
 *     "collection" = "/admin/content/asymmetric-node-translation"
 *   },
 * )
 */
class AsymmetricNodeTranslation extends ContentEntityBase implements AsymmetricNodeTranslationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNode() {
    $entities = $this->get('node')->referencedEntities();

    if ($entities) {
      return reset($entities);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setNode(Node $node) {
    if ($this->getNode()) {
      throw new \Exception('An referenced node of an asymmetric translation cannot be overwritten. Remove the old reference first.');
    }

    $this->set('node', $node);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['node'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The node for this language.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setDisplayOptions('view', [
       'label' => 'above',
       'type' => 'entity_reference',
       'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
       'type' => 'entity_reference',
       'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the asymmetric node translation was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the asymmetric node translation was last edited.'));

    return $fields;
  }

}
