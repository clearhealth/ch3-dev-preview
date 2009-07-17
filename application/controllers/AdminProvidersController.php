<?php
/*****************************************************************************
*	AdminProvidersController.php
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


class AdminProvidersController extends WebVista_Controller_Action
{
	protected $_form;
	protected $_provider;
	
    public function indexAction() {
        $this->render();
    }

	public function editAction() {
		$personId = (int)$this->_getParam('personId');
        	if (isset($this->_session->messages)) {
        	    $this->view->messages = $this->_session->messages;
        	}
		$this->_form = new WebVista_Form(array('name' => 'provider-detail'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "admin-providers.raw/edit-process");
		$this->_provider = new Provider();
		$this->_provider->person_id = $personId;
		if (!$this->_provider->populate()) {
			if ($personId > 0) {
				//handle case where person exists but no provider record
				$this->view->noProvider = true;
			}
			//do nothing if personId is 0, no person selected yet
		}
		$this->_form->loadORM($this->_provider, "Person");
		//var_dump($this->_form);
		$this->view->form = $this->_form;
		$this->view->person = $this->_provider;
        	$this->render('edit');
	}

	function editProcessAction() {
		$this->indexAction();
		//$this->_form->isValid($_POST);
		$this->_provider->populateWithArray($_POST['provider']);
		$this->_provider->persist();
		$this->view->message = "Record Saved for Provider: " . ucfirst($this->_provider->first_name) . " " . ucfirst($this->_provider->last_name);
        	$this->render('index');
	}

	function addProcessAction() {
		$personId = (int)$this->_getParam('personId');
		$this->_provider = new Provider();
                $this->_provider->person_id = $personId;
                $this->_provider->populate();
		$this->_provider->persist();
		$acj = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $acj->suppressExit = true;
                $acj->direct(array(true));
	}
}
