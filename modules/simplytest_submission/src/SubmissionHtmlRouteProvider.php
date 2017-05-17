<?php

namespace Drupal\simplytest_submission;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Submission entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class SubmissionHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);
    $entity_type_id = $entity_type->id();

    if ($progress_route = $this->getProgressRoute($entity_type)) {
      $collection->add("entity.$entity_type_id.progress", $progress_route);
    }

    if ($delete_instance_route = $this->getDeleteInstanceRoute($entity_type)) {
      $collection->add("entity.$entity_type_id.delete_instance", $delete_instance_route);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type) {
    $route = parent::getCanonicalRoute($entity_type);

    $route->setDefaults([
      '_controller' => '\Drupal\simplytest_submission\Controller\SubmissionViewController::view',
      '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
    ]);

    return $route;
  }

  /**
   * Gets the collection route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass()) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route
        ->setDefaults([
          '_entity_list' => $entity_type_id,
          '_title' => "{$entity_type->getLabel()} list",
        ])
        ->setRequirement('_permission', 'administer submission entities')
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the add-form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getAddFormRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('add-form')) {
      $entity_type_id = $entity_type->id();
      $parameters = [
        $entity_type_id => ['type' => 'entity:' . $entity_type_id],
      ];

      $route = new Route($entity_type->getLinkTemplate('add-form'));
      // Content entities with bundles are added via a dedicated controller.
      $route
        ->setDefaults([
          '_controller' => 'Drupal\simplytest_submission\Controller\SubmissionAddController::addForm',
          '_title_callback' => 'Drupal\simplytest_submission\Controller\SubmissionAddController::getAddFormTitle',
        ])
        ->setRequirement('_entity_create_access', $entity_type_id);

      $route
        ->setOption('parameters', $parameters)
        ->setOption('_admin_route', FALSE);

      return $route;
    }
  }

  /**
   * Gets the add page route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    $route = new Route("/submission/add");
    $route
      ->setDefaults([
        '_controller' => 'Drupal\simplytest_submission\Controller\SubmissionAddController::add',
        '_title' => "Add {$entity_type->getLabel()}",
      ])
      ->setRequirement('_entity_create_access', $entity_type->id());

    return $route;
  }

  /**
   * Gets the add page route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getProgressRoute(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $parameters = [
      $entity_type_id => ['type' => 'entity:' . $entity_type_id],
    ];
    $route = new Route("/submission/{simplytest_submission}/progress");
    $route
      ->setDefaults([
        '_controller' => '\Drupal\simplytest_submission\Controller\SimplytestSubmissionController::submissionProgress',
        '_title' => "Submission is being processed",
      ])
      ->setRequirement('_permission', 'create submission entities');

    $route->setOption('parameters', $parameters);
    return $route;
  }

  /**
   * Gets the delete instance page route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getDeleteInstanceRoute(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $parameters = [
      $entity_type_id => ['type' => 'entity:' . $entity_type_id],
    ];
    $route = new Route("/admin/submission/{simplytest_submission}/delete-instance");
    $route
      ->setDefaults([
        '_controller' => '\Drupal\simplytest_submission\Controller\SimplytestSubmissionController::deleteSubmissionInstance',
        '_title' => "Delete Instance",
      ])
      ->setRequirement('_permission', 'administer submission entities');

    $route->setOption('parameters', $parameters)
      ->setOption('_admin_route', TRUE);

    return $route;
  }

}
