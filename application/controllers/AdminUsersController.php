<?php
/*****************************************************************************
*	AdminUsersController.php
*
*	Author:  ClearHealth Inc. (www.clear-health.com)	2009
*	
*	ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*	respective logos, icons, and terms are registered trademarks 
*	of ClearHealth Inc.
*
*	Though this software is open source you MAY NOT use our 
*	trademarks, graphics, logos and icons without explicit permission. 
*	Derivitive works MUST NOT be primarily identified using our 
*	trademarks, though statements such as "Based on ClearHealth(TM) 
*	Technology" or "incoporating ClearHealth(TM) source code" 
*	are permissible.
*
*	This file is licensed under the GPL V3, you can find
*	a copy of that license by visiting:
*	http://www.fsf.org/licensing/licenses/gpl.html
*	
*****************************************************************************/


class AdminUsersController extends WebVista_Controller_Action
{
	protected $_form;
	protected $_user;
	
	public function editAction() {
		$personId = (int)$this->_getParam('personId');
        	if (isset($this->_session->messages)) {
        	    $this->view->messages = $this->_session->messages;
        	}
		$this->_form = new WebVista_Form(array('name' => 'person-detail'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "admin-user.raw/edit-process");
		$this->_user = new User();
		$this->_user->populateWithPersonId($personId);
		$this->_form->loadORM($this->_user, "User");
		//var_dump($this->_form);
		$this->view->form = $this->_form;
		$this->view->user = $this->_user;
        	$this->render('edit-user');
	}

	function editProcessAction() {
		$this->indexAction();
		//$this->_form->isValid($_POST);
		$this->user->populateWithArray($_POST['user']);
		$this->user->persist();
		$this->view->message = "Record Saved for User: " . ucfirst($this->user->username);
        	$this->render('index');
	}
}
