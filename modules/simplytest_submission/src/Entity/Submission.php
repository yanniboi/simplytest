<?php

namespace Drupal\simplytest_submission\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\simplytest_submission\SubmissionInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Submission entity.
 *
 * @ingroup simplytest
 *
 * @ContentEntityType(
 *   id = "simplytest_submission",
 *   label = @Translation("Submission"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simplytest_submission\SubmissionListBuilder",
 *     "views_data" = "Drupal\simplytest_submission\Entity\SubmissionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "add" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "edit" = "Drupal\simplytest_submission\Form\SubmissionForm",
 *       "delete" = "Drupal\simplytest_submission\Form\SubmissionDeleteForm",
 *     },
 *     "access" = "Drupal\simplytest_submission\SubmissionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\simplytest_submission\SubmissionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "simplytest_submission",
 *   admin_permission = "administer submission entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/admin/submission/{simplytest_submission}",
 *     "add-form" = "/admin/submission/add",
 *     "edit-form" = "/admin/submission/{simplytest_submission}/edit",
 *     "manage-form" = "/admin/submission/{simplytest_submission}/manage",
 *     "delete-form" = "/admin/submission/{simplytest_submission}/delete",
 *     "collection" = "/admin/submission",
 *   },
 *   field_ui_base_route = "entity.simplytest_submission.collection"
 * )
 */
class Submission extends ContentEntityBase implements SubmissionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'Submission ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Submission entity.'))
      ->setReadOnly(TRUE);

    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Submission status'))
      ->setDescription(t('The current status of the Submission entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values_function' => 'simplytest_submission_status_options',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue('received');

    $fields['container_token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container Token'))
      ->setDescription(t(''))
      ->setReadOnly(TRUE);

    $fields['container_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Container Url'))
      ->setDescription(t(''))
      ->setReadOnly(TRUE);
    
//    $fields['drupal_projects'] = BaseFieldDefinition::create('field_collection')
//      ->setLabel(t('Drupal Projects'))
//      ->setDescription(t(''));
//
//    $fields['instance_image'] = BaseFieldDefinition::create('string')
//      ->setLabel(t('Instance Image'))
//      ->setDescription(t(''));
//
//    $fields['instance_runtime'] = BaseFieldDefinition::create('string')
//      ->setLabel(t('Runtime'))
//      ->setDescription(t(''));
//
//    $fields['instance_snapshot_cache'] = BaseFieldDefinition::create('string')
//      ->setLabel(t('Use snapshot cache'))
//      ->setDescription(t(''));

    $fields['webspace_dbs'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Database'))
      ->setDescription(t(''))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mysql' => 'mysql',
          'mariadb' => 'mariadb',
          'sqllite' => 'sqllite',
          'postgresql' => 'postgresql',
        ]
      ])
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);


    $fields['webspace_interpreter'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Interpreter'))
      ->setDescription(t(''))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mod-php7' => 'mod-php7',
          'php7-fpm' => 'php7-fpm',
          'php7-cgi' => 'php7-cgi',
        ]
      ])
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);


    $fields['webspace_secondary_dbs'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Secondary Database'))
      ->setDescription(t(''))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'mongodb' => 'mongodb',
          'elasticsearch' => 'elasticsearch',
          'redis' => 'redis',
        ]
      ])
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['webspace_webserver'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Webserver'))
      ->setDescription(t(''))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => [
          'nginx' => 'nginx',
          'apache2' => 'apache2',
        ]
      ])
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Get the submission status options.
   *
   * @return array
   *   An array of status labels keyed by value.
   */
  public static function getStatusOptions() {
    $statuses = &drupal_static(__METHOD__);
    if (!isset($statuses)) {
      // @todo Add statuses.
      $statuses = [];
    }
    return $statuses;
  }

}
