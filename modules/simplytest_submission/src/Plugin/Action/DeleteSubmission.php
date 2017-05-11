<?php

namespace Drupal\simplytest_submission\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\simplytest_submission\SubmissionService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes a Submission Instance on the service server.
 *
 * @Action(
 *   id = "simplytest_submission_delete_submission_action",
 *   label = @Translation("Delete submission instance(s)"),
 *   type = "simplytest_submission"
 * )
 */
class DeleteSubmission extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The submission automation service.
   *
   * @var \Drupal\simplytest_submission\SubmissionService
   */
  protected $submissionService;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\simplytest_submission\SubmissionService $submission_service
   *   The submission service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SubmissionService $submission_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->submissionService = $submission_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('simplytest_submission.submission_service'));
  }

  /**
   * {@inheritdoc}
   */
  public function execute($submission = NULL) {
    /* @var $submission \Drupal\simplytest_submission\Entity\Submission */
    if ($submission == FALSE) {
      return FALSE;
    }

    if (!$submission->container_id->value) {
      return FALSE;
    }

    // Set submission status to resolved.
    return $this->submissionService->deleteBuild($submission);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
