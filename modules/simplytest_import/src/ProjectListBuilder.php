<?php

namespace Drupal\simplytest_import;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Project entities.
 *
 * @ingroup simplytest
 */
class ProjectListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Project ID');
    $header['name'] = $this->t('Name');
    $header['creator'] = $this->t('Creator');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\simplytest_import\Entity\Project */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.simplytest_project.edit_form', [
          'simplytest_project' => $entity->id(),
        ]
      )
    );
    $row['creator'] = $entity->creator->value;
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

    return $operations;
  }

}
