<?php

namespace Drupal\simplytest_submission;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Submission entities.
 *
 * @ingroup simplytest
 */
interface SubmissionInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Base url for service requests.
   */
  const SERVICE_URL = 'https://spawn.sh';

  /**
   * Bit flag for open statuses.
   */
  const STATUS_OPEN = 0x01;

  /**
   * Bit flag for closed statuses.
   */
  const STATUS_CLOSED = 0x02;

  /**
   * Bit flag for statuses requiring action.
   */
  const STATUS_ACTION_REQUIRED = 0x04;

  /**
   * Bit flag for statuses that are decided.
   */
  const STATUS_DECIDED = 0x08;

  /**
   * Bit flag for statuses that are accepted.
   */
  const STATUS_ACCEPTED = 0x10;

  /**
   * Bit flag for statuses that are rejected.
   */
  const STATUS_REJECTED = 0x20;

  /**
   * Bit flag for status where a payment is in progress.
   */
  const STATUS_PAYMENT = 0x40;

  /**
   * Bit flag for statuses where the submissions should be ignored.
   */
  const STATUS_IGNORED = 0x80;

  /**
   * Gets the Submission name.
   *
   * @return string
   *   Name of the Submission.
   */
  public function getName();

  /**
   * Gets the Submission creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Submission.
   */
  public function getCreatedTime();

  /**
   * Sets the Submission creation timestamp.
   *
   * @param int $timestamp
   *   The Submission creation timestamp.
   *
   * @return \Drupal\simplytest_submission\SubmissionInterface
   *   The called Submission entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Submission status.
   *
   * @return string
   *   Machine name of Submission status.
   *
   * @see simplytest_submission_status_options()
   */
  public function getStatus();

  /**
   * Sets the status of a Submission.
   *
   * @param string $status
   *   Machine name of Submission status.
   *
   * @return \Drupal\simplytest_submission\SubmissionInterface
   *   The called Submission entity.
   */
  public function setStatus($status);

}
