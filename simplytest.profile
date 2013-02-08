<?php

/**
 * @file
 * Enables modules and site configuration for a minimal site installation.
 */

/**
 * Implements hook_menu().
 */
function simplytest_menu() {
  $items = array();
  // Submission frontpage.
  $items['start'] = array(
    'page callback' => 'drupal_get_form',
    'page arguments' => array('simplytest_submission_form'),
    'access arguments' => array('access simplytest page'),
    'type' => MENU_CALLBACK,
  );
  // General administrative config path.
  $items['admin/simplytest'] = array(
    'title' => 'Simplytest',
    'description' => 'Administer simplytest site settings.',
    'access arguments' => array('administer simplytest'),
    'page callback' => 'simplytest_admin_menu_block_page',
    'type' => MENU_NORMAL_ITEM,
  );
  return $items;
}

/**
 * Submission page form.
 *
 * This page callback represents the main submission page on simplytest.me.
 * Other submodules build the actual submission form and may alter it.
 */
function simplytest_submission_form($form = array()) {
  $form['#submission_form'] = TRUE;

  // Set empty breadcrumb for start page.
  drupal_set_breadcrumb(array());
  // Set title to site slogan.
  drupal_set_title(variable_get('site_slogan'));

  // Let other modules build the submission form elements.
  foreach (module_implements(__FUNCTION__) as $module) {
    $function = $module . '_' . __FUNCTION__;
    if (function_exists($function)) {
      $result = call_user_func_array($function, func_get_args());
      if (isset($result) && is_array($result)) {
        $form = array_merge_recursive($form, $result);
      }
    }
  }

  return $form;
}

/**
 * Implements hook_preprocess_HOOK().
 *
 * Moves the submission form from content to the featured region.
 */
function simplytest_preprocess_page(&$variables) {
  if (isset($variables['page']['content']['system_main']['#submission_form'])) {
    $variables['page']['featured'] = array($variables['page']['content'], $variables['page']['featured']);
    $variables['page']['content'] = array();
  }
}

/**
 * Administrative block overview page.
 */
function simplytest_admin_menu_block_page() {
  module_load_include('inc', 'system', 'system.admin');
  $item = menu_get_item();
  if ($content = system_admin_menu_block($item)) {
    $output = theme('admin_block_content', array('content' => $content));
  }
  else {
    $output = t('You do not have any administrative items.');
  }
  return $output;
}

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function simplytest_form_install_configure_form_alter(&$form, $form_state) {
  // Pre-populate the site name with the server name.
  $form['site_information']['site_name']['#default_value'] = 'simplytest.me';
}

/**
 * Implements hook_permission().
 */
function simplytest_permission() {
  // General permissions.
  return array(
    'access simplytest page' => array(
      'title' => t('Access simplytest home page'),
    ),
    'administer simplytest' => array(
      'title' => t('Administer simplytest'),
    ),
    'submit simplytest requests' => array(
      'title' => t('Submit simplytest submissions'),
    ),
    'bypass antiflood' => array(
      'title' => t('Bypass anti-flood mechanisms'),
    ),
  );
}
