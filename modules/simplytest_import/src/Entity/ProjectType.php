<?php

namespace Drupal\simplytest_import\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\simplytest_import\ProjectTypeInterface;

/**
 * Defines the Project type entity.
 *
 * @ConfigEntityType(
 *   id = "simplytest_project_type",
 *   label = @Translation("Project type"),
 *   handlers = {
 *     "list_builder" = "Drupal\simplytest_import\ProjectTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\simplytest_import\Form\ProjectTypeForm",
 *       "edit" = "Drupal\simplytest_import\Form\ProjectTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "simplytest_project_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "simplytest_project",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/project-type/add",
 *     "edit-form" = "/admin/structure/project-type/{simplytest_project_type}/edit",
 *     "delete-form" = "/admin/structure/project-type/{simplytest_project_type}/delete",
 *     "collection" = "/admin/structure/project-type"
 *   }
 * )
 */
class ProjectType extends ConfigEntityBundleBase implements ProjectTypeInterface {

  /**
   * The Project type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Project type label.
   *
   * @var string
   */
  protected $label;

}
