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

  $form['submissions_timeout'] = array(
    '#type' => 'fieldset',
    '#title' => t('Submission timout'),
  );
  // Default submission timeout.
  $default = variable_get('simplytest_submissions_timeout', 15);
  $form['submissions_timeout']['simplytest_submissions_timeout'] = array(
    '#type' => 'textfield',
    '#title' => t('Default submission usage time (minutes)'),
    '#default_value' => $default,
    '#field_suffix' => '(' . format_interval($default * 60) . ')',
  );
  // Submission timeouts per role.
  foreach (user_roles() as $rid => $role) {
    $timeout = variable_get('simplytest_submissions_timeout_' . $rid, $default);
    $form['submissions_timeout']['simplytest_submissions_timeout_' . $rid] = array(
      '#type' => 'textfield',
      '#title' => t('Submission usage time: %role', array('%role' => $role)),
      '#default_value' => $timeout,
      '#field_suffix' => '(' . format_interval($timeout * 60) . ')',
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
    ->fields('p', array('id', 'state', 'ip', 'timestamp', 'data', 'server'))
    ->extend('PagerDefault')
    ->limit(50)
    ->orderBy('number', 'DESC');
  // Apply the filter values if available.
  if (!empty($form['#filter']['id'])) {
    $query->condition('id', db_like($form['#filter']['id']) . '%', 'LIKE');
  }
  if (!empty($form['#filter']['ip'])) {
    $query->condition('ip', db_like($form['#filter']['ip']) . '%', 'LIKE');
  }
  if ($form['#filter']['state']) {
    $query->condition('state', $form['#filter']['state']);
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
        l($submission['id'], 'admin/simplytest/submissions/' . $submission['id']),
        $state_text,
        l($submission['server'] . ' ↗', 'goto/' . $submission['id']),
        l(format_string('!title (!version)', array(
          '!title' => $submission['title'],
          '!version' => $submission['version'],
          )), $project_url),
        check_plain($submission['type']),
        ),
      // Attributes for tr.
      'class' => array(drupal_html_class('simplytest-submissions-' . $state_text)),
    );
  }

  $build = array();
  $build['table'] = array(
    '#theme' => 'table',
    '#header' => array(
      'id' => t('Id'),
      'state' => t('State'),
      'server' => t('Server'),
      'title' => t('Project title'),
      'type' => t('Category'),
    ),
    '#rows' => $rows,
    '#empty' => t('No recent submissions.'),
    '#attached' => array(
      'css' => array(
        drupal_get_path('module', 'simplytest_submissions') . '/simplytest_submissions.css',
      ),
    ),
  );
  $build['pager'] = array('#theme' => 'pager');
  return $build;
}

/**
 * Submission monitor form page.
 */
function simplytest_submissions_monitor($form = array(), $form_state) {

  // Get current filter settings.
  $form['#filter'] = variable_get('simplytest_submissions_monitor_filter', array(
    'id' => '',
    'ip' => '',
    'state' => NULL,
  ));

  // Filter form.
  $form['filters'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filters'),
  );
  $form['filters']['id'] = array(
    '#type' => 'textfield',
    '#description' => t('Submission ID'),
    '#default_value' => $form['#filter']['id'],
    '#size' => 20,
  );
  $form['filters']['ip'] = array(
    '#type' => 'textfield',
    '#description' => t('IP address'),
    '#default_value' => $form['#filter']['ip'],
    '#size' => 20,
  );
  $form['filters']['state'] = array(
    '#type' => 'select',
    '#description' => t('Submission state'),
    '#default_value' => $form['#filter']['state'],
    '#options' => array(0 => t('- Any -')) + simplytest_submissions_states(),
  );
  $form['filters']['filter_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Filter'),
  );

  // Build table.
  $form += simplytest_submissions_monitor_table($form, $form_state);
  return $form;
}

/**
 * Submission monitor form page; Submit callback for filter options.
 */
function simplytest_submissions_monitor_submit($form, $form_state) {
  // Save filter settings.
  variable_set('simplytest_submissions_monitor_filter', array(
    'id' => $form_state['values']['id'],
    'ip' => $form_state['values']['ip'],
    'state' => $form_state['values']['state'],
  ));
}

/**
 * Submission detail page.
 */
function simplytest_submissions_view($sid) {
  // Get submissions data.
  $submission = db_select('simplytest_submissions', 'p')
    ->fields('p', array('number', 'id', 'state', 'ip', 'timestamp', 'data', 'server'))
    ->condition('id', $sid)
    ->execute()->fetchObject();
  $submission->data = unserialize($submission->data);
  if (!$submission) {
    return drupal_not_found();
  }

  $state_text = simplytest_submissions_state_to_string($submission->state);

  drupal_set_title(t('Submission: @id', array('@id' => $submission->id)));

  $build = array();
  $rows = array(
    array(
      array(
        'data' => t('Number'),
        'header' => TRUE,
      ),
      '#' . $submission->number,
    ),
    array(
      array(
        'data' => t('Date'),
        'header' => TRUE,
      ),
      format_date($submission->timestamp, 'short'),
    ),
    array(
      array(
        'data' => t('State'),
        'header' => TRUE,
      ),
      $state_text . ' (' . $submission->state . ')',
    ),
    array(
      array(
        'data' => t('User IP'),
        'header' => TRUE,
      ),
      check_plain($submission->ip),
    ),
    array(
      array(
        'data' => t('Server ID'),
        'header' => TRUE,
      ),
      ($submission->server) ? l($submission->server . ' ↗', 'goto/' . $sid) : t('none'),
    ),
    array(
      array(
        'data' => t('Project shortname'),
        'header' => TRUE,
      ),
      l($submission->data['project'], 'http://drupal.org/project/' . $submission->data['project']),
    ),
    array(
      array(
        'data' => t('Project title'),
        'header' => TRUE,
      ),
      check_plain($submission->data['title']),
    ),
    array(
      array(
        'data' => t('Project type'),
        'header' => TRUE,
      ),
      check_plain($submission->data['type']),
    ),
    array(
      array(
        'data' => t('Project version'),
        'header' => TRUE,
      ),
      check_plain($submission->data['version']),
    ),
    array(
      array(
        'data' => t('Duration'),
        'header' => TRUE,
      ),
      format_interval($submission->data['timeout'] * 60),
    ),
  );

  if (isset($submission->data['additionals'])) {
    foreach ($submission->data['additionals'] as $id => $additional) {
      $rows[] = array(
        array(
          'data' => t('Additional project #@n', array('@n' => $id)),
          'header' => TRUE,
        ),
        l($additional, 'http://drupal.org/project/' . $additional),
      );
    }
  }

  if (isset($submission->data['patches'])) {
    foreach ($submission->data['patches'] as $id => $patch) {
      $rows[] = array(
        array(
          'data' => t('Patch #@n', array('@n' => $id)),
          'header' => TRUE,
        ),
        l($patch, $patch),
      );
    }
  }

  $build['submission_table'] = array(
    '#theme' => 'table',
    '#rows' => $rows,
  );
  return $build;
}
