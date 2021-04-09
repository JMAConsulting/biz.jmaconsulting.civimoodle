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
 * Implements hook_civicrm_container().
 */
function civimoodle_civicrm_container(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
  $container->setDefinition("cache.civiMoodle", new Symfony\Component\DependencyInjection\Definition(
    'CRM_Utils_Cache_Interface',
    [
      [
        'name' => 'civi-moodle',
        'type' => ['*memory*', 'SqlGroup', 'ArrayCache'],
      ],
    ]
  ))->setFactory('CRM_Utils_Cache::create')->setPublic(true);
}

/**
 * Implements hook_civicrm_fieldOptions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_fieldOptions
 */
function civimoodle_civicrm_fieldOptions($entity, $field, &$options, $params) {
  if ($entity == 'Event') {
    if ($field == CRM_Civimoodle_Util::getCustomFieldKey('courses')) {
      // fetch available Moodle courses in array('id' => 'fullname') format
      $courses = CRM_Civimoodle_Util::getAvailableCourseNames();
      if (isset($courses) && count($courses)) {
        $options = $courses;
      }
    }
  }
}

function civimoodle_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Event_Form_Registration_ThankYou' || $formName == 'CRM_Event_Form_Registration_ParticipantConfirm') {
    if (Civi::settings()->get('moodle_cms_credential') && function_exists('user_load')) {
      global $user;
      if (!empty($user->uid)) {
        $ufID = $user->uid;
        $contactID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFMatch', $ufID, 'contact_id', 'uf_id');
        $result = civicrm_api3('Contact', 'getsingle', array(
          'return' => array(
            'email',
            'first_name',
            'last_name',
          ),
          'id' => $contactID,
        ));
        _updateDrupalUserDetails($ufID, $result, TRUE);

        $courses = Civi::cache('civiMoodle')->get('moodle-courses');
        if (!empty($courses)) {
          $contactID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFMatch', $ufID, 'contact_id', 'uf_id');
          $userID = CRM_Civimoodle_Util::createUser($contactID, TRUE);
          CRM_Civimoodle_Util::enrollUser($courses, $userID);

          $userIDKey = CRM_Civimoodle_Util::getCustomFieldKey('user_id');
          //update user id in contact
          civicrm_api3('Contact', 'create', array(
            'id' => $contactID,
            $userIDKey => $userID,
            $passwordKey => '', //clean password if user ID is stored
          ));

          Civi::cache('civiMoodle')->delete('moodle-courses');
        }
      }
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

      if (Civi::settings()->get('moodle_cms_credential') && function_exists('user_load')) {
        $userIDKey = CRM_Civimoodle_Util::getCustomFieldKey('user_id');
        $passwordKey = CRM_Civimoodle_Util::getCustomFieldKey('password');
        $result = civicrm_api3('Contact', 'getsingle', array(
          'return' => array(
            'email',
            'first_name',
            'last_name',
            $userIDKey,
          ),
          'id' => $objectRef->contact_id,
        ));

        $ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFMatch', $objectRef->contact_id, 'uf_id', 'contact_id');

        if (!$ufID) {
          global $user;
          if (!empty($user) && !empty($user->uid)) {
            civicrm_api3('UFMatch', 'create', array(
              'contact_id' => $objectRef->contact_id,
              'uf_id' => $user->uid,
              'uf_name' => $user->mail,
              'domain_id' => CRM_Core_BAO_Domain::getDomain(),
            ));
            $ufID = $user->uid;
          }
        }

        $userParams = [
          'firstname' => $result['first_name'],
          'lastname' => $result['last_name'],
          'email' => CRM_Utils_Array::value('email', $result),
          'username' => $result['first_name'],
          'password' => 'changeme',
        ];
        if ($ufID) {
          $drupaluser = _updateDrupalUserDetails($ufID, $result);
          $userParams = array_merge($userParams, [
            'email' => $drupaluser->mail,
            'username' => $drupaluser->name,
          ]);
        }
        $userID = CRM_Utils_Array::value($userIDKey, $result);

        if (!empty($userID)) {
          // If user ID not found, meaning if moodle user is not created or user ID not found in CiviCRM
          $criterias = array(
            'username' => $usernameKey,
            'email' => 'email',
          );
          // fetch user ID on basis of username OR email
          foreach ($criterias as $key => $value) {
            $criteria = array(
              'key' => $key,
              'value' => $result[$value],
            );
            list($isError, $response) = CRM_Civimoodle_API::singleton($criteria, TRUE)->getUser();
            $response = json_decode($response, TRUE);

            // if user found on given 'username' value
            if (!empty($response['users'])) {
              $userID = $response['users'][0]['id'];
            }
            // break the loop means avoid next criteria search on basis of email if user ID is found
            if (!empty($userID)) {
              break;
            }
          }
        }

        if (!empty($userID)) {
          // update user by calling core_user_update_users
          $updateParams = array_merge($userParams, array('id' => $userID));
          list($isError, $response) = CRM_Civimoodle_API::singleton($updateParams, TRUE)->updateUser();
        }

        //update user id in contact
        civicrm_api3('Contact', 'create', array(
          'id' => $objectRef->contact_id,
          $userIDKey => $userID,
          $passwordKey => '', //clean password if user ID is stored
        ));
      }
      else {
        // create/update moodle user based on CiviCRM contact ID information
        $userID = CRM_Civimoodle_Util::createUser($objectRef->contact_id);
      }

      // enroll user of given $userID to multiple courses $courses
      if (!empty($userID)) {
        CRM_Civimoodle_Util::enrollUser($courses, $userID);
      }
      else {
         Civi::cache('civiMoodle')->delete('moodle-courses');
         Civi::cache('civiMoodle')->set('moodle-courses', $courses);
      }
    }
  }
}

function _updateDrupalUserDetails($ufID, $contactParams, $create = FALSE) {
  // fetch user details
  $user = user_load($ufID);
  $userEditParams = (array) $user;
  $matchingParams = [
    'field_first_name',
    'field_last_name',
  ];
  if ($create) {
    $userEditParams = [
      'uid' => $ufID,
      'field_first_name' => NULL,
      'field_last_name' => NULL,
    ];
  }
  foreach($userEditParams as $attribute => $value) {
    if (in_array($attribute, $matchingParams) && (!empty($user->$attribute) || $create)) {
      $paramName = str_replace('field_', '', $attribute);
      if ($create) {
        $userEditParams[$attribute]['und'] = [
          0 => [
            'value' => CRM_Utils_Array::value($paramName, $contactParams),
          ],
        ];
      }
      elseif (empty($userEditParams[$attribute]['und'][0]['value'])) {
        $userEditParams[$attribute]['und'][0]['value'] = CRM_Utils_Array::value($paramName, $contactParams);
      }
    }
  }

  user_save($user, $userEditParams);

  return $user;
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 */
function civimoodle_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Event_Form_Participant' && !($form->_action & CRM_Core_Action::DELETE)) {
    $courses = CRM_Civimoodle_Util::getCoursesFromEvent($fields['event_id']);
    if (isset($courses) &&
      count($courses) > 0 &&
      CRM_Civimoodle_Util::moodleCredentialPresent($form->_contactId)
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
