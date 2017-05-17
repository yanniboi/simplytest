<?php

namespace Drupal\simplytest_import\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Project entities.
 */
class ProjectViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['simplytest_project']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Project'),
      'help' => $this->t('The Project ID.'),
    ];

    $data['simplytest_project']['sandbox']['filter']['type'] = 'yes-no';
    $data['simplytest_project']['sandbox']['filter']['use_equal'] = TRUE;

    return $data;
  }

}
