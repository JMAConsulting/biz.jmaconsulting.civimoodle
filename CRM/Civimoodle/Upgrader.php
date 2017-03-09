<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Civimoodle_Upgrader extends CRM_Civimoodle_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
   */
  public function install() {
    civicrm_api3('Navigation', 'create', array(
      'label' => ts('Moodle Settings', array('domain' => 'biz.jmaconsulting.civimoodle')),
      'name' => 'moodle_settings',
      'url' => 'civicrm/moodle/setting?reset=1',
      'domain_id' => CRM_Core_Config::domainID(),
      'is_active' => 1,
      'parent_id' => civicrm_api3('Navigation', 'getvalue', array(
        'return' => "id",
        'name' => "System Settings",
      )),
      'permission' => 'administer CiviCRM',
    ));

    CRM_Core_BAO_Navigation::resetNavigation();
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
   */
  public function uninstall() {
    self::changeNavigation('delete');
    Civi::settings()->revert('moodle_events');
    Civi::settings()->revert('moodle_access_token');
    Civi::settings()->revert('moodle_domain');

  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
   */
  public function enable() {
    self::changeNavigation('enable');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
   */
  public function disable() {
    self::changeNavigation('disable');
  }

  /**
   * disable/enable/delete Moodle Setting link
   *
   * @param string $action
   * @throws \CiviCRM_API3_Exception
   */
  public static function changeNavigation($action) {
    $names = array('moodle_settings');
    foreach ($names as $name) {
      if ($name == 'delete') {
        $id = civicrm_api3('Navigation', 'getvalue', array(
          'return' => "id",
          'name' => $name,
        ));
        if ($id) {
          civicrm_api3('Navigation', 'delete', array('id' => $id));
        }
      }
      else {
        $isActive = ($action == 'enable') ? 1 : 0;
        CRM_Core_BAO_Navigation::setIsActive(
          CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $name, 'id', 'name'),
          $isActive
        );
      }
    }

    CRM_Core_BAO_Navigation::resetNavigation();
  }

}
