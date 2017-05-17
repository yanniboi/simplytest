<?php

namespace Drupal\simplytest_import\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProjectTypeForm.
 *
 * @package Drupal\simplytest_import\Form
 */
class ProjectTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $simplytest_project_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $simplytest_project_type->label(),
      '#description' => $this->t("Label for the Project type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $simplytest_project_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\simplytest_import\Entity\ProjectType::load',
      ],
      '#disabled' => !$simplytest_project_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $simplytest_project_type = $this->entity;
    $status = $simplytest_project_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Project type.', [
          '%label' => $simplytest_project_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Project type.', [
          '%label' => $simplytest_project_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($simplytest_project_type->urlInfo('collection'));
  }

}
