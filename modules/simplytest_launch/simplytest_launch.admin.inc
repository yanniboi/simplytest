<?php

/**
 * @file
 * Provides a submission launcher block for simplytest.me.
 *
 * Configuration pages.
 */

/**
 * Flood settings configuration form.
 */
function simplytest_launch_flood_form($form = array()) { 

  $form['flood_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Flood protection settings'),
  );
  $form['flood_settings']['simplytest_launch_flood_window'] = array(
    '#type' => 'textfield',
    '#title' => t('Flood window in seconds'),
    '#default_value' => variable_get('simplytest_launch_flood_window', 900),
    '#element_validate' => array('element_validate_integer_positive'),
  );
  $form['flood_settings']['simplytest_launch_flood_threshold'] = array(
    '#type' => 'textfield',
    '#title' => t('Flood threshold'),
    '#default_value' => variable_get('simplytest_launch_flood_threshold', 5),
    '#element_validate' => array('element_validate_integer_positive'),
  );

  // Get current flood entries.
  $select = db_select('flood', 'f');
  $select->addField('f', 'identifier');
  $select->addField('f', 'timestamp');
  $select->addExpression('COUNT(fid)', 'count');
  $select->groupBy('identifier');
  $select->orderBy('count', 'DESC');
  $select->condition('f.event', 'simplytest_launch_submit');
  $select->range(0, 50);
  // Build rows.
  $rows = array();
  foreach ($select->execute()->fetchAll() as $entry) {
    $rows[] = array(
      $entry->identifier,
      format_date($entry->timestamp),
      $entry->count,
      l(t('Permanent block'), 'admin/config/people/ip-blocking/' . $entry->identifier),
      l(t('Reset'), 'admin/simplytest/flood/' . $entry->identifier . '/reset'),
    );
  }
  $form['flood_table'] = array(
    '#type' => 'fieldset',
    '#title' => t('Current flood logs'),
  );
  $form['flood_table']['table'] = array(
    '#theme' => 'table',
    '#header' => array(
      t('Identifier'),
      t('Start date'),
      t('Count'),
      array(
        'data' => t('Operations'),
        'colspan' => 2,
      ),
    ), 
    '#rows' => $rows, 
    '#empty' => t('No recent flood logs.'),
  );

  return system_settings_form($form);
}


/**
 * Confirms and executes an operation on a flood entry.
 */
function simplytest_launch_flood_operation($form, $form_state, $id, $operation) {
  $form['#identifier'] = $id;

  switch ($operation) {
    case 'reset':
      $form['#submit'][] = 'simplytest_launch_foold_operation_reset';
      break;
    default:
      return $form;
      break;
  }

  $form = confirm_form(
    $form,
    t('Do you want to @operation this entry?', array('@operation' => $operation, '%id' => $id)),
    'admin/simplytest/flood'
  );
  return $form;
}

/**
 * Deletes an sponsor entry after confirmation.
 */
function simplytest_launch_foold_operation_reset($form, $form_state) {
  db_delete('flood')
    ->condition('event', 'simplytest_launch_submit')
    ->condition('identifier', $form['#identifier'])
    ->execute();
  drupal_goto('admin/simplytest/flood');
}
