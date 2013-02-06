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
  $form['submissions_timeout']['simplytest_submissions_timeout'] = array(
    '#type' => 'textfield',
    '#title' => t('Default submission usage time (minutes)'),
    '#default_value' => variable_get('simplytest_submissions_timeout', 15),
  );
  // Submission timeouts per role.
  foreach (user_roles() as $rid => $role) {
    $form['submissions_timeout']['simplytest_submissions_timeout_' . $rid] = array(
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
        l($submission['id'], 'goto/' . $submission['id']),
        $state_text,
        '<em>' . $submission['server'] . '</em>',
        check_plain($submission['ip']),
        format_date($submission['timestamp']),
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
      'ip' => t('IP'),
      'timestamp' => t('Date/Time'),
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
