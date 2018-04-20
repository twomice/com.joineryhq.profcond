<?php

require_once 'profcond.civix.php';
use CRM_Profcond_ExtensionUtil as E;

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function profcond_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Event_Form_Registration_Register') {
    $eventId = $form->get('id');
    $config = _profcond_get_search_config();
    // Only take action if we're configured to act on this event.
    if ($eventConfig = CRM_Utils_Array::value($eventId, $config['event'])) {
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.profcond', 'js/profcond.js');
      CRM_Core_Resources::singleton()->addVars('profcond', array(
        'eventConfig' => $eventConfig,
        'formId' => $form->_attributes['id'],
      ));
      // Add a hidden field for transmitting names of dynamically hidden fields.
      $form->add('hidden', 'profcond_hidden_fields', NULL, array('id' => 'profcond_hidden_fields'));
      // If the form is being submitted, note the value of profcond_hidden_fields
      // and temporarily strip them from the "required" array.
      if ($form->_flagSubmitted) {
        $hiddenFieldNames = json_decode($form->_submitValues['profcond_hidden_fields']);
        $temporarilyUnrequiredFields = array();
        foreach($hiddenFieldNames as $hiddenFieldName) {
          $index = array_search($hiddenFieldName, $form->_required);
          if ($index) {
            unset($form->_required[$index]);
            $temporarilyUnrequiredFields[] = $hiddenFieldName;
          }
        }      
        $form->_attributes['temporarilyUnrequiredFields'] = $temporarilyUnrequiredFields;
      }
    }
    
  }
}

/**
 * Implements hook_civicrm_validateForm().
 */
function profcond_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $form->_required = array_merge($form->_required, $form->_attributes['temporarilyUnrequiredFields']);
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

function _profcond_get_search_config() {
  return CRM_Core_BAO_Setting::getItem(NULL, 'com.joineryhq.profcond');
}