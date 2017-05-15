<?php

namespace Drupal\simplytest_submission\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simplytest_submission\SubmissionInterface;
use Drupal\simplytest_submission\SubmissionService;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to render a single submission entity.
 */
class SubmissionViewController extends EntityViewController {

  use StringTranslationTrait;

  /**
   * The submission service.
   *
   * @var \Drupal\simplytest_submission\SubmissionService
   */
  protected $submissionService;

  /**
   * Creates an NodeViewController object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\simplytest_submission\SubmissionService $submission_service
   *   The submission service.
   */
  public function __construct(EntityManagerInterface $entity_manager, RendererInterface $renderer, SubmissionService $submission_service) {
    parent::__construct($entity_manager, $renderer);
    $this->submissionService = $submission_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('renderer'),
      $container->get('simplytest_submission.submission_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $simplytest_submission, $view_mode = 'full') {
    $page = parent::view($simplytest_submission, $view_mode);

    // Only alter the full entity view.
    if ($view_mode != 'full') {
      return $page;
    }

    $script = $this->submissionService->buildScript($simplytest_submission);

    $page['script'] = [
      '#type' => 'details',
      '#title' => $this->t('Build script'),
      'processed' => [
        '#type' => 'html_tag',
        '#tag' => 'h5',
        '#value' => $this->t('Current script:'),
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'pre',
        '#value' => $script,
      ],
    ];

    $url = SubmissionInterface::SERVICE_URL;
    $url .= '/' . $simplytest_submission->container_id->value;
    $url .= '?token=' . $simplytest_submission->container_token->value;
    $client = \Drupal::httpClient();

    try {
      $request = $client->get($url);
      $response = $request->getBody();
      $contents = $response->getContents();
    }
    catch (RequestException $e) {
      watchdog_exception('my_module', $e);
      $contents = $e->getMessage();
    }

    $page['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Log output'),
      'processed' => [
        '#type' => 'html_tag',
        '#tag' => 'h5',
        '#value' => $this->t('Response from build server:'),
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'pre',
        '#value' => $contents,
      ],
    ];

    return $page;
  }

}
