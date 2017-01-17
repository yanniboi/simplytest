<?php

namespace Drupal\simplytest_submission\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Controller\NodeViewController;

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
   * Displays a node.
   *
   * @todo non-js support
   *
   * @param int $node_id
   *   The node ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function progress(EntityInterface $node) {
    $page = [
      '#type' => 'page',
      '#show_messages' => TRUE,
      '#title' => $node->title->value,
      'content' => array(
        '#theme' => 'progress_bar',
        '#percent' => 30, // @todo actual progress
        '#message' => array('#markup' => '<pre id="simplytest_submission_progress"></pre>'), // @todo clean solution // @todo print current task
        '#label' => t('Launching @node', array('@node' => $node->title->value)),
        '#attached' => array(
          'library' => [ 'simplytest_submission/progress' ],
          'html_head' => array(
            array(
              array(
                // Redirect through a 'Refresh' meta tag if JavaScript is disabled.
                // @todo necessary?
                '#tag' => 'meta',
                '#noscript' => TRUE,
                '#attributes' => array(
                  'http-equiv' => 'Refresh',
                  'content' => '1; URL=.',
                ),
              ),
              'batch_progress_meta_refresh',
            ),
          ),
          // Adds JavaScript code and settings for clients where JavaScript is enabled.
          'drupalSettings' => [
            'simplytest_submission' => [
              'container_id' => $node->title->value,
              'container_token' => $node->field_container_token->value,
              'container_url' => $node->field_container_url->value,
              'service_url' => SIMPLYTEST_SUBMISSION_SERVICE_URL,
            ],
          ],
        ),
      ),
    ];
    return $page;
  }
}
