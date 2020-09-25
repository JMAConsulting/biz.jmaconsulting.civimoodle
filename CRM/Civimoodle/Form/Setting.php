<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Civimoodle_Form_Setting extends CRM_Core_Form {

  /**
   * Moodle Web Access token
   *
   * @var string
   */
  protected $_accessToken;

  /**
   * Moodle domain URL
   *
   * @var string
   */
  protected $_url;

  /**
   * Use CMS account credentials for Moodle user
   *
   * @var string
   */
  protected $_use_cms_credential;

  /**
   * Set variables up before form is built.
   */
  public function preProcess() {
    if (!CRM_Core_Permission::check('administer CiviCRM')) {
      throw new CRM_Core_Exception(ts('You do not permission to access this page, please contact your system administrator.'));
    }
    $this->_accessToken = Civi::settings()->get('moodle_access_token');
    $this->_url = Civi::settings()->get('moodle_domain');
    $this->_use_cms_credential = Civi::settings()->get('moodle_cms_credential');
  }

  /**
   * Set default values.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array(
      'moodle_access_token' => $this->_accessToken,
      'moodle_domain' => $this->_url,
      'moodle_cms_credential' => $this->_use_cms_credential,
    );
    return $defaults;
  }

  public function buildQuickForm() {
    $this->add('password', 'moodle_access_token', ts('Moodle Web-access Token'), array('class' => 'huge'), TRUE);
    $this->add('text', 'moodle_domain', ts('Moodle domain'), array('class' => 'huge'), TRUE);
    $this->addYesNo('moodle_cms_credential', ts('Do you want to use CMS account credentials for Moodle?'));

    $this->assign('moodleFields', array('moodle_access_token', 'moodle_domain', 'moodle_cms_credential'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    foreach (['moodle_access_token', 'moodle_domain', 'moodle_cms_credential'] as $attribute) {
      Civi::settings()->set($attribute, CRM_Utils_Array::value($attribute, $values));
    }

    CRM_Core_Session::setStatus(ts("Moodle Settings submitted"), ts('Success'), 'success');
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm', 'reset=1'));
  }

}
