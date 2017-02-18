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

    if ($confirmation_page_route = $this->getConfirmationPageRoute($entity_type)) {
      $collection->add("$entity_type_id.confirmation_page", $confirmation_page_route);
    }

    if ($manage_form_route = $this->getManageFormRoute($entity_type)) {
      $collection->add("entity.$entity_type_id.manage_form", $manage_form_route);
    }

    if ($autocomplete_route = $this->getAutocompleteRoute($entity_type)) {
      $collection->add("entity.$entity_type_id.ttd_autocomplete", $autocomplete_route);
    }

    return $collection;
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
        ->setRequirement('_permission', 'view submission entities')
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
        ->setOption('_admin_route', TRUE);

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
  protected function getConfirmationPageRoute(EntityTypeInterface $entity_type) {
    return;
    $route = new Route("/submission/{simplytest_submission}/confirm");
    $route
      ->setDefaults([
        '_controller' => 'Drupal\simplytest_submission\Controller\SubmissionAddController::confirmationPage',
        '_title' => "Thank your for your submission",
      ])
      ->setRequirement('_entity_create_access', $entity_type->id());

    return $route;
  }

  /**
   * Gets the manage form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route
   *   The generated route.
   */
  protected function getManageFormRoute(EntityTypeInterface $entity_type) {
    $route = new Route("/admin/delay-check/submission/{simplytest_submission}/manage");
    $route
      ->setDefaults([
        '_entity_form' => 'simplytest_submission.processing',
        '_title' => "Manage Submission - Staff",
      ])
      ->setRequirement('_permission', 'manage submission entities')
      ->setOption('_admin_route', TRUE);

    return $route;
  }
  
  /**
   * Gets the manage form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route
   *   The generated route.
   */
  protected function getAutocompleteRoute(EntityTypeInterface $entity_type) {
    return;
    $route = new Route("ttd_autocomplete/{target_type}");
    $route
      ->setDefaults([
        '_controller' => '\Drupal\simplytest_submission\Controller\TtdAutocompleteController::handleAutocomplete',
      ])
      ->setRequirement('_access', 'TRUE');

    return $route;
  }

}
