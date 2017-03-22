<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

class CRM_Civimoodle_Util {

  /**
   * Function used to enroll a user on multiple courses
   *
   * @param array $courses
   *   Array of course IDs
   * @param int $userID
   *   Moodle user ID
   *
   */
  public static function enrollUser($courses, $userID) {
    foreach ($courses as $courseID) {
      $criteria = array(
        'roleid' => 5, //hardcoding for now, 5 is the value for student role ID
        'userid' => $userID,
        'courseid' => $courseID,
      );
      list($isError, $response) = CRM_Civimoodle_API::singleton($criteria, TRUE)->enrollUser();
    }
  }

  /**
   * Function used to create/update moodle user
   *
   * @param int $contactID
   *      CiviCRM contact ID
   *
   * @return int $userID
   *     Moodle user ID
   */
  public static function createUser($contactID) {
    $usernameKey = self::getCustomFieldKey('username');
    $passwordKey = self::getCustomFieldKey('password');
    $userIDKey = self::getCustomFieldKey('user_id');
    $result = civicrm_api3('Contact', 'getsingle', array(
      'return' => array(
        'email',
        'first_name',
        'last_name',
        $usernameKey,
        $passwordKey,
        $userIDKey,
      ),
      'id' => $contactID,
    ));
    $userParams = array(
      'firstname' => $result['first_name'],
      'lastname' => $result['last_name'],
      'email' => $result['email'],
      'username' => $result[$usernameKey],
      'password' => $result[$passwordKey],
    );
    $userID = CRM_Utils_Array::value($userIDKey, $result);

    // If user ID not found, meaning if moodle user is not created or user ID not found in CiviCRM
    $criterias = array(
      'username' => $usernameKey,
      'email' => 'email',
    );
    if (empty($userID)) {
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
    else {
      // create user by calling core_user_create_users
      list($isError, $response) = CRM_Civimoodle_API::singleton($userParams, TRUE)->createUser();
      $response = json_decode($response, TRUE);
      $userID = CRM_Utils_Array::value('id', $response);
    }

    //update user id in contact
    civicrm_api3('Contact', 'create', array(
      'id' => $contactID,
      $userIDKey => $userID,
      $passwordKey => '', //clean password if user ID is stored
    ));

    return $userID;
  }

  /**
   * Function to fetch courses IDs from given event ID
   *
   * @param int $eventID
   *      CiviCRM Event ID
   *
   * @return array
   *     Array of moodle course IDs
   */
  public static function getCoursesFromEvent($eventID) {
    $coursesFieldKey = self::getCustomFieldKey('courses');
    $result = civicrm_api3('Event', 'getsingle', array(
      'return' => array($coursesFieldKey),
      'id' => $eventID,
    ));

    return CRM_Utils_Array::value($coursesFieldKey, $result);
  }

  /**
   * Function to get custom-field api key from its name
   *
   * @param string $fieldName
   *      CiviCRM custom field name
   *
   * @return string
   *     custom-field api key name e.g. 'custom_19'
   */
  public static function getCustomFieldKey($fieldName) {
    $customFieldID = civicrm_api3('CustomField', 'getvalue', array(
      'name' => $fieldName,
      'return' => 'id',
    ));

    return 'custom_' . $customFieldID;
  }

  /**
   * Function check if moodle credentials is present for a given contact ID
   *
   * @param int $contactID
   *      CiviCRM contact ID
   *
   * @return boolean
   *
   */
  public static function moodleCredentialPresent($contactID) {
    $usernameKey = self::getCustomFieldKey('username');
    $passwordKey = self::getCustomFieldKey('password');
    $userIDKey = self::getCustomFieldKey('user_id');
    $result = civicrm_api3('Contact', 'getsingle', array(
      'return' => array(
        $usernameKey,
        $passwordKey,
        $userIDKey,
      ),
      'id' => $contactID,
    ));
    return (empty($result[$userIDKey]) && empty($result[$usernameKey]));
  }
}
