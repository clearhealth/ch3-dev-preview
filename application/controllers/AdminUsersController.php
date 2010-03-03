<?php
/*****************************************************************************
*       AdminUsersController.php
*
*       Author:  ClearHealth Inc. (www.clear-health.com)        2009
*       
*       ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*       respective logos, icons, and terms are registered trademarks 
*       of ClearHealth Inc.
*
*       Though this software is open source you MAY NOT use our 
*       trademarks, graphics, logos and icons without explicit permission. 
*       Derivitive works MUST NOT be primarily identified using our 
*       trademarks, though statements such as "Based on ClearHealth(TM) 
*       Technology" or "incoporating ClearHealth(TM) source code" 
*       are permissible.
*
*       This file is licensed under the GPL V3, you can find
*       a copy of that license by visiting:
*       http://www.fsf.org/licensing/licenses/gpl.html
*       
*****************************************************************************/


class AdminUsersController extends WebVista_Controller_Action {

	protected $_form;
	protected $_user;
	
	public function editAction() {
		$personId = (int)$this->_getParam('personId');
        	if (isset($this->_session->messages)) {
        	    $this->view->messages = $this->_session->messages;
        	}
		$this->_form = new WebVista_Form(array('name' => 'user-detail'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "admin-users.raw/edit-process");
		$this->_user = new User();
		$this->_user->populateWithPersonId($personId);
		$this->_user->personId = $personId;
		$this->_form->loadORM($this->_user, "User");
		//var_dump($this->_form);
		$this->view->form = $this->_form;
		$this->view->user = $this->_user;
        	$this->render('edit-user');
	}

	public function editProcessAction() {
		$personId = (int)$this->_getParam('personId');
		$params = $this->_getParam('user');
		$this->_user = new User();
		$this->_user->populateWithPersonId($personId);
		$this->_user->personId = $personId;
		$this->_user->populateWithArray($params);
		$this->_user->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$msg = "Record Saved for User: " . ucfirst($this->_user->username);
		$json->direct($msg);
	}

	public function changePasswordAction() {
		$this->render('change-password');
	}

	public function processChangePasswordAction() {
		$params = $this->_getParam('user');
		$currentUserId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$user = new User();
		$user->userId = $currentUserId;
		$user->personId = $currentUserId; // userId and personId are similar
		$user->populate();
		if ($params['newPassword'] != $params['confirmNewPassword']) {
			$ret = __('New password does not match confirmed password.');
		}
		else if ($user->password != $params['currentPassword']) {
			$ret = __('Current password is invalid.');
		}
		else if (!strlen($params['newPassword']) > 0) {
			$ret = __('New password is required.');
		}
		else if ($params['newPassword'] == $params['currentPassword']) {
			$ret = __('New password must be different from current password.');
		}
		else {
			$password = $params['newPassword'];
			$user->password = $password;
			$user->persist();
			$ret = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function editSigningKeyAction() {
		$currentUserId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$userKey = new UserKey();
		$userKey->userId = $currentUserId;
		$userKey->populate();
		$isNewKey = true;
		if (strlen($userKey->privateKey) > 0) {
			$isNewKey = false;
		}
		$this->view->isNewKey = $isNewKey;
		$this->render('edit-signing-key');
	}

	public function processEditSigningKeyAction() {
		$params = $this->_getParam('user');
		if ($params['newSignature'] != $params['confirmNewSignature']) {
			$ret = __('New signature does not match confirmed signature.');
		}
		else if (!strlen($params['newSignature']) > 0) {
			$ret = __('New signature is required.');
		}
		else if ($params['newSignature'] == $params['currentSignature']) {
			$ret = __('New signature must be different from current signature.');
		}
		else {
			$currentUserId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
			$userKey = new UserKey();
			$userKey->userId = $currentUserId;
			$userKey->populate();
			$newUserKey = clone $userKey;
			$newUserKey->generateKeys($params['newSignature']);
			do {
				if (strlen($userKey->privateKey) > 0) {
					try {
						$privateKeyString = $userKey->getDecryptedPrivateKey($params['currentSignature']);
					}
					catch (Exception $e) {
						$ret = __('Current signature is invalid.'.PHP_EOL.$e->getMessage());
						break;
					}
				}
				$newUserKey->persist();
				$ret = true;
			} while (false);
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function validateSigningKeyAction() {
		$signature = $this->_getParam('signature');
		$currentUserId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$userKey = new UserKey();
		$userKey->userId = $currentUserId;
		$userKey->populate();
		if (strlen($userKey->privateKey) > 0) {
			try {
				$privateKeyString = $userKey->getDecryptedPrivateKey($signature);
				$ret = __('Current signature is valid.');
			}
			catch (Exception $e) {
				$ret = __('Current signature is invalid.'.PHP_EOL.$e->getMessage());
			}
		}
		else {
			$ret = __('Cannot verify, no signature exists');
		}

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function editLocationAction() {
		$personId = (int)$this->_getParam('personId');
		$this->_form = new WebVista_Form(array('name' => 'user'));
		$this->_form->setAction(Zend_Registry::get('baseUrl').'admin-users.raw/process-edit-location');
		$this->_user = new User();
		$this->_user->populateWithPersonId($personId);
		$this->_user->personId = $personId;
		$this->_form->loadORM($this->_user,'User');
		$this->view->form = $this->_form;
		$this->view->facilityIterator = new FacilityIterator();
		$this->render('edit-location');
	}

	public function processEditLocationAction() {
		$personId = (int)$this->_getParam('personId');
		$params = $this->_getParam('user');
		$this->_user = new User();
		$this->_user->userId = $personId;
		$this->_user->populateWithPersonId($personId);
		$this->_user->personId = $personId;
		$this->_user->populateWithArray($params);
		$this->_user->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$msg = __('Record Saved');
		$json->direct($msg);
	}

	public function processAddAction() {
		$username = $this->_getParam('username');
		$user = new User();
		$user->username = $username;
		$user->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($user->personId);
	}

}
