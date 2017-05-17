<?php

namespace Drupal\simplytest_import\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\simplytest_import\ProjectInterface;

/**
 * Defines the Project entity.
 *
 * @ingroup simplytest_import
 *
 * @ContentEntityType(
 *   id = "simplytest_project",
 *   label = @Translation("Project"),
 *   bundle_label = @Translation("Project type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simplytest_import\ProjectListBuilder",
 *     "views_data" = "Drupal\simplytest_import\Entity\ProjectViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *   },
 *   base_table = "simplytest_project",
 *   admin_permission = "administer project entities",
 *   entity_keys = {
 *     "id" = "shortname",
 *     "bundle" = "source",
 *     "label" = "name"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/project/{simplytest_project}",
 *     "add-form" = "/admin/structure/project/add/{simplytest_project_type}",
 *     "edit-form" = "/admin/structure/project/{simplytest_project}/edit",
 *     "delete-form" = "/admin/structure/project/{simplytest_project}/delete",
 *     "collection" = "/admin/structure/project"
 *   },
 *   bundle_entity_type = "simplytest_project_type",
 *   field_ui_base_route = "entity.simplytest_project_type.edit_form"
 * )
 */
class Project extends ContentEntityBase implements ProjectInterface {

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['shortname'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Shortname'))
      ->setDescription(t('The shortname/id of the Project entity.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Project entity.'))
      ->setSettings(array(
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => 1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    
    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of Drupal project.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          ProjectInterface::SIMPLYTEST_PROJECTS_TYPE_CORE => 'Drupal core',
          ProjectInterface::SIMPLYTEST_PROJECTS_TYPE_MODULE => 'Module',
          ProjectInterface::SIMPLYTEST_PROJECTS_TYPE_THEME => 'Theme',
          ProjectInterface::SIMPLYTEST_PROJECTS_TYPE_DISTRO => 'Distribution',
        ],
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Source'))
      ->setDescription(t('The Project origin (bundle).'))
      ->setSetting('target_type', 'simplytest_project_type')
      ->setRequired(TRUE);

    $fields['sandbox'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sandbox'))
      ->setDescription(t('Is this a sandbox project?'))
      ->setDefaultValue(FALSE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'boolean',
        'weight' => 3,
      ))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['creator'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Creator'))
      ->setDescription(t('The name of the Project owner.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'string',
        'weight' => 4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    return $fields;
  }

}
