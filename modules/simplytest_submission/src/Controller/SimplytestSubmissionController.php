<?php

namespace Drupal\simplytest_submission\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\simplytest_submission\SubmissionInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class SimplytestSubmissionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a SimplytestSubmissionController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Displays deployment progress.
   *
   * @param \Drupal\simplytest_submission\SubmissionInterface $simplytest_submission
   *   The submission being deployed.
   *
   * @return array
   *   Drupal render array.
   */
  public function submissionProgress(SubmissionInterface $simplytest_submission) {
    $page = [];
    $page['progress'] = [
      '#theme' => 'progress_bar',
      // @todo actual progress
      '#percent' => 30,
      '#label' => t('Launching @name', ['@name' => $simplytest_submission->getName()]),
      '#attached' => [
        'library' => ['simplytest_submission/progress'],
        'html_head' => [
          [
            [
              // Redirect through a 'Refresh' meta tag if JS is disabled.
              // @todo necessary?
              '#tag' => 'meta',
              '#noscript' => TRUE,
              '#attributes' => [
                'http-equiv' => 'Refresh',
                'content' => '1; URL=.',
              ],
            ],
            'batch_progress_meta_refresh',
          ],
        ],
        // Adds code and settings for clients where JavaScript is enabled.
        'drupalSettings' => [
          'simplytest_submission' => [
            'container_id' => $simplytest_submission->container_id->value,
            'container_token' => $simplytest_submission->container_token->value,
            'container_url' => $simplytest_submission->container_url->value,
            'service_url' => SubmissionInterface::SERVICE_URL,
          ],
        ],
      ],
    ];

    $page['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Log output'),
      '#open' => TRUE,
      'processed' => [
        '#type' => 'html_tag',
        '#tag' => 'h5',
        '#value' => $this->t('Response from build server:'),
      ],
      'auto_scroll' => [
        '#type' => 'checkbox',
        '#title' => 'Auto-Scroll',
        '#default_value' => 1,
        '#attributes' => [
          'id' => [
            'simplytest_submission_autoscroll',
          ],
          'checked' => 'checked',
        ],
      ],
      'auto_redirect' => [
        '#type' => 'checkbox',
        '#title' => 'Redirect on completion',
        '#default_value' => 0,
        '#attributes' => [
          'id' => [
            'simplytest_submission_redirect',
          ],
        ],
      ],
      'message' => [
        '#type' => 'html_tag',
        '#tag' => 'pre',
        '#attributes' => ['id' => ['simplytest_submission_progress']],
        '#value' => '',
      ],
    ];

    return $page;
  }

  /**
   * Page callback for a submission instance status.
   *
   * @param \Drupal\simplytest_submission\SubmissionInterface $simplytest_submission
   *   Submission entity to get instance data from.
   *
   * @return array
   *   Render array.
   */
  public function submissionStatus(SubmissionInterface $simplytest_submission) {
    $page = [];

    if (!$simplytest_submission->container_id->value || !$simplytest_submission->container_token->value) {
      return ['#markup' => 'Missing data'];
    }

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
      return ['#markup' => $e->getMessage()];
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

  /**
   * Page callback for a page to delete submission instance.
   *
   * @param \Drupal\simplytest_submission\SubmissionInterface $simplytest_submission
   *   Submission entity to get instance from.
   *
   * @return array
   *   Render array.
   */
  public function deleteSubmissionInstance(SubmissionInterface $simplytest_submission) {
    /* @var $action \Drupal\simplytest_submission\Plugin\Action\DeleteSubmission */
    $action = \Drupal::service('plugin.manager.action')->createInstance('simplytest_submission_delete_submission_action');
    $response = $action->execute($simplytest_submission);

    if ($response) {
      return ['#markup' => $response];
    }

    return ['#markup' => 'Something went wrong.'];
  }

}
