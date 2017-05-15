<?php

namespace Drupal\simplytest_submission\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\simplytest_submission\SubmissionInterface;

/**
 * Returns responses for Node routes.
 */
class SimplytestSubmissionController extends ControllerBase implements ContainerInjectionInterface {

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
            'percent' => 30,
            'service_url' => SubmissionInterface::SERVICE_URL,
          ],
        ],
      ],
    ];

    $page['debug'] = [
      '#type' => 'details',
      '#title' => $this->t('Log output'),
      '#open' => FALSE,
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

    $page['action'] = [
      '#type' => 'button',
      '#attributes' => ['id' => ['simplytest_submission_submit']],
      '#value' => $this->t('Go to Site'),
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
