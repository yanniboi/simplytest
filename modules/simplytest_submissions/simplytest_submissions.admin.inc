<?php

/**
 * @file
 * Submission management functionality.
 *
 * Configuration pages.
 */

/**
 * Submissions configuration form.
 */
function simplytest_submissions_config_form($form = array()) {

  $form['simplytest_submissions_timeout'] = array(
    '#type' => 'textfield',
    '#title' => t('Default submission usage time (minutes)'),
    '#default_value' => variable_get('simplytest_submissions_timeout', 15),
  );

  foreach (user_roles() as $rid => $role) {
    $form['simplytest_submissions_timeout_' . $rid] = array(
      '#type' => 'textfield',
      '#title' => t('Submission usage time: %role', array('%role' => $role)),
      '#default_value' => variable_get('simplytest_submissions_timeout_' . $rid,
        variable_get('simplytest_submissions_timeout', 15)),
    );
  }

  return system_settings_form($form);
}

/**
 * Submission monitor table.
 */
function simplytest_submissions_monitor_table($form, $form_state = array()) {
  // Get list of submissions from database.
  $query = db_select('simplytest_submissions', 'p')
    ->fields('p', array('id', 'state', 'ip', 'timestamp', 'data'))
    ->extend('PagerDefault')
    ->limit(50)
    ->orderBy('number', 'DESC');
  // Apply the filter values if available.
  if (isset($form_state['values']['id']) && !empty($form_state['values']['id'])) {
    $query->condition('id', db_like($form_state['values']['id']), 'LIKE');
  }
  if (isset($form_state['values']['ip']) && !empty($form_state['values']['ip'])) {
    $query->condition('ip', db_like($form_state['values']['ip']), 'LIKE');
  }
  if (isset($form_state['values']['state']) && $form_state['values']['state']) {
    $query->condition('state', $form_state['values']['state']);
  }
  $submissions = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

  // Build rows.
  $rows = array();
  foreach ($submissions as &$submission) {
    // Collect all data.
    $submission = array_merge($submission, unserialize($submission['data']));
    unset($submission['data']);
    // Create rows contents.
    $project_url = ($submission['sandbox']
      ? 'http://drupal.org/sandbox/' . $submission['creator'] . '/' . $submission['project']
      : 'http://drupal.org/project/' . $submission['project']
    );
    $state_text = simplytest_submissions_state_to_string($submission['state']);
    $rows[] = array(
      // Cells.
      'data' => array(
        l($submission['id'], 'goto/' . $submission['id']),
        $state_text,
        check_plain($submission['ip']),
        format_date($submission['timestamp']),
        l(format_string('!title (!version)', array(
          '!title' => $submission['title'],
          '!version' => $submission['version'],
          )), $project_url),
        check_plain($submission['type']),
        ($submission['state'] != SIMPLYTEST_SUBMISSIONS_TERMINATED)
          ? l(t('Terminate'), 'admin/simplytest/submissions/monitor/' . $submission['id'] . '/terminate')
          : '',
        ),
      // Attributes for tr.
      'class' => array(drupal_html_class('simplytest-submissions-' . $state_text)),
    );
  }

  $build = array();
  $build['wrapper'] = array(
    '#type' => 'container',
    '#attributes' => array(
      'id' => 'submission-monitor-table-wrapper',
    ),
  );
  $build['wrapper']['table'] = array(
    '#theme' => 'table',
    '#header' => array(
      'id' => t('Id'),
      'state' => t('State'),
      'ip' => t('IP'),
      'timestamp' => t('Date/Time'),
      'title' => t('Project title'),
      'type' => t('Category'),
      'operations' => t('Operations'),
    ),
    '#rows' => $rows,
    '#empty' => t('No recent submissions.'),
    '#attached' => array(
      'css' => array(
        drupal_get_path('module', 'simplytest_submissions') . '/simplytest_submissions.css',
      ),
    ),
  );
  $build['wrapper']['pager'] = array('#theme' => 'pager');
  return $build;
}

/**
 * Submission monitor form page.
 */
function simplytest_submissions_monitor($form = array(), $form_state) {

  // Filter form.
  $form['filters'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filters'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['filters']['id'] = array(
    '#type' => 'textfield',
    '#description' => t('Submission ID'),
    '#default_value' => '',
    '#size' => 20,
  );
  $form['filters']['ip'] = array(
    '#type' => 'textfield',
    '#description' => t('IP address'),
    '#default_value' => '',
    '#size' => 20,
  );
  $form['filters']['state'] = array(
    '#type' => 'select',
    '#description' => t('Submission state'),
    '#default_value' => NULL,
    '#options' => array(0 => t('- Any -')) + simplytest_submissions_states(),
  );
  $form['filters']['filter_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Filter'),
    '#ajax' => array(
      'callback' => 'simplytest_submissions_monitor_table',
      'wrapper' => 'submission-monitor-table-wrapper',
    ),
  );

  // Build table.
  $form += simplytest_submissions_monitor_table($form, $form_state);
  return $form;
}

/**
 * Confirms and executes an operation on a submission.
 */
function simplytest_submissions_monitor_operation($form, $form_state, $id, $operation) {
  $form['#submission_id'] = $id;

  switch ($operation) {
    case 'terminate':
      $form['#submit'][] = 'simplytest_submissions_monitor_operation_terminate';
      break;
    default:
      return $form;
      break;
  }

  $form = confirm_form(
    $form,
    t('Do you want to @operation %id?', array('@operation' => $operation, '%id' => $id)),
    'admin/simplytest/submissions/monitor'
  );
  return $form;
}

/**
 * Terminates the given submission.
 */
function simplytest_submissions_monitor_operation_terminate($form, $form_state) {
  // Set as terminated in database.
  simplytest_submissions_set_state($form['#submission_id'], SIMPLYTEST_SUBMISSIONS_TERMINATED);
  // @todo connect to server and terminate.
  drupal_goto('admin/simplytest/submissions/monitor');
}
