<?php

namespace Drupal\simplytest_import\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Defines a controller to render a single submission entity.
 */
class ProjectController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function import() {
    $operations = [];
    $operations[] = ['simplytest_import_batch_operation_process_xml', ['file']];

    $batch = array(
      'title' => t('Processing Drupal.org Projects import'),
      'operations' => $operations
    );

    batch_set($batch);
    return batch_process(Url::fromRoute('entity.simplytest_project.collection'));
  }

}
