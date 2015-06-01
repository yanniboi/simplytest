<?php

/**
 * @file
 * Worker server management.
 *
 * Configuration page.
 */

/**
 * Server configuration form.
 */
function simplytest_servers_overview_form($form = array()) {

  // Get configured servers.
  $servers = simplytest_servers_get();

  // Build rows.
  $rows = array();
  foreach($servers as $server) {
    $rows[] = array(
      $server['id'],
      $server['name'],
      $server['host'],
      $server['fullslots'] . ' / ' . $server['slots'],
      '<strong>' . (($server['active']) ? t('Active') : t('Inactive')) . '</strong>',
      ($server['active'])
        ? l(t('Deactivate'), 'admin/simplytest/servers/' . $server['id'] . '/deactivate')
        : l(t('Activate'), 'admin/simplytest/servers/' . $server['id'] . '/activate'),
      l(t('Edit'), 'admin/simplytest/servers/' . $server['id'] . '/edit'),
      l(t('Delete'), 'admin/simplytest/servers/' . $server['id'] . '/delete'),
    );
  }

  // Build table.
  $form['table'] = array(
    '#theme' => 'table',
    '#header' => array(
      'id' => t('Id'),
      'name' => t('Name'),
      'host' => t('Host'),
      'slots' => t('Slots'),
      'active' => t('Status'),
      'operations' => array('data' => t('Operations'), 'colspan' => 3),
    ),
    '#rows' => $rows,
    '#empty' => t('No servers configured.'),
  );
  return $form;
}

/**
 * Server specific configuration.
 */
function simplytest_servers_config_form($form = array(), $form_state, $id) {
  $servers = simplytest_servers_get();
  $form['#servers'] = $servers;

  // No server id given, use default config.
  if (!$id) {
    $config = array(
      'id' => NULL,
      'active' => FALSE,
      'name' => 'service.com',
      'protocol' => 'http://',
      'host' => 'node-01.service.com',
      'port' => 22,
      'user' => '',
      'password' => '',
      'slots' => 0,
      'spawn' => '',
    );
  }
  // Server id given, get server data.
  else {
    if (isset($servers[$id])) {
      $config = $servers[$id];
    }
    else {
      return drupal_page_not_found();
    }
  }

  // Build form.
  $form['id'] = array(
    '#type' => 'textfield',
    '#title' => t('Id'),
    '#description' => t('This is going to be used as prefix for submission ids. Should me a short or single letter and a valid first character of a unix username.'),
    '#disabled' => ($config['id']) ? TRUE : FALSE,
    '#required' => TRUE,
    '#default_value' => $config['id'],
  );
  $form['active'] = array(
    '#type' => 'checkbox',
    '#title' => t('Active'),
    '#default_value' => $config['active'],
  );
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Domain name suffix'),
    '#description' => t('Will be used as domain suffix for the sandbox address. Structure: [server-id][submission-id].[domain-name]. Example: [a][001].[simplytest.me]'),
    '#required' => TRUE,
    '#default_value' => $config['name'],
  );
  $form['protocol'] = array(
    '#type' => 'textfield',
    '#title' => t('Protocol for sandbox URL'),
    '#required' => TRUE,
    '#default_value' => $config['protocol'],
  );
  $form['slots'] = array(
    '#type' => 'textfield',
    '#title' => t('Slots'),
    '#description' => t('How many sandboxes can be launched on this server simultaneously.'),
    '#required' => TRUE,
    '#default_value' => $config['slots'],
  );
  $form['host'] = array(
    '#type' => 'textfield',
    '#title' => t('Server hostname to SSH into'),
    '#required' => TRUE,
    '#default_value' => $config['host'],
  );
  $form['port'] = array(
    '#type' => 'textfield',
    '#title' => t('SSH port'),
    '#required' => TRUE,
    '#default_value' => $config['port'],
  );
  $form['user'] = array(
    '#type' => 'textfield',
    '#title' => t('SSH User'),
    '#required' => TRUE,
    '#default_value' => $config['user'],
  );
  $form['password'] = array(
    '#type' => 'textfield',
    '#title' => t('SSH Password'),
    '#required' => FALSE,
    '#default_value' => $config['password'],
  );
  $form['spawn'] = array(
    '#type' => 'textfield',
    '#title' => t('SSH command'),
    '#required' => TRUE,
    '#default_value' => $config['spawn'],
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Server configuration form, validation handler.
 */
function simplytest_servers_config_form_validate($form, &$form_state) {
  if (!is_numeric($form_state['values']['port'])) {
    form_set_error('port', t('Port must be a number.'));
  }
  if (!is_numeric($form_state['values']['slots'])) {
    form_set_error('slots', t('Slot number must be numeric.'));
  }
}

/**
 * Server configuration form, submit handler.
 */
function simplytest_servers_config_form_submit($form, &$form_state) {
  $servers = $form['#servers'];

  // Collect submitted server data.
  $server = array(
    'id' => $form_state['values']['id'],
    'active' => $form_state['values']['active'],
    'name' => $form_state['values']['name'],
    'protocol' => $form_state['values']['protocol'],
    'slots' => $form_state['values']['slots'],
    'host' => $form_state['values']['host'],
    'port' => $form_state['values']['port'],
    'user' => $form_state['values']['user'],
    'password' => $form_state['values']['password'],
    'spawn' => $form_state['values']['spawn'],
  );

  // Save the server configuration.
  $servers[$form_state['values']['id']] = $server;
  simplytest_servers_set($servers);

  drupal_set_message(t('Updated configuration successfully!'));
  drupal_goto('admin/simplytest/servers');
}

/**
 * Confirms and executes an operation on a server.
 */
function simplytest_servers_operation($form, $form_state, $id, $operation) {
  $form['#server_id'] = $id;

  switch ($operation) {
    case 'activate':
      $form['#submit'][] = 'simplytest_servers_operation_activate';
      break;
    case 'deactivate':
      $form['#submit'][] = 'simplytest_servers_operation_deactivate';
      break;
    case 'delete':
      $form['#submit'][] = 'simplytest_servers_operation_delete';
      break;
    default:
      return $form;
      break;
  }

  $form = confirm_form(
    $form,
    t('Do you want to @operation server %id?', array('@operation' => $operation, '%id' => $id)),
    'admin/simplytest/servers'
  );
  return $form;
}

/**
 * Confirmed operation: Activate a server.
 */
function simplytest_servers_operation_activate($form, $form_state) {
  $id = $form['#server_id'];
  // Get the server.
  $servers = simplytest_servers_get();
  if (isset($servers[$id])) {
    $servers[$id]['active'] = TRUE;
    simplytest_servers_set($servers);
  }
  drupal_goto('admin/simplytest/servers');
}

/**
 * Confirmed operation: Deactivate a server.
 */
function simplytest_servers_operation_deactivate($form, $form_state) {
  $id = $form['#server_id'];
  // Get the server.
  $servers = simplytest_servers_get();
  if (isset($servers[$id])) {
    $servers[$id]['active'] = FALSE;
    simplytest_servers_set($servers);
  }
  drupal_goto('admin/simplytest/servers');
}

/**
 * Confirmed operation: Delete a server.
 */
function simplytest_servers_operation_delete($form, $form_state) {
  $id = $form['#server_id'];
  // Get the server.
  $servers = simplytest_servers_get();
  if (isset($servers[$id])) {
    unset($servers[$id]);
    simplytest_servers_set($servers);
  }
  drupal_goto('admin/simplytest/servers');
}
