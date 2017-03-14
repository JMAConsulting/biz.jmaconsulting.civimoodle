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

/**
 * One place to store frequently used Donor Search variables.
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
        ),
        'password' => array(
          'name' => 'password',
          'label' => ts('Password', array('domain' => 'biz.jmaconsulting.civimoodle')),
          'text_length' => 20,
          'data_type' => 'String',
          'html_type' => 'Text',
        ),
        'user_id' => array(
          'name' => 'user_id',
          'label' => ts('User ID', array('domain' => 'biz.jmaconsulting.civimoodle')),
          'data_type' => 'Int',
          'html_type' => 'Text',
          'is_view' => 1,
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
