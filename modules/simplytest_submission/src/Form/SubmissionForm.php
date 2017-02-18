<?php

namespace Drupal\simplytest_submission\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\simplytest_submission\SubmissionInterface;

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
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->entity->isNew()) {
      return $actions;
    }

//    $actions['accept'] = [
//      '#type' => 'submit',
//      '#value' => $this->t('Accept'),
//      '#validate' => [
//        '::validateForm',
//        '::validateNoChange',
//      ],
//      '#submit' => [
//        '::submitForm',
//        '::submitAccept',
//        '::save',
//        '::submitPayment',
//      ],
//      '#access' => $needs_decision && $has_repayment,
//    ];


    return $actions;
  }

  /**
   * Validate that nothing has changed that requires a recalculation.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateNoChange(array &$form, FormStateInterface $form_state) {
    // To reject we need a reason.
    if ($form_state->getValue('calling_points_table')) {
      $form_state->setError($form['actions'], $this->t('Changing trains requires a recalculation.'));
    }
  }

  /**
   * Form validation handler.
   *
   * Make sure we have a rejection reason before rejecting.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateReject(array &$form, FormStateInterface $form_state) {
    // To reject we need a reason.
    if (!$form_state->getValue('repay_reject_reason')) {
      $form_state->setErrorByName('repay_reject_reason', $this->t('You must choose a reason for rejecting this submission.'));
    }
  }

  /**
   * Form submission handler.
   *
   * Set submission status to Accepted (Pending).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitAccept(array &$form, FormStateInterface $form_state) {
    $this->entity->status = 'resolved';
    $this->submissionAutomation->sendEmails($this->entity);
  }


  /**
   * Form submission handler.
   *
   * Set submission status to Rejected.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitReject(array &$form, FormStateInterface $form_state) {
    $this->entity->status = 'rejected';
    $this->submissionAutomation->sendEmails($this->entity);
    $form_state->setRedirect('entity.simplytest_submission.collection');
  }

  /**
   * Form submission handler.
   *
   * Set submission status to Escalated (Under Investigation).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitEscalate(array &$form, FormStateInterface $form_state) {
    $this->entity->status = 'needs_review';
    $form_state->setRedirect('entity.simplytest_submission.collection');
  }

}
