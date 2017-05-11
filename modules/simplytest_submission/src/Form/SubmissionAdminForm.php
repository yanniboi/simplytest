<?php

namespace Drupal\simplytest_submission\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Form controller for Submission edit forms.
 *
 * @ingroup simplytest_submission
 */
class SubmissionAdminForm extends SubmissionFormBase {

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
  public function buildFieldGroups() {
    $fieldgroups = parent::buildFieldGroups();
    
    $fieldgroups['group_admin'] = [
      'title' => $this->t('Admin'),
      'attributes' => ['class' => ['submission-admin']],
      'weight' => 4,
      'fields' => [
        "status",
        "container_token",
        "container_url",
      ],
    ];

    return $fieldgroups;
  }


}
