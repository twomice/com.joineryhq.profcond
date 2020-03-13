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
      $useConditionals = 'contribution';
      break;
  }
  if ($useConditionals) {
    $pageId = $form->get('id');
    $config = _profcond_get_search_config($useConditionals, $pageId);

    // Only take action if we're configured to act on this page (or all pages).
    $pageConfig = $config[$useConditionals]['all'] ?: [];
    $pageConfig = array_merge($pageConfig, CRM_Utils_Array::value($pageId, $config[$useConditionals], []));
    if ($pageConfig) {
      // Add JS Class file for select2 support class. Ensure its weight is lower than profcond.js
      // so that the class is actually loaded before it's invoked in profcond.js.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.profcond', 'js/profcondSelect2.js', 1);
      // Add javascript file to handle the bulk of profcond rules processing.
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.profcond', 'js/profcond.js', 11, 'page-footer');
      CRM_Core_Resources::singleton()->addVars('profcond', array(
        'pageConfig' => $pageConfig,
        'formId' => $form->_attributes['id'],
      ));
      // Add a hidden field for transmitting names of dynamically hidden fields.
      $form->add('hidden', 'profcond_hidden_fields', NULL, array('id' => 'profcond_hidden_fields'));
      // Take specific action when form has been submitted.
      if ($form->_flagSubmitted) {
        // Note the value of profcond_hidden_fields and temporarily strip them
        // from the "required" array. (We'll add them back later in hook_civicrm_validateForm().)
        $hiddenFieldNames = json_decode($form->_submitValues['profcond_hidden_fields']);
        $temporarilyUnrequiredFields = array();
        foreach ($hiddenFieldNames as $hiddenFieldName) {
          $index = array_search($hiddenFieldName, $form->_required);
          if ($index) {
            unset($form->_required[$index]);
            $temporarilyUnrequiredFields[] = $hiddenFieldName;
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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function profcond_civicrm_xmlMenu(&$files) {
  _profcond_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function profcond_civicrm_postInstall() {
  _profcond_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function profcond_civicrm_uninstall() {
  _profcond_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function profcond_civicrm_enable() {
  _profcond_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function profcond_civicrm_disable() {
  _profcond_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function profcond_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _profcond_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function profcond_civicrm_managed(&$entities) {
  _profcond_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function profcond_civicrm_caseTypes(&$caseTypes) {
  _profcond_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function profcond_civicrm_angularModules(&$angularModules) {
  _profcond_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function profcond_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _profcond_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
  function profcond_civicrm_preProcess($formName, &$form) {

  } // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
  function profcond_civicrm_navigationMenu(&$menu) {
  _profcond_civix_insert_navigation_menu($menu, NULL, array(
  'label' => E::ts('The Page'),
  'name' => 'the_page',
  'url' => 'civicrm/the-page',
  'permission' => 'access CiviReport,access CiviContribute',
  'operator' => 'OR',
  'separator' => 0,
  ));
  _profcond_civix_navigationMenu($menu);
  } // */

function _profcond_get_search_config($pageType, $entityId) {
  $config = CRM_Core_BAO_Setting::getItem(NULL, 'com.joineryhq.profcond');
  // Invoke hook_civicrm_profcond_alterConfig
  $null = NULL;
  CRM_Utils_Hook::singleton()->invoke(['config', 'pageType', 'entityId'], $config, $pageType, $entityId,
    $null, $null, $null,
    'civicrm_profcond_alterConfig'
  );
  return $config;
}
