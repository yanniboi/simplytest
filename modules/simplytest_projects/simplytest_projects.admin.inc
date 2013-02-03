<?php

/**
 * @file
 * Handles drupal.org project data.
 *
 * Configuration pages.
 */

/**
 * Configuration form.
 */
function simplytest_projects_settings_form($form = array()) {

  $form['simplytest_projects_version_timeout'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum age of version data'),
    '#default_value' => variable_get('simplytest_projects_version_timeout', '-1 hour'),
    '#description' => t('Example: %example', array('%example' => '-1 hour')),
  );

  $form['simplytest_projects_blacklisted_projects'] = array(
    '#type' => 'textarea',
    '#title' => t('Blacklisted project'),
    '#description' => t('A list of project shortnames to disable.'),
    '#default_value' => implode(PHP_EOL, variable_get('simplytest_projects_blacklisted_projects', array())),
  );

  $form['simplytest_projects_blacklisted_versions'] = array(
    '#type' => 'textarea',
    '#title' => t('Blacklisted versions'),
    '#description' => t('A list of regular expressions for versions to disable.'),
    '#default_value' => implode(PHP_EOL, variable_get('simplytest_projects_blacklisted_versions', array())),
  );

  $form = system_settings_form($form);

  // We need to call our own submit callback first, not the one from
  // system_settings_form(), so that we can process and save the blacklists.
  unset($form['#submit']);
  
  $form['actions']['clear_version_cache'] = array(
    '#type' => 'submit',
    '#value' => t('Save and flush version cache'),
  );

  return $form;
}

/**
 * Configuration form submit handler.
 */
function simplytest_projects_settings_form_submit($form, $form_state) {
  // Save blacklists.
  $blacklist_projects = explode(PHP_EOL, trim($form_state['values']['simplytest_projects_blacklisted_projects']));
  $blacklist_versions = explode(PHP_EOL, trim($form_state['values']['simplytest_projects_blacklisted_versions']));
  foreach ($blacklist_projects as $key => &$project) {
    $project = trim($project);
    if (empty($project)) {
      unset($blacklist_projects[$key]);
    }
  }
  foreach ($blacklist_versions as $key => &$version) {
    $version = trim($version);
    if (empty($version)) {
      unset($blacklist_versions[$key]);
    }
  }
  variable_set('simplytest_projects_blacklisted_projects', $blacklist_projects);
  variable_set('simplytest_projects_blacklisted_versions', $blacklist_versions);
  unset($form_state['values']['simplytest_projects_blacklisted_projects']);
  unset($form_state['values']['simplytest_projects_blacklisted_versions']);

  // Flush (obsolete) the version cache if desired.
  if ($form_state['values']['op'] == t('Save and flush version cache')) {
    db_update('simplytest_projects')
      ->fields(array(
        'timestamp' => 0,
      ))
      ->execute();
  }

  // Save the rest like using system_settings_form() normaly.
  system_settings_form_submit($form, $form_state);
}

/**
 * Project list management page.
 */
function simplytest_projects_management_form($form = array(), $form_state) {
  // To keep table-sort working we need to use the GET method.
  $filter_string = isset($_GET['filter']) ? trim($_GET['filter']) : '';
  $form['#method'] = 'GET';

  // Filter form.
  $form['filters'] = array(
    '#type' => 'container',
    '#attributes' => array(
      'class' => array('container-inline'),
    ),
  );
  $form['filters']['filter'] = array(
    '#type' => 'textfield',
    '#default_value' => $filter_string,
  );
  $form['filters']['filter_submit'] = array(
    '#type' => 'submit',
    '#value' => t('Filter'),
  );

  // Table header.
  $header = array(
    array('data' => t('Title'), 'field' => 'title'),
    array('data' => t('Shortname'), 'field' => 'shortname'),
    array('data' => t('Type'), 'field' => 'type'),
    array('data' => t('Version cache'), 'field' => 'timestamp'),
    array('data' => t('Operations'), 'colspan' => 2),
  );

  // Get project data.
  $query = db_select('simplytest_projects', 'p')
    ->fields('p', array('title', 'shortname', 'type', 'timestamp', 'sandbox', 'creator'))
    ->extend('PagerDefault')
      ->limit(50)
    ->extend('TableSort')
      ->orderByHeader($header);
  // Filter by title and shortname.
  if (!empty($filter_string)) {
    $db_or = db_or()
      ->condition('title', '%' . db_like($filter_string) . '%', 'LIKE')
      ->condition('shortname', '%' . db_like($filter_string) . '%', 'LIKE');
    $query->condition($db_or);
  }
  $projects = $query->execute()->fetchAll();

  // Build rows.
  $rows = array();
  foreach ($projects as &$project) {
    // Create rows contents.
    $project_url = ($project->sandbox
      ? SIMPLYTEST_PROJECTS_DRUPAL_ORG . 'sandbox/' . $project->creator . '/' . $project->shortname
      : SIMPLYTEST_PROJECTS_DRUPAL_ORG . 'project/' . $project->shortname
    );
    $cache_link = ($project->timestamp
      ? l(format_date($project->timestamp), 'admin/simplytest/projects/' . $project->shortname . '/versions')
      : t('none')
    );
    $rows[] = array(
      'data' => array(
        l($project->title, $project_url),
        $project->shortname,
        $project->type,
        $cache_link,
        l(t('edit'), 'admin/simplytest/projects/' . $project->shortname . '/edit'),
        l(t('delete'), 'admin/simplytest/projects/' . $project->shortname . '/delete'),
      ),
    );
  }

  // Build table.
  $form['table'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('No projects in database.'),
  );
  $form['pager'] = array('#theme' => 'pager');
  return $form;
}

/**
 * Project cached version listing.
 */
function simplytest_projects_version_list_form($form, $form_state, $shortname) {
  // Get versions.
  $result = db_select('simplytest_projects', 'p')
    ->fields('p', array('versions'))
    ->condition('shortname', $shortname)
    ->execute()->fetchField();

  $versions = array('tags' => array(), 'heads' => array());
  if ($result) {
    $versions = unserialize($result);
  }
  $row_tags = array();
  foreach ($versions['tags'] as $tag) {
    $row_tags[] = array($tag);
  }
  $row_heads = array();
  foreach ($versions['heads'] as $head) {
    $row_heads[] = array($head);
  }

  // Build table.
  $build = array();
  $build['table_tags'] = array(
    '#theme' => 'table',
    '#header' => array(t('Tags')),
    '#rows' => $row_tags,
    '#empty' => t('Project has no tags.'),
  );
  $build['table_heads'] = array(
    '#theme' => 'table',
    '#header' => array(t('Heads')),
    '#rows' => $row_heads,
    '#empty' => t('Project has no heads.'),
  );
  return $build;
}

/**
 * Project cached version listing.
 */
function simplytest_projects_edit_form($form = array(), $form_state, $shortname) {
  $project = db_select('simplytest_projects', 'p')
    ->fields('p', array('title', 'shortname', 'type', 'sandbox', 'creator'))
    ->condition('shortname', $shortname)
    ->execute()->fetchObject();
  if (!$project) {
    return drupal_access_denied();
  }

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => $project->title,
  );
  $form['shortname'] = array(
    '#type' => 'textfield',
    '#title' => t('Shortname'),
    '#default_value' => $project->shortname,
    '#disabled' => TRUE,
  );
  $form['creator'] = array(
    '#type' => 'textfield',
    '#title' => t('Creator'),
    '#default_value' => $project->creator,
  );
  $form['type'] = array(
    '#type' => 'select',
    '#title' => t('Type'),
    '#default_value' => $project->type,
    '#options' => array(
      'Drupal core' => t('Drupal core'),
      'Module' => t('Module'),
      'Theme' => t('Theme'),
      'Distribution' => t('Distribution'),
    ),
  );
  $form['sandbox'] = array(
    '#type' => 'checkbox',
    '#title' => 'Sandbox project',
    '#default_value' => $project->sandbox,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Project cached version listing; Submit callback.
 */
function simplytest_projects_edit_form_submit($form, $form_state) {
  $values = $form_state['values'];
  db_update('simplytest_projects')
    ->fields(array(
      'title' => $values['title'],
      'creator' => $values['creator'],
      'type' => $values['type'],
      'sandbox' => $values['sandbox'],
    ))
    ->condition('shortname', $values['shortname'])
    ->execute();
  drupal_set_message(t('Project entry updated!'));
}

/**
 * Confirms and executes an operation on a submission.
 */
function simplytest_projects_operation($form, $form_state, $shortname, $operation) {
  $form['#shortname'] = $shortname;

  switch ($operation) {
    case 'delete':
      $form['#submit'][] = 'simplytest_projects_operation_delete';
      break;
    default:
      return $form;
      break;
  }

  $form = confirm_form(
    $form,
    t('Do you want to @operation %shortname?', array('@operation' => $operation, '%shortname' => $shortname)),
    'admin/simplytest/projects'
  );
  return $form;
}

/**
 * Deletes the given project entry.
 */
function simplytest_projects_operation_delete($form, $form_state) {
  // Delete the entry.
  db_delete('simplytest_projects')
    ->condition('shortname', $form['#shortname'])
    ->execute();
  drupal_goto('admin/simplytest/projects');
}
