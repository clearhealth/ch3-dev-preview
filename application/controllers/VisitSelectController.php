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
		$personId = (int)$this->_getParam('personId');
		$this->view->personId = $personId;
		$visitId = (int)$this->_getParam('visitId');
		$this->view->visitId = $visitId;

		$identity = Zend_Auth::getInstance()->getIdentity();
		$currentUserPersonId = (int)$identity->personId;

		$userId = $identity->userId;
		$user = new User();
		$user->userId = $userId;
		$user->populate();
		$defaultLocationId = 0;
		if (strlen($user->preferences) > 0) {
			$xmlPreferences = new SimpleXMLElement($user->preferences);
			$defaultLocationId = (int)$xmlPreferences->currentLocation;
		}
		if (!$defaultLocationId > 0) {
			$defaultLocationId = (int)$identity->default_location_id;
		}
		$defaultPracticeId = 0;
		$defaultBuildingId = 0;
		if ($defaultLocationId > 0) {
			$room = new Room();
			$room->roomId = $defaultLocationId;
			$room->populate();
			$defaultBuildingId = $room->building->buildingId;
			$defaultPracticeId = $room->building->practice->practiceId;
		}
		$this->view->defaultPracticeId = $defaultPracticeId;
		$this->view->defaultBuildingId = $defaultBuildingId;
		$this->view->currentUserPersonId = $currentUserPersonId;
		$this->view->defaultLocationId = $defaultLocationId;
		$this->view->defaultDateOfService = date('Y-m-d');

		$facilityIterator = new FacilityIterator();
		$providerIterator = new ProviderIterator();
		$this->view->facilityIterator = $facilityIterator;
		$this->view->providerIterator = $providerIterator;

		$this->view->visitDetails = $this->_getVisitDetails();

		$insuredRelationship = new InsuredRelationship();
		$insuredRelationship->personId = $personId;
		$this->view->insurancePrograms = $insuredRelationship->getProgramList();

		$this->view->currentActivePayer = $insuredRelationship->getDefaultActivePayer();

		$visit = null;
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
		}
		$this->view->visit = $visit;

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

	public function claimAction() {
		$visitId = (int)$this->_getParam('visitId');
		$listPayments = array();
		$listCharges = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();

			$appointment = new Appointment();
			$appointment->appointmentId = $visit->appointmentId;
			$appointment->populate();
			$personId = (int)$appointment->patientId;

			$payment = new Payment();
			$paymentIterator = $payment->getIteratorByVisitId($visit->visitId);
			//$paymentIterator = $payment->getMostRecentPayments();
			foreach ($paymentIterator as $pay) {
				$listPayments[$pay->paymentId] = array(
					date('Y-m-d',strtotime($pay->paymentDate)), // date
					$pay->paymentType, // type
					$pay->amount, // amount
					$pay->title, // note
				);
			}

			$miscCharge = new MiscCharge();
			$results = $miscCharge->getUnpaidCharges();
			foreach ($results as $id=>$row) {
				$listCharges[$id] = array(
					$row['date'], // date
					$row['type'], // type
					$row['amount'], // amount
					$row['note'], // note
				);
			}
		}
		$this->view->listPayments = $listPayments;
		$this->view->listCharges = $listCharges;
		$this->render();
	}

	public function vitalsAction() {
		$this->render();
	}

	public function immunizationsAction() {
		$enumerationsClosure = new EnumerationsClosure();

		$othersId = 0;
		$series = array();
		$sites = array();
		$sections = array();
		$reactions = array();
		$routes = array();
		$parentName = PatientImmunization::ENUM_PARENT_NAME;
		$enumeration = new Enumeration();
		$enumeration->populateByUniqueName($parentName);
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		foreach ($enumerationIterator as $enum) {
			switch ($enum->name) {
				case PatientImmunization::ENUM_SERIES_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					$series = $enumIterator->toArray('key','name');
					break;
				case PatientImmunization::ENUM_BODY_SITE_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					$sites = $enumIterator->toArray('key','name');
					break;
				case PatientImmunization::ENUM_SECTION_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					foreach ($enumIterator as $item) {
						if ($item->name == PatientImmunization::ENUM_SECTION_OTHER_NAME) {
							$othersId = $item->enumerationId;
							continue;
						}
						$sections[$item->enumerationId] = $item->name;
					}
					break;
				case PatientImmunization::ENUM_REACTION_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					$reactions = $enumIterator->toArray('key','name');
					break;
				case PatientImmunization::ENUM_ADMINISTRATION_ROUTE_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					$routes = $enumIterator->toArray('key','name');
					break;
			}
		}
		$this->view->othersId = $othersId;
		$this->view->series = $series;
		$this->view->sites = $sites;
		$this->view->sections = $sections;
		$this->view->reactions = $reactions;
		$this->view->routes = $routes;

		$this->render();
	}

	public function educationAction() {
		$enumerationsClosure = new EnumerationsClosure();

		$othersId = 0;
		$levels = array();
		$sections = array();
		$parentName = PatientEducation::ENUM_EDUC_PARENT_NAME;
		$enumeration = new Enumeration();
		$enumeration->populateByUniqueName($parentName);
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		foreach ($enumerationIterator as $enum) {
			switch ($enum->name) {
				case PatientEducation::ENUM_EDUC_LEVEL_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					foreach ($enumIterator as $item) {
						$levels[$item->enumerationId] = $item->name;
					}
					break;
				case PatientEducation::ENUM_EDUC_SECTION_NAME:
					$enumIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
					foreach ($enumIterator as $item) {
						if ($item->name == PatientEducation::ENUM_EDUC_SECTION_OTHER_NAME) {
							$othersId = $item->enumerationId;
							continue;
						}
						$sections[$item->enumerationId] = $item->name;
					}
					break;
			}
		}
		$this->view->othersId = $othersId;
		$this->view->levels = $levels;
		$this->view->sections = $sections;

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
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$rows = array();
		foreach ($visitIterator as $visit) {
			$row = array();
			$row['id'] = $visit->visitId;
			$row['data'][] = $visit->displayDateOfService;
			$row['data'][] = $visit->locationName;
			$row['data'][] = $visit->providerDisplayName;
			$row['data'][] = $visit->insuranceProgram;
			$row['userdata']['locationId'] = $visit->practiceId.':'.$visit->buildingId.':'.$visit->roomId;
			$row['userdata']['providerId'] = $visit->treatingPersonId;
			$row['userdata']['activePayerId'] = $visit->activePayerId;
			$rows[] = $row;
		}
		$json->direct(array('rows' => $rows),true);
        }

	function processAddVisitAction() {
		$visitParams = $this->_getParam('visit');
		$visitParams['created_by_user_id'] = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$visitParams['date_of_treatment'] = date('Y-m-d');
		$visitParams['timestamp'] = date('Y-m-d h:i:s');
		$visit = new Visit();
		$visitId = (int)$visitParams['visitId'];
		if ($visitId > 0) {
			$visit->visitId = $visitId;
			$visit->populate();
		}
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

	public function processCloseAction() {
		$visitId = (int)$this->_getParam('visitId');
		$data = false;
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
			$visit->closed = 1;
			$visit->persist();
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processVoidAction() {
		$visitId = (int)$this->_getParam('visitId');
		$data = false;
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
			$visit->void = 1;
			$visit->persist();
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processReopenAction() {
		$visitId = (int)$this->_getParam('visitId');
		$data = false;
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
			$visit->closed = 0;
			$visit->persist();
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function visitDetailsAction() {
		$this->render();
	}

}
