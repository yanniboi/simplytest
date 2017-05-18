<?php

namespace Drupal\simplytest_submission\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\simplytest_import\Entity\Project;

/**
 * Form controller for Submission edit forms.
 *
 * @ingroup simplytest_submission
 */
class SocialSubmissionForm extends SubmissionForm {

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    parent::alterForm($form, $form_state);
    $widget = &$form['drupal_projects']['widget'][0];
    $widget['project_install']['widget']['value']['#default_value'] = FALSE;
    if (isset($widget['project_identifier']['widget']['#type']) && in_array($widget['project_identifier']['widget']['#type'], ['select', 'radios'])) {
      $widget['project_identifier']['widget']['#default_value'] = 'social';
    }
    else {
      $project = Project::load($_GET['social']);
      $widget['project_identifier']['widget']['0']['target_id']['#default_value'] = $project;
    }
  }
  
}
