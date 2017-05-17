<?php

namespace Drupal\simplytest_submission\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\simplytest_submission\SubmissionService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form controller for Submission edit forms.
 *
 * @ingroup simplytest_submission
 */
abstract class SubmissionFormBase extends ContentEntityForm {

  /**
   * The submission service.
   *
   * @var \Drupal\simplytest_submission\SubmissionService
   */
  protected $submissionService;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager, SubmissionService $submission_service) {
    parent::__construct($entity_manager);
    $this->submissionService = $submission_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('simplytest_submission.submission_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$this->submissionService->isActive()) {
      $args = [
        // @todo Write up documentation on Drupal.org or module help page.
        '@link' => Link::fromTextAndUrl('Read more', Url::fromUri('https://github.com/yanniboi/simplytest', [
          'attributes' => ['target' => 'blank_'],
        ]))->toString(),
        '@spawn' => Link::fromTextAndUrl('spawn.sh', Url::fromUri('https://spawn.sh', [
          'attributes' => ['target' => 'blank_'],
        ]))->toString(),
      ];
      drupal_set_message(t('You cannot create a submission without a @spawn token.<br>@link.', $args), 'warning');
      $form['#title'] = $this->t('Missing Configuration');
      $form['message'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('No API token found.'),
      ];
      return $form;
    }

    $form = parent::buildForm($form, $form_state);

    // Allow sub-forms to make fields read-only.
    foreach ($this->getReadonlyFields() as $field => $settings) {
      if (!is_array($settings)) {
        $field = $settings;
        $settings = [];
      }
      $form[$field] = $this->entity->{$field}->view($settings + [
        'label' => 'inline',
        'weight' => isset($form[$field]['#weight']) ? $form[$field]['#weight'] : NULL,
      ]);
    }

    // Allow sub-forms to make fields disabled.
    foreach ($this->getDisabledFields() as $field) {
      $form[$field]['#disabled'] = TRUE;
    }

    // Allow sub-forms to alter the form before groups are applied.
    $this->alterForm($form, $form_state);

    // Apply the fields groups to the form.
    $fieldgroups = $this->buildFieldGroups();
    $this->applyFieldGroups($form, $form_state, $fieldgroups);

    return $form;
  }

  /**
   * Apply changes to the entity form prior to grouping.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    // @todo Do a more generic request query lookup.
    if (isset($_GET['project']) && $_GET['project'] === 'social') {
      $widget = &$form['drupal_projects']['widget'][0];
      $widget['project_identifier']['widget']['0']['value']['#default_value'] = 'social';
      $widget['project_install']['widget']['value']['#default_value'] = FALSE;
    }
  }

  /**
   * Gets a list of the fields that should be rendered as view rather than form.
   *
   * @return array
   *   An array of field names. If the values are strings, they are treated as
   *   the field name. If arrays, the key is the field name and the array is
   *   passed in as the options for EntityViewBuilderInterface::viewField().
   *
   * @see \Drupal\Core\Entity\EntityViewBuilderInterface::viewField()
   */
  public function getReadonlyFields() {
    return [
      'status',
      'container_token',
      'container_url',
      'container_id',
    ];
  }

  /**
   * Gets a list of the fields that should be disabled in the form.
   *
   * @return array
   *   An array of field names. If the values are strings, they are treated as
   *   the field name. If arrays, the key is the field name and the array is
   *   passed in as the options for EntityViewBuilderInterface::viewField().
   *
   * @see \Drupal\Core\Entity\EntityViewBuilderInterface::viewField()
   */
  public function getDisabledFields() {
    return [];
  }

  /**
   * Group form fields in useful sections.
   *
   * @return array
   *   Structured array of field group values and field names.
   */
  public function buildFieldGroups() {
    return [
      'group_drupal' => [
        'title' => $this->t('Drupal'),
        'attributes' => ['class' => ['submission-drupal']],
        'weight' => 1,
        'fields' => [
          "drupal_projects",
        ],
      ],
      'group_webspace' => [
        'title' => $this->t('Webspace'),
        'attributes' => ['class' => ['submission-webspace']],
        'weight' => 2,
        'fields' => [
          "webspace_dbs",
          "webspace_interpreter",
          "webspace_secondary_dbs",
          "webspace_webserver",
        ],
      ],
      'group_instance' => [
        'title' => $this->t('Instance'),
        'attributes' => ['class' => ['submission-instance']],
        'weight' => 3,
        'fields' => [
          "instance_image",
          "instance_runtime",
          "instance_snapshot_cache",
        ],
      ],
    ];
  }

  /**
   * Group form fields in useful sections.
   *
   * @param array $form
   *   Drupal form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Drupal form state.
   * @param array $fieldgroups
   *   Array of field groups to be built.
   * @param string $default_tab
   *   Field group tab id for the tab that should be expanded by default.
   */
  public function applyFieldGroups(array &$form, FormStateInterface $form_state, array $fieldgroups, $default_tab = NULL) {
    $form['submission_tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-group-drupal',
      '#weight' => 1,
    ];

    // Check for query parameter to set the default tab.
    if ($this->getRequest()->query->get('default_tab')) {
      $default_tab = $this->getRequest()->query->get('default_tab');
    }
    if ($default_tab) {
      $form['submission_tabs']['#default_tab'] = $default_tab;
    }

    foreach ($fieldgroups as $key => $fieldgroup) {
      $form[$key] = [
        '#type' => 'details',
        '#title' => $fieldgroup['title'],
        '#weight' => isset($fieldgroup['weight']) ? $fieldgroup['weight'] : 0,
        '#group' => 'submission_tabs',
      ];
      if (isset($fieldgroup['attributes'])) {
        $form[$key]['#attributes'] = $fieldgroup['attributes'];
      }

      foreach ($fieldgroup['fields'] as $field) {
        if (isset($form[$field])) {
          $form[$key][$field] = $form[$field];
          unset($form[$field]);
        }
      }

      if (empty(Element::children($form[$key]))) {
        $form[$key]['#access'] = FALSE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if (empty($this->entity->container_id->value)) {
      $actions['deploy'] = [
        '#type' => 'submit',
        '#value' => $this->t('Deploy Sandbox'),
        '#submit' => ['::submitForm', '::save', '::submitDeploy'],
      ];
      unset($actions['submit']);
    }

    return $actions;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitDeploy(array &$form, FormStateInterface $form_state) {
    // Build script.
    /* @var $service \Drupal\simplytest_submission\SubmissionService */
    $script = $this->submissionService->buildScript($this->entity);
    if ($script) {
      try {
        $data = $this->submissionService->buildInstance($this->entity, $script);

        // Save container ID as title.
        $this->entity->container_id->value = $data['id'];
        $this->entity->container_url->value = $data['url'];
        $this->entity->container_token->value = $data['token'];
        $this->entity->save();
      }
      catch (\Exception $e) {
        // @todo error handling.
      }
    }

    $form_state->setRedirect('entity.simplytest_submission.progress', [
      'simplytest_submission' => $this->entity->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);
    return $entity;
  }

}
