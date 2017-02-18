<?php

namespace Drupal\simplytest_submission\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SubmissionAddController.
 *
 * @package Drupal\simplytest_submission\Controller
 */
class SubmissionAddController extends ControllerBase {

  /**
   * Submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;


  /**
   * {@inheritdoc}
   */
  public function __construct(EntityStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('simplytest_submission')
    );
  }

  /**
   * Displays add links for available bundles/types for entity simplytest_submission.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param string $mode
   *   The requested form mode.
   *
   * @return array
   *   A render array for a list of the simplytest_submission types that can be added
   *   or if there is only one type defined for the site, the function returns
   *   the add page for that type.
   */
  public function add(Request $request, $mode = 'default') {
    return $this->addForm($mode);
  }

  /**
   * Request add form in the data_collection form mode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A render array for a list of the simplytest_submission types that can be added
   *   or if there is only one type defined for the site, the function returns
   *   the add page for that type.
   */
  public function addPassenger(Request $request) {
    return $this->add($request, 'data_collection');
  }

  /**
   * Request add form in the processing form mode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A render array for a list of the simplytest_submission types that can be added
   *   or if there is only one type defined for the site, the function returns
   *   the add page for that type.
   */
  public function addStaff(Request $request) {
    return $this->add($request, 'processing');
  }

  /**
   * Presents the creation form for simplytest_submission entities of given type.
   *
   * @param string $mode
   *   The requested form mode.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function addForm($mode = 'default') {
    $entity = $this->storage->create([]);
    return $this->entityFormBuilder()->getForm($entity, $mode);
  }

  /**
   * Provides the page title for this controller.
   *
   * @return string
   *   The page title.
   */
  public function getAddFormTitle() {
    return t('Create Submission');
  }

  /**
   * Show a confirmation page after a submission.
   *
   * @param \Drupal\Core\Entity\EntityInterface $submission
   *   The submission that has just been submitted.
   *
   * @return array
   *   A render array for the confirmation page.
   */
  public function confirmationPage(EntityInterface $submission) {
    $content = [];

//    $content['#attached'] = [
//      'library' => [
//        'simplytest_submission/confirmation-page',
//      ],
//    ];

    $content['text'] = ['#markup' => 'All done!!'];

    return $content;
  }

}
