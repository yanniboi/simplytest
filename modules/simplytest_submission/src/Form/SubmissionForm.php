<?php

namespace Drupal\simplytest_submission\Form;

/**
 * Form controller for Submission edit forms.
 *
 * @ingroup simplytest_submission
 */
class SubmissionForm extends SubmissionFormBase {

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
