<?php

/**
 * One place to store frequently used CiviMoodle variables.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */
class CRM_Civimoodle_FieldInfo {

  /**
   * Return array of Donor Search fields where key is the XML name and value got attributes of corrosponding custom field
   *
   * @return array
   */
  public static function getAttributes($customGroupName) {
    switch ($customGroupName) {
      case 'moodle_credential':
      return array(
        'username' => array(
          'name' => 'username',
          'label' => ts('Username', array('domain' => 'biz.jmaconsulting.civimoodle')),
          'text_length' => 20,
          'data_type' => 'String',
          'html_type' => 'Text',
          'weight' => 1,
        ),
        'password' => array(
          'name' => 'password',
          'label' => ts('Password', array('domain' => 'biz.jmaconsulting.civimoodle')),
          'text_length' => 20,
          'data_type' => 'String',
          'html_type' => 'Text',
          'weight' => 2,
        ),
        'user_id' => array(
          'name' => 'user_id',
          'label' => ts('User ID', array('domain' => 'biz.jmaconsulting.civimoodle')),
          'data_type' => 'Int',
          'html_type' => 'Text',
          'is_view' => 1,
          'weight' => 3,
        ),
      );

      case 'moodle_courses':
       return array(
         'courses' => array(
           'name' => 'courses',
           'label' => ts('Courses', array('domain' => 'biz.jmaconsulting.civimoodle')),
           'data_type' => 'String',
           'html_type' => 'Multi-Select',
         ),
       );
    }
  }

}
