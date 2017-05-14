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
  public function alterForm(array &$form, FormStateInterface $form_state) {
    parent::alterForm($form, $form_state);
    // Add submission theme library.
    $form['#attached']['library'][] = 'simplytest_submission/submission';
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
