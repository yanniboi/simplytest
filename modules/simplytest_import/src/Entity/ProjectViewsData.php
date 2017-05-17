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

    $data['simplytest_project']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Project'),
      'help' => $this->t('The Project ID.'),
    );

    $data['simplytest_project']['sandbox']['filter']['type'] = 'yes-no';
    $data['simplytest_project']['sandbox']['filter']['use_equal'] = TRUE;

//    $data['simplytest_project']['type']['filter']['id'] = 'list_field';
//    $data['simplytest_project']['type']['argument']['id'] = 'string_list_field';


    dpm($data);

    return $data;
  }

}
