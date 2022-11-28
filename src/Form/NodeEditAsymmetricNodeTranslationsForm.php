<?php

namespace Drupal\asymmetric_translations\Form;

use Drupal\asymmetric_translations\Entity\AsymmetricNodeTranslation;
use Drupal\content_translation\ContentTranslationManager;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * The form on a node's translations tab in which to add and edit asymmetric translations
 */
class NodeEditAsymmetricNodeTranslationsForm extends FormBase {

  /**
   * @var Node
   */
  private $current_node;

  /**
   * @var NULL|AsymmetricNodeTranslation
   */
  private $current_ant;

  public function getFormId() {
    return 'node_edit_asymmetric_node_translations_forms';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $current_node = null) {
    if (empty($current_node)) {
      throw new \Exception('The $current_node must be passed to render the translations form');
    }

    $this->current_node = $current_node;
    $this->current_ant = $this->getWorkingAnt();

    $languages = \Drupal::languageManager()->getLanguages();

    $form['translations'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Language'),
        $this->t('Translation'),
        $this->t('Status'),
        $this->t('Operations'),
      ],
    ];

    $source_language = $this->current_ant->getUntranslated()->language();

    foreach($languages as $language) {
      $is_source_language = $language->getId() === $source_language->getId();

      $row = [
        'language' => ['#markup' => $language->getName() . ($is_source_language ? ' (' . $this->t('Source language') . ')' : '')],
      ];

      $operations = [
        'data' => [
          '#type' => 'operations',
          '#links' => [],
        ],
      ];

      $links = &$operations['data']['#links'];

      $link_options = ['language' => $language];

      // TODO: add saved translation to ANT if add-node form is saved, based on GET parameter "source" and "translation"
      // TODO: make sure that all node types use "interface language" as their default language

      $translation = $this->getNodeValueFromCurrentAnt($language);

      if (!empty($translation)) {
        $links['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('entity.node.edit_form', ['node' => $translation->id()], $link_options),
        ];

        if (!$is_source_language) {
          $links['delete'] = [
            'title' => $this->t('Delete'),
            'url' => Url::fromRoute('entity.node.delete_form', ['node' => $translation->id()], $link_options),
          ];
        }

        $row['translation'] = Link::fromTextAndUrl($translation->getTitle(), $translation->toUrl())->toRenderable();
        $row['status'] = ['#markup' => $translation->isPublished() ? $this->t('Published') : $this->t('Unpublished')];
        $row['operations'] = $operations;

      }elseif (!$is_source_language && $this->current_node->hasTranslation($language->getId())) {
        $translation = $this->current_node->getTranslation($language->getId());

        $row['#attributes'] = ['class' => 'asymmetric-translations--table-row--warning'];

        $row['translation'] = Link::fromTextAndUrl($translation->getTitle(), $translation->toUrl())->toRenderable();
        $row['status'] = ['#markup' => $translation->isPublished() ? $this->t('Published') : $this->t('Unpublished')];
        $row['operations'] = ['#type' => 'inline_template', '#template' => '<span class="warning"><b>Warning:</b> core translation detected</span>'];

      } else {
        $url = Url::fromRoute('quick_node_clone.node.quick_clone', ['node' => $current_node->id()], $link_options);
        $url->setOption('query', ['clone_for_translation' => $language->getId()]);
        $links['add'] = [
          'title' => $this->t('Add'),
          'url' => $url,
        ];

        $row['translation'] = [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'node',
          '#title' => $language->getName(),
          '#title_display' => 'invisible',
          '#default_value' => null,
          //'#tags' => TRUE,
          '#selection_settings' => [
            //'target_bundles' => array('page', 'article'),
          ],
          '#weight' => 20,
        ];

        $row['status'] = ['#markup' => $this->t('N/A')];
        $row['operations'] = $operations;
      }

      $form['translations'][$language->getId()] = $row;
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#weight' => 100,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $has_violations = FALSE;
    $has_translations = FALSE;

    if (!$this->current_ant) {
      $this->current_ant = $this->createAnt();
    }

    $storage = \Drupal::entityTypeManager()->getStorage('asymmetric_node_translation');

    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      // We don't edit the current node reference
      if ($language->getId() === $this->current_node->language()->getId()) {
        continue;
      }

      // Fetch the array of submitted translations; only formerly "empty" translations can be submitted
      $submitted_translations = $form_state->getValue('translations');

      // If the current language ID doesn't exist in the submitted translations, it wasn't editable
      if (!isset($submitted_translations[$language->getId()])) {
        // If the current language wasn't submitted, we expect that the ANT already has a translation in that language
        if ($this->current_ant->hasTranslation($language->getId())) {
          $has_translations = TRUE;
        }

        continue;
      }

      // If the value for a certain language is empty, remove the translation
      if (!$nid = $submitted_translations[$language->getId()]['translation']) {
        if ($this->current_ant->hasTranslation($language->getId())) {
          $this->current_ant->removeTranslation($language->getId());
        }

        continue;
      }

      // Load the referenced node
      if (!$referenced_node = Node::load($nid)) {
        \Drupal::messenger()->addError(sprintf('Referenced node with ID %d could not be loaded', $nid));
        return;
      }

      $has_translations = TRUE;

      $storage->addTranslation($language, $this->current_ant, $referenced_node);
    }

    if (!$has_translations) {
      $this->current_ant->delete();
      \Drupal::messenger()->addMessage('Saved succesfully');
      return;
    }

    if (!$has_violations) {
      $this->current_ant->save();
      \Drupal::messenger()->addMessage('Saved succesfully');
    }
  }

  /**
   * @param LanguageInterface $language
   * @return false|mixed|null
   */
  private function getNodeValueFromCurrentAnt(LanguageInterface $language) {
    if (!$this->current_ant || !$this->current_ant->hasTranslation($language->getId())) {
      return NULL;
    }

    $ant_translation = $this->current_ant->getTranslation($language->getId());
    $entities = $ant_translation->node->referencedEntities();

    $node = reset($entities);

    // If the ANT was somehow linked to an incorrect language, or the language of the node has changed, return nothing
    if (!$node->hasTranslation($language->getId())) {
      return NULL;
    }

    return $node->getTranslation($language->getId());
  }

  /**
   * Get Asymmetric Translation object from the database by looking up the current node
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getWorkingAnt() {
    $storage = \Drupal::entityTypeManager()->getStorage('asymmetric_node_translation');
    if ($ant = $storage->getTranslationEntityByNode($this->current_node)) {
      return $ant;
    }

    return $this->createAnt();
  }

  /**
   * Create Asymmetric Translation object from the current node
   *
   * @return AsymmetricNodeTranslation|\Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function createAnt() {
    $source_language = $this->current_node->getUntranslated()->language()->getId();
    $working_ant = AsymmetricNodeTranslation::create(['langcode' => $source_language]);
    $working_ant->node = $this->current_node;

    return $working_ant;
  }

}
