<?php

require_once 'profcond.civix.php';

use CRM_Profcond_ExtensionUtil as E;

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function profcond_civicrm_buildForm($formName, &$form) {
  $useConditionals = FALSE;
  switch ($formName) {
    case 'CRM_Event_Form_Registration_Register':
    case 'CRM_Event_Form_Registration_AdditionalParticipant':
      $useConditionals = 'event';
      break;

    case 'CRM_Contribute_Form_Contribution_Main':
    case 'CRM_Contribute_Form_Contribution_Confirm':
      $useConditionals = 'contribution';
      break;
  }
  if ($useConditionals) {
    $pageId = $form->get('id');
    $priceSetId = $form->getVar('_priceSetId');
    $config = _profcond_get_search_config($useConditionals, $pageId, $priceSetId);

    // Only take action if we're configured to act on this page (or all pages).
    $pageConfig = $config[$useConditionals]['all'] ?? [];
    $pageConfig = array_merge_recursive($pageConfig, CRM_Utils_Array::value($pageId, $config[$useConditionals], []));

    if ($priceSetId) {
      $pageConfig = array_merge_recursive($pageConfig, ($config['priceset']['all'] ?? []));
      $pageConfig = array_merge_recursive($pageConfig, ($config['priceset'][$priceSetId] ?? []));
    }

    if ($pageConfig) {
      // Add JS Class file for select2 support class. Ensure its weight is lower than profcond.js
      // so that the class is actually loaded before it's invoked in profcond.js.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.profcond', 'js/profcondSelect2.js', 1);
      // Add javascript file to handle the bulk of profcond rules processing.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.profcond', 'js/profcond.js', 11, 'page-footer');
      $jsVars = array(
        // Whether civicrm debugging is on:
        'isDebug' => (bool) CRM_Core_BAO_Setting::getItem(NULL, 'debug_enabled'),
        // Full configuration for this page:
        'pageConfig' => $pageConfig,
        // The ID of this form (relevant esp. in multi-participant event registrations)
        'formId' => $form->_attributes['id'],
      );
      if ($formName == 'CRM_Event_Form_Registration_AdditionalParticipant') {
        $submittedParticipantValues = [];
        foreach ($form->getVar('_params') as $key => $params) {
          unset($params['credit_card_number']);
          unset($params['credit_card_exp_date']);
          unset($params['credit_card_type']);
          $submittedParticipantValues[$key] = $params;
        }
        $jsVars['submittedParticipantValues'] = $submittedParticipantValues;
      }
      CRM_Core_Resources::singleton()->addVars('profcond', $jsVars);
      // Add a hidden field for transmitting names of dynamically hidden fields.
      $form->add('hidden', 'profcond_hidden_fields', NULL, array('id' => 'profcond_hidden_fields'));
      // Take specific action when form has been submitted with an action that will
      // incur form validation.
      $actionName = $form->controller->_actionName[1];
      $unvalidatedActionNames = [
        'display',
        'reload',
      ];
      if (!in_array($actionName, $unvalidatedActionNames)) {
        // Hidden field names are submitted in the form field 'profcond_hidden_fields'.
        // However, that field won't exist on Contribution 'Confirmation Page' submission.
        // Therefore, we store it in the session when we have it, and on Confirmation
        // Page submit, we'll get it from the session.
        $session = CRM_Core_Session::singleton();
        if ($formName == 'CRM_Contribute_Form_Contribution_Confirm' && $actionName == 'next') {
          $hiddenFieldNamesJson = $session->get('profcond_hidden_fields_' . $form->controller->_key);
        }
        else {
          $hiddenFieldNamesJson = $form->_submitValues['profcond_hidden_fields'];
          $session->set('profcond_hidden_fields_' . $form->controller->_key, $hiddenFieldNamesJson);
        }
        // Now we know the value of profcond_hidden_fields. Temporarily strip them
        // from the "required" array. (We'll add them back later in hook_civicrm_validateForm().)
        $hiddenFieldNames = array_unique(json_decode($hiddenFieldNamesJson) ?? []);
        $temporarilyUnrequiredFields = array();
        foreach ($hiddenFieldNames as $hiddenFieldName) {
          $baseHiddenFieldName = $hiddenFieldName;
          $wasRequired = _profcond_unrequire_field($baseHiddenFieldName, $form);
          if ($wasRequired) {
            $temporarilyUnrequiredFields[] = $baseHiddenFieldName;
          }
        }
        // Store the list so we can add them back later.
        $form->_attributes['temporarilyUnrequiredFields'] = $temporarilyUnrequiredFields;
        // TODO:
        // - Compile list of titles (and group titles too?)for hidden fields,
        // and use session storage or similar to make that available to the
        // confirmation page form. You can get this from $form->_fields.
        //
      }
    }
  }
  // TODO: On display of confirmation page, hide fields that are hidden, and then hide any empty profiles.
  // elseif ($formName == 'CRM_Event_Form_Registration_Confirm' && !$form->_flagSubmitted) {
  // Retrieve list of hidden field titles (and their group titles?), and remove
  // them. These are built from $tpl->_tpl_vars['primaryParticipantProfile'],
  // so you can simply unset them in that array and they won't display.
  // May also need to do something similar for "additional participant profile"
  // fields.
  //
  // $tpl = CRM_Core_Smarty::singleton();
  // dsm($tpl->_tpl_vars['primaryParticipantProfile'], 'vars[primaryParticipantProfile]');
  // unset($tpl->_tpl_vars['primaryParticipantProfile']['CustomPost'][15]['Participant Role']);
  // }
}

/**
 * Implements hook_civicrm_validateForm().
 */
function profcond_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if (array_key_exists('temporarilyUnrequiredFields', $form->_attributes)) {
    // Re-add tempoarily unrequired fields to the list of required fields.
    $form->_required = array_merge($form->_required, $form->_attributes['temporarilyUnrequiredFields']);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function profcond_civicrm_config(&$config) {
  _profcond_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function profcond_civicrm_install() {
  _profcond_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function profcond_civicrm_enable() {
  _profcond_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 */
// function profcond_civicrm_preProcess($formName, &$form) {

// } // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
// function profcond_civicrm_navigationMenu(&$menu) {
// _profcond_civix_insert_navigation_menu($menu, NULL, array(
// 'label' => E::ts('The Page'),
// 'name' => 'the_page',
// 'url' => 'civicrm/the-page',
// 'permission' => 'access CiviReport,access CiviContribute',
// 'operator' => 'OR',
// 'separator' => 0,
// ));
// _profcond_civix_navigationMenu($menu);
// } // */

/**
 *
 */
function _profcond_get_search_config($pageType, $entityId) {
  $config = CRM_Core_BAO_Setting::getItem(NULL, 'com.joineryhq.profcond');
  // Invoke hook_civicrm_profcond_alterConfig
  $null = NULL;
  CRM_Utils_Hook::singleton()->invoke(['config', 'entityType', 'entityId'], $config, $pageType, $entityId,
    $null, $null, $null,
    'civicrm_profcond_alterConfig'
  );
  $pageTypePriceSet = 'priceset';
  CRM_Utils_Hook::singleton()->invoke(['config', 'entityType', 'entityId'], $config, $pageTypePriceSet, $priceSetId,
    $null, $null, $null,
    'civicrm_profcond_alterConfig'
  );
  return $config;
}

/**
 * Specify that a given field should not be considered required.
 * (This effect is temporary, reversed elsewhere in this extension.)
 *
 * @param string $baseHiddenFieldName The form name of the element.
 * @param obj $form The form object, e.g. of the type passed to hook_civicrm_buildForm.
 *
 * @return boolean Whether this field was actually being required before this change.
 */
function _profcond_unrequire_field($baseHiddenFieldName, &$form) {
  $wasRequired = NULL;
  $requiredIndex = array_search($baseHiddenFieldName, $form->_required);
  if ($requiredIndex !== FALSE) {
    unset($form->_required[$requiredIndex]);
    $wasRequired = TRUE;
  }

  if (!empty($form->_rules[$baseHiddenFieldName])) {
    foreach ($form->_rules[$baseHiddenFieldName] as $ruleIndex => $rule) {
      if ($rule['type'] == 'required') {
        unset($form->_rules[$baseHiddenFieldName][$ruleIndex]);
      }
    }
    $wasRequired = TRUE;
  }

  return $wasRequired;
}
