<?php

/**
 * @file
 * Provides an advanced submission launcher form.
 *
 * Configuration pages.
 */

/**
 * Flood settings configuration form.
 */
function simplytest_advanced_settings_form($form = array()) {

  // Settings for applying patches.
  $form['patch_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Patch settings'),
  );
  $form['patch_settings']['simplytest_advanced_patches'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum count of patches to apply.'),
    '#default_value' => variable_get('simplytest_advanced_patches', 3),
    '#element_validate' => array('element_validate_integer_positive'),
  );
  $form['patch_settings']['simplytest_advanced_patch_source'] = array(
    '#type' => 'textfield',
    '#title' => t('Allowed patch source'),
    '#default_value' => variable_get('simplytest_advanced_patch_source', '/^https?:\/\/(www\.)?drupal\.org\/files\//'),
  );

  // Settings for addtional projects
  $form['additionals_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Additional projects settings'),
  );
  $form['additionals_settings']['simplytest_advanced_additionals'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum count of additional projects.'),
    '#default_value' => variable_get('simplytest_advanced_additionals', 10),
    '#element_validate' => array('element_validate_integer_positive'),
  );

  return system_settings_form($form);
}
