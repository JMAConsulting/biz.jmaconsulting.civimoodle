<?php

require_once 'civimoodle.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civimoodle_civicrm_config(&$config) {
  _civimoodle_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civimoodle_civicrm_xmlMenu(&$files) {
  _civimoodle_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civimoodle_civicrm_install() {
  _civimoodle_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civimoodle_civicrm_uninstall() {
  _civimoodle_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civimoodle_civicrm_enable() {
  _civimoodle_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civimoodle_civicrm_disable() {
  _civimoodle_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function civimoodle_civicrm_postProcess($formName, &$form) {
  $eventIDs = (array) json_decode(Civi::settings()->get('moodle_events'), TRUE);
  if ($formName == 'CRM_Event_Form_Registration_Confirm' && in_array($form->_eventId, $eventIDs)) {
    $params = CRM_Utils_Array::value(0, $form->get('params'));
    $criteria = array(
      'key' => 'firstname',
      'value' => $params['first_name'],
    );
    list($isError, $response) = CRM_Civimoodle_API::singleton($criteria)->getUser();
    $response = json_decode($response, TRUE);

    if (!empty($response['users'])) {
      // update user by calling core_user_update_users
      $updateParams = array(
        'id' => $response['users'][0]['id'],
        'firstname' => $params['first_name'],
        'lastname' => $params['last_name'],
        'email' => $params['email-Primary'],
      );
      list($isError, $response) = CRM_Civimoodle_API::singleton($updateParams, TRUE)->updateUser();
    }
    else {
      // create user by calling core_user_create_users
      $createParams = array(
        'username' => 'dummy', //for now we are using 'dummy' username/password to test this web service
        'password' => 'Dummy*123',
        'firstname' => $params['first_name'],
        'lastname' => $params['last_name'],
        'email' => $params['email-Primary'],
      );
      list($isError, $response) = CRM_Civimoodle_API::singleton($createParams, TRUE)->createUser();
    }
  }
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civimoodle_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civimoodle_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civimoodle_civicrm_managed(&$entities) {
  _civimoodle_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civimoodle_civicrm_caseTypes(&$caseTypes) {
  _civimoodle_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civimoodle_civicrm_angularModules(&$angularModules) {
_civimoodle_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civimoodle_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civimoodle_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function civimoodle_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function civimoodle_civicrm_navigationMenu(&$menu) {
  _civimoodle_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'biz.jmaconsulting.civimoodle')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _civimoodle_civix_navigationMenu($menu);
} // */
