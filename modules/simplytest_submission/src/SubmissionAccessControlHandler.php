<?php

namespace Drupal\simplytest_submission;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Submission entity.
 *
 * @see \Drupal\simplytest_submission\Entity\Submission.
 */
class SubmissionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $result = parent::checkAccess($entity, $operation, $account);
    if ($result->isNeutral()) {
      switch ($operation) {
        case 'view':
          return AccessResult::allowedIfHasPermission($account, 'view submission entities');

        case 'update':
          return AccessResult::allowedIfHasPermission($account, 'edit submission entities');

        case 'manage':
          return AccessResult::allowedIfHasPermission($account, 'manage submission entities');

        case 'delete':
          return AccessResult::allowedIfHasPermission($account, 'delete submission entities');
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $result = parent::checkCreateAccess($account, $context, $entity_bundle);
    if ($result->isNeutral()) {
      return AccessResult::allowed();
    }
    return $result;
  }

}
