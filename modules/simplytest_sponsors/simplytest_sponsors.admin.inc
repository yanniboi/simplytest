<?php

/**
 * @file
 * Provides managable sponsor blocks and advertisement.
 *
 * Configuration page.
 */

/**
 * Form callback for administrative configuration.
 */
function simplytest_sponsors_configuration_form($form, &$form_state) {
  // Get sponsor configuration.
  $sponsors = variable_get('simplytest_sponsors', array());
  // Add an extra row for new entries.
  $sponsors[] = array(
    'weight' => 1,
    'list_embed' => '',
    'ad_embed' => '',
    'active' => FALSE,
  );
  $form['#tree'] = TRUE;

  foreach ($sponsors as $id => $sponsor) {
    // The dragable table's contents.
    $form['sponsors'][$id]['weight'] = array(
      '#type' => 'weight',
      '#title' => t('Weight'),
      '#title_display' => 'invisible',
      '#default_value' => $sponsor['weight'],
    );
    $form['sponsors'][$id]['list_embed'] = array(
      '#type' => 'textfield',
      '#default_value' => $sponsor['list_embed'],
      '#maxlength' => 999,
    );
    $form['sponsors'][$id]['ad_embed'] = array(
      '#type' => 'textfield',
      '#default_value' => $sponsor['ad_embed'],
      '#maxlength' => 999,
    );
    $form['sponsors'][$id]['active'] = array(
      '#type' => 'checkbox',
      '#default_value' => $sponsor['active'],
    );
    if (!empty($sponsor['list_embed'])) {
      $form['sponsors'][$id]['operations'] = array(
        '#markup' => l(t('Delete'), 'admin/simplytest/sponsors/' . $id . '/delete'),
      );
    }
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save settings'),
  );

  return $form;
}

/**
 * Configuration form submission callback.
 */
function simplytest_sponsors_configuration_form_submit($form, &$form_state) {
  // Sort and save sponsors config.
  $sponsors = $form_state['values']['sponsors'];
  foreach ($sponsors as $id => $sponsor) {
    if (empty($sponsor['list_embed'])) {
      unset($sponsors[$id]);
    }
  }
  usort($sponsors, '_simplytest_sponsors_configuration_form_arraysort');
  variable_set('simplytest_sponsors', $sponsors);
  drupal_set_message(t('Successfully saved sponsor list.'));
}

/**
 * Helper function for sorting tabledrag weights.
 */
function _simplytest_sponsors_configuration_form_arraysort($a, $b) {
  if (isset($a['weight']) && isset($b['weight'])) {
    return $a['weight'] < $b['weight'] ? -1 : 1;
  }
  return 0;
}

/**
 * Configuration form theming function.
 */
function theme_simplytest_sponsors_configuration_form($variables) {
  $form = $variables['form'];
  $rows = array();
  $output = '';

  // Render each cell per row.
  foreach (element_children($form['sponsors']) as $id) {
    $form['sponsors'][$id]['weight']['#attributes']['class'] = array('sponsors-order-weight');
    $rows[] = array(
      'data' => array(
        array('class' => array('sponsors-cross')),
        drupal_render($form['sponsors'][$id]['weight']),
        drupal_render($form['sponsors'][$id]['list_embed']),
        drupal_render($form['sponsors'][$id]['ad_embed']),
        drupal_render($form['sponsors'][$id]['active']),
        drupal_render($form['sponsors'][$id]['operations']),
      ),
      'class' => array('draggable'),
    );
  }

  // Prepare the dragable table to be rendered.
  $header = array('', t('weight'), t('Sponsor list embed'), t('Advertisement embed'), t('Active'), t('Operations'));
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'sponsors-order')));

  $output .= drupal_render_children($form);

  drupal_add_tabledrag('sponsors-order', 'order', 'sibling', 'sponsors-order-weight');

  return $output;
}