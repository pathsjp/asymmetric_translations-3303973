<?php

namespace Drupal\asymmetric_translations\EventSubscriber;

use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\asymmetric_translations\Form\NodeEditAsymmetricNodeTranslationsForm;

class ControllerAlterSubscriber implements EventSubscriberInterface {

  /**
   * We tried several ways to alter the output of the node "translations" tab.
   * Altering the route triggered an error because the original route's access check expects a parameter,
   * set by the Drupal\content_translation\Controller\ContentTranslationController.
   * Since we would actually like those access checks to stay intact, we looked for another way to alter the output
   * of the controller.
   * In a StackExchange answer someone suggested to listen for the view event and then alter the controller's
   * output. This seems to work very well.
   * See: https://drupal.stackexchange.com/a/281158/61343
   *
   * So here we actually overwrite the output that the original controller created.
   */
  public function onView(ViewEvent $event) {
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');

    if ($route == 'entity.node.content_translation_overview') {
      $build = $event->getControllerResult();

      if (is_array($build)) {
        /** @var Node $node */
        $node = \Drupal::routeMatch()->getParameter('node');

        if ($this->hasTranslations($node)) {
          $build['content_translation_overview'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => 'asymmetric-translations--container asymmetric-translations--container--warning',
            ],
            'warning' => [
              '#type' => 'inline_template',
              '#template' => '
                <h2>Warning: core translations are found, while using the asymmetric_translations module</h2>
                <p>This website is using the <b>asymmetric_translations</b> module, but there are core translations found in this entity.
                Please either remove these translations, or disable the <b>asymmetric_translations</b> module to use core translations instead.</p>
              '
            ],
            'asymmetric_translations_overview' => $build['content_translation_overview'],
            '#attached' => [
              'library' => [
                'asymmetric_translations/admin'
              ]
            ]
          ];

        }else{
          unset($build['content_translation_overview']);
        }

        // Add custom translations to the controller build array
        $build['asymmetric_translations_overview'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => 'asymmetric-translations--container',
          ],
          'message' => [
            '#type' => 'inline_template',
            '#template' => '
                <h2>Asymmetric Translations</h2>
                <p>Each translation can have a different kind of bundle and can have totally unique content, including different and different amount of paragraphs.</p>
              '
          ],
          'asymmetric_translations_overview' => $this->getTranslationsOverview($node),
          '#attached' => [
            'library' => [
              'asymmetric_translations/admin'
            ]
          ]
        ];

        $event->setControllerResult($build);
      }
    }
  }

  private function hasTranslations(Node $node) {
    $languages = \Drupal::languageManager()->getLanguages();

    foreach($languages as $language) {
      if ($node->language()->getId() === $language->getId()) {
        continue;
      }

      if ($node->hasTranslation($language->getId())) {
        return true;
      }
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // priority > 0 so that it runs before the controller output
    // is rendered by \Drupal\Core\EventSubscriber\MainContentViewSubscriber
    $events[KernelEvents::VIEW][] = ['onView', 50];
    return $events;
  }

  /**
   * @param Node $node
   * @return array
   */
  public function getTranslationsOverview(Node $node): array {
    return \Drupal::formBuilder()
                  ->getForm(NodeEditAsymmetricNodeTranslationsForm::class, $node);
  }

}
