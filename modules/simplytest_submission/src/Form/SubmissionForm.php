<?php

namespace Drupal\simplytest_submission\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Submission edit forms.
 *
 * @ingroup simplytest_submission
 */
class SubmissionForm extends SubmissionFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisabledFields() {
    return [
      'webspace_webserver',
      'webspace_interpreter',
      'webspace_secondary_dbs',
      'drupal_projects',
    ];
  }

}
