<?php

namespace Drupal\asymmetric_translations\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the asymmetric node translation entity edit forms.
 */
class AsymmetricNodeTranslationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New asymmetric node translation %label has been created.', $message_arguments));
      $this->logger('asymmetric_translations')->notice('Created new asymmetric node translation %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The asymmetric node translation %label has been updated.', $message_arguments));
      $this->logger('asymmetric_translations')->notice('Updated new asymmetric node translation %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.asymmetric_node_translation.canonical', ['asymmetric_node_translation' => $entity->id()]);
  }

}
