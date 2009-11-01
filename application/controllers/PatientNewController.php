<?php
/*****************************************************************************
*       PatientNewController.php
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


class PatientNewController extends WebVista_Controller_Action {

	protected $_session;
	protected $_patient;
	protected $_form;
	protected $_location;

	public function init() {
		$this->_location = $cprs->location;
	}

	public function indexAction() {
        	if (isset($this->_session->messages)) {
        	    $this->view->messages = $this->_session->messages;
        	}
		$this->_form = new WebVista_Form(array('name' => 'patient-new'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "patient-new.raw/add-process");
		$this->_patient = new Patient();
		$this->_address = new Address();
		$this->_phoneNumber = new PhoneNumber();
		$this->_form->loadORM($this->_patient, "Patient");
		$this->_form->setWindow('windowNewPatientId');
		$this->_form->registrationLocationId->setValue($this->_location->locationId);
		$this->view->form = $this->_form;
        	$this->render();
	}

	function addProcessAction() {
		$this->indexAction();
		//$this->_form->isValid($_POST);
		$this->_patient->populateWithArray($_POST['patient']);
		$this->_patient->persist();
		$this->_patient->address->persist();
		$this->_patient->phoneNumber->persist();
		//$this->_patient->persist();
		$this->view->message = "Record Saved for Patient: " . ucfirst($this->_patient->firstName) . " " . ucfirst($this->_patient->lastName);
        	$this->render('index');
	}
}
