<?php

namespace Drupal\at_simple_sitemap_translations;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;

class AtSimpleSitemapUrlAlternativesProcessor {

  private array $activeLanguages = [];

  /**
   * @var \Drupal\asymmetric_translations\AsymmetricNodeTranslationEntityStorage
   */
  private $antStorage;

  /**
   * @var \Drupal\node\NodeStorage
   */
  private $nodeStorage;

  private array $frontPageUrls = [];

  public function __construct(EntityTypeManager $entity_type_manager, AtSimpleSitemapLanguageManager $language_manager) {
    $this->antStorage = $entity_type_manager->getStorage('asymmetric_node_translation');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->activeLanguages = $language_manager->getAvailableLanguages();

    $this->gatherFrontPageUrls();
  }

  private function gatherFrontPageUrls() {
    $this->frontPageUrls = [];
    foreach($this->activeLanguages as $language) {
      $url = Url::fromRoute('<front>', [], ['language' => $language]);
      $this->frontPageUrls[$language->getId()] = $url->toString();
    }
  }

  public function alterSitemapLink(&$link) {
    if (!isset($link['meta']['entity_info']['entity_type'])) {
      return;
    }
    if ($link['meta']['entity_info']['entity_type'] !== 'node') {
      return;
    }

    $nid = $link['meta']['entity_info']['id'];
    if (!$node = $this->nodeStorage->load($nid)) {
      return;
    }
    if (!$ant = $this->antStorage->getTranslationEntityByNode($node)) {
      return;
    }

    // If the URL is a frontpage URL, handle it differently
    if (in_array($link['url'], $this->frontPageUrls)) {
      $link['alternate_urls'] = $this->frontPageUrls;
      return;
    }

    foreach ($this->activeLanguages as $language) {
      $this->alterSitemapLinkLanguage($link, $ant, $language);
    }
  }

  private function alterSitemapLinkLanguage(&$link, $ant, $language) {
    // A URL has already been set for this language; we don't want to fiddle with that
    if (isset($link['alternate_urls'][$language->getId()])) {
      return;
    }
    if (!$ant->hasTranslation($language->getId())) {
      return;
    }

    $ant_translation = $ant->getTranslation($language->getId());
    $nodes = $ant_translation->node->referencedEntities();
    if (!$nodes) {
      return;
    }

    $node_translation = reset($nodes);
    $link['alternate_urls'][$language->getId()] = Url::fromRoute('entity.node.canonical', [
      'node' => $node_translation->id(),
    ], [
      'absolute' => TRUE,
      'language' => $language
    ])->toString();
  }

}
