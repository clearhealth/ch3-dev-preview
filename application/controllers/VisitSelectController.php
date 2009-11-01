<?php
/*****************************************************************************
*       VisitSelectController.php
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


class VisitSelectController extends WebVista_Controller_Action {


	public function indexAction()  {
		$currentUserPersonId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$defaultLocationId = (int)Zend_Auth::getInstance()->getIdentity()->default_location_id;
		$this->currentUserPersonId = $currentUserPersonId;
		$this->defaultLocationId = $defaultLocationId;

		$facilityIterator = new FacilityIterator();
		$providerIterator = new ProviderIterator();
		$this->view->facilityIterator = $facilityIterator;
		$this->view->providerIterator = $providerIterator;

		$this->view->visitDetails = $this->_getVisitDetails();

		$this->render();
	}

	public function visitTypeAction() {
		$this->render();
	}

	public function diagnosesAction() {
		$this->render();
	}

	public function proceduresAction() {
		$providerIterator = new ProviderIterator();
		$this->view->listProviders = $providerIterator->toArray('personId','displayName');
		$this->render();
	}

	public function vitalsAction() {
		$this->render();
	}

	public function immunizationsAction() {
		$immunizationName = "Reactions";
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($immunizationName);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$this->view->reactions = $enumerationIterator;

		$this->render();
	}

	public function educationAction() {
		$name = PatientEducation::ENUM_LEVEL_PARENT_NAME;
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$listLevels = array(''=>__('(None selected)'));
		foreach ($enumerationIterator as $enumeration) {
			$listLevels[$enumeration->key] = $enumeration->name;
		}
		$this->view->listLevels = $listLevels;
		$this->render();
	}

	public function hsaAction() {
		$this->render();
	}

	public function examsAction() {
		$name = PatientExam::ENUM_RESULT_PARENT_NAME;
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$listResults = array(''=>__('(None selected)'));
		foreach ($enumerationIterator as $enumeration) {
			$listResults[$enumeration->key] = $enumeration->name;
		}
		$this->view->listResults = $listResults;
		$this->render();
	}

	function listVisitsAction() {
                $personId = (int)$this->_getParam('personId');
                if (!$personId > 0) $this->_helper->autoCompleteDojo(array());
		$visitIterator = new VisitIterator();
		$visitIterator->setFilters(array('patientId' => $personId));
                $acj = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $acj->suppressExit = true;
                $acj->direct(array("rows" => $visitIterator->toJsonArray('encounter_id',array('date_of_treatment','locationName','providerDisplayName'))),true);
        }

	function processAddVisitAction() {
		$visitParams = $this->_getParam('visit');
		$visitParams['created_by_user_id'] = (int)Zend_Auth::getInstance()->getIdentity()->userId;
		$visitParams['date_of_treatment'] = date('Y-m-d');
		$visitParams['timestamp'] = date('Y-m-d h:i:s');
		$visit = new Visit();
		$visit->populateWithArray($visitParams);
		$visit->persist();
		$msg = __("Visit added successfully.");
		$data = array();
		$data['msg'] = $msg;
		$data['visitId'] = $visit->encounter_id;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	protected function _getVisitDetails() {
		$ret = array();
		$ret[] = array('id'=>'visit_type','text'=>__('Visit Type'));
		$ret[] = array('id'=>'diagnosis','text'=>__('Diagnosis'));
		$ret[] = array('id'=>'procedures','text'=>__('Procedures'));
		$ret[] = array('id'=>'vitals','text'=>__('Vitals'));
		$ret[] = array('id'=>'immunizations','text'=>__('Immunizations'));
		$ret[] = array('id'=>'skin_tests','text'=>__('Skin Tests'));
		$ret[] = array('id'=>'patient_education','text'=>__('Patient Education'));
		$ret[] = array('id'=>'health_factors','text'=>__('Health Factors'));
		$ret[] = array('id'=>'exams','text'=>__('Exams'));
		return $ret;
	}
}
