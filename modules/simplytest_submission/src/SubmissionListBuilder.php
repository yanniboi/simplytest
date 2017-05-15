<?php

namespace Drupal\simplytest_submission;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Submission entities.
 *
 * @ingroup simplytest
 */
class SubmissionListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Submission ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\simplytest_submission\Entity\Submission */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.simplytest_submission.edit_form', [
          'simplytest_submission' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Add view operation.
    if ($entity->access('view') && $entity->hasLinkTemplate('canonical')) {
      $operations['view'] = [
        'title' => t('View'),
        'weight' => 1,
        'url' => $entity->toUrl('canonical'),
      ];
    }

    // Add delete_instance operation.
    if ($entity->access('delete')) {
      $operations['delete_instance'] = [
        'title' => t('Delete Instance'),
        'weight' => 100,
        'url' => new Url(
          'entity.simplytest_submission.delete_instance', [
            'simplytest_submission' => $entity->id(),
          ]
        ),
      ];
    }

    return $operations;
  }

}
