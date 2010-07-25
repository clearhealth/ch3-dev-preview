<?php
/*****************************************************************************
*       Visit.php
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


class Visit extends WebVista_Model_ORM {
	protected $encounter_id;
	protected $encounter_reason;
	protected $patient_id;
	protected $building_id;
	protected $date_of_treatment;
	protected $treating_person_id;
	protected $timestamp;
	protected $last_change_user_id;
	protected $status;
	protected $occurence_id;
	protected $created_by_user_id;
	protected $payer_group_id;
	protected $current_payer;
	protected $room_id;
	protected $practice_id;
	protected $dateOfService;
	protected $activePayerId;
	protected $closed;
	protected $void;
	protected $appointmentId;
	protected $_providerDisplayName = ''; //placeholder for use in visit list iterator
	protected $_locationName = ''; //placeholder for use in visit list iterator
	protected $_legacyORMNaming = true;
	protected $_table = "encounter";
	protected $_primaryKeys = array("encounter_id");

	function getIterator($objSelect = null) {
		return new VisitIterator($objSelect);
	}
	function setLocationName($locationName) {
		$this->_locationName = $locationName;
	}
	function getLocationName() {
		return $this->_locationName;
	}
	function setProviderDisplayName($providerDisplayName) {
		$this->_providerDisplayName = $providerDisplayName;
	}
	function getProviderDisplayName() {
		$provider = new Provider();
		$provider->person_id = $this->treating_person_id;
		$provider->populate();
		return $provider->person->getDisplayName();
	}

	public function getVisitId() {
		return $this->encounter_id;
	}

	public function setVisitId($id) {
		$this->encounter_id = $id;
	}

	public function getDisplayDateOfService() {
		$date = '';
		if ($this->dateOfService != '' && $this->dateOfService != '0000-00-00 00:00:00') {
			$date = date('Y-m-d',strtotime($this->dateOfService));
		}
		return $date;
	}

	public function getInsuranceProgram() {
		return InsuranceProgram::getInsuranceProgram($this->activePayerId);
	}

	public function ormEditMethod($ormId,$isAdd) {
		return $this->ormVisitTypeEditMethod($ormId,$isAdd);
	}

	public function ormVisitTypeEditMethod($ormId,$isAdd) {
		$controller = Zend_Controller_Front::getInstance();
		$request = $controller->getRequest();
		$enumerationId = (int)$request->getParam('enumerationId');

		$view = Zend_Layout::getMvcInstance()->getView();
		$params = array();
		if ($isAdd) {
			$params['parentId'] = $enumerationId;
			unset($_GET['enumerationId']); // remove enumerationId from params list
			$params['grid'] = 'enumItemsGrid';
		}
		else {
			$params['enumerationId'] = $enumerationId;
			$params['ormId'] = $ormId;
		}
		return $view->action('edit-type','visit-details',null,$params);
	}

	public static function ormClasses() {
		return array(
			'Visit' => 'Visit Type',
			'ProcedureCodesCPT' => 'Procedure',
			'DiagnosisCodesICD' => 'Diagnosis',
		);
	}

	public function populateByAppointmentId($appointmentId = null) {
		if ($appointmentId === null) {
			$appointmentId = $this->appointmentId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('appointmentId = ?',(int)$appointmentId);
		$ret = $this->populateWithSql($sqlSelect->__toString());
		$this->postPopulate();
		return $ret;
	}

}
