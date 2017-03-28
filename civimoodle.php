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
 * Implements hook_civicrm_customFieldOptions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_customFieldOptions
 */
function civimoodle_civicrm_customFieldOptions($customFieldID, &$options) {
  if ($customFieldID == CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'courses', 'id', 'name')) {
    // fetch available Moodle courses in array('id' => 'fullname') format
    $courses = CRM_Civimoodle_Util::getAvailableCourseNames();
    if (isset($courses) && count($courses)) {
      $options = $courses;
    }
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function civimoodle_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Participant' && $op == 'create') {
    // fetch courses from given event ID
    $courses = CRM_Civimoodle_Util::getCoursesFromEvent($objectRef->event_id);
    if (isset($courses) && count($courses) > 0) {
      // create/update moodle user based on CiviCRM contact ID information
      $userID = CRM_Civimoodle_Util::createUser($objectRef->contact_id);
      // enroll user of given $userID to multiple courses $courses
      if (!empty($userID)) {
        CRM_Civimoodle_Util::enrollUser($courses, $userID);
      }
    }
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 */
function civimoodle_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Event_Form_Participant' && !($form->_action & CRM_Core_Action::DELETE)) {
    $courses = CRM_Civimoodle_Util::getCoursesFromEvent($fields['event_id']);
    $contactID = (!empty($fields['contact_id'])) ? $fields['contact_id'] : $form->_contactId;
    if (isset($courses) &&
      count($courses) > 0 &&
      CRM_Civimoodle_Util::moodleCredentialPresent($contactID)
    ) {
      $errors['event_id'] = ts('Moodle Username or Password not found.');
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
 */
function civimoodle_civicrm_navigationMenu(&$menu) {
  // get the id of Administer Menu
  $administerMenuID = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Administer', 'id', 'name');
  $systemSettingMenuID = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'System Settings', 'id', 'name');

  // skip adding menu if there is no administer menu
  if ($administerMenuID) {
    // get the maximum key under adminster menu
    if (!empty($menu[$administerMenuID]['child'][$systemSettingMenuID])) {
      $maxKey = max(array_keys($menu[$administerMenuID]['child'][$systemSettingMenuID]['child']));
      $menu[$administerMenuID]['child'][$systemSettingMenuID]['child'][$maxKey+1] =  array (
        'attributes' => array (
          'label'      => 'CiviCRM Moodle Integration',
          'name'       => 'moodle_settings',
          'url'        => 'civicrm/moodle/setting?reset=1',
          'permission' => 'administer CiviCRM',
          'operator'   => NULL,
          'separator'  => TRUE,
          'parentID'   => $administerMenuID,
          'navID'      => $maxKey+1,
          'active'     => 1,
        )
      );
    }
  }
}
