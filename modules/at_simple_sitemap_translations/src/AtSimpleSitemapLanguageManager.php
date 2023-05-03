<?php

namespace Drupal\at_simple_sitemap_translations;

use Drupal\Core\Language\LanguageManager;

class AtSimpleSitemapLanguageManager {

  private LanguageManager $languageManager;

  public function __construct(LanguageManager $language_manager) {
    $this->languageManager = $language_manager;
  }

  public function getAvailableLanguages() {
    return $this->languageManager->getLanguages();

    // TODO: implement logic for language_access module
    // Check if this is even needed; if we just switch to anonymous for the current user, does the language manager simply return the languages we want?
    /*if (\Drupal::moduleHandler()->moduleExists('language_access')) {
      $anonymous_role = Role::load(AccountInterface::ANONYMOUS_ROLE);
      $languages      = \Drupal::languageManager()->getLanguages();

      $allowed_lngs   = [];
      foreach ($languages as $key => $language) {
        if ($anonymous_role->hasPermission('access language ' . $language->getId())) {
          $allowed_lngs[$key] = $language;
        }
      }

      return $allowed_lngs;
    }*/
  }

}
