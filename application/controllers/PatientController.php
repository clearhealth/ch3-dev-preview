<?php
/*****************************************************************************
*       PatientController.php
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


class PatientController extends WebVista_Controller_Action {

	protected $_form = null;
	protected $_patient = null;
	protected $_insurer = null;

	public function indexAction() {
		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice'));
                $this->_form = new WebVista_Form(array('name' => 'patient-new'));
                $this->_form->setAction(Zend_Registry::get('baseUrl') . "patient.raw/edit-details");
                $this->_patient = new Patient();
                $this->_form->loadORM($this->_patient, "Patient");
                $this->view->form = $this->_form;
		$this->view->facilityIterator = $facilityIterator;
	}

	public function ajaxSetPatientDefaultPharmacyAction() {
		$retval = false;
		$personId = (int) $this->_getParam('personId');
		$pharmacyId = preg_replace('/[^a-zA-Z0-9]/','',$this->_getParam('pharmacyId'));
		if ($personId > 0 && strlen($pharmacyId) > 0) {
			$patient = new Patient();
			$patient->personId = $personId;
			if ($patient->populate()) {
				$patient->defaultPharmacyId = $pharmacyId;
				$patient->persist();
				$retval = true;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
		if ($retval == false) {
			//$this->getResponse()->setHttpResponseCode(500);
                        $json->direct(array('error' => __('There was an error attempting to set the selected pharmacy as default for the patient.')));
			return;
		}
                $json->direct(true);
		
	}

	public function detailsAction() {
		$patientId = (int)$this->_getParam('patientId');
		$this->_patient = new Patient();
		$this->_patient->person_id = $patientId;
		$this->_patient->populate();

		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice'));
		$this->_form = new WebVista_Form(array('name' => 'patient-details'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "patient.raw/process-details");
		$this->_form->loadORM($this->_patient,"Patient");
		$this->_form->setWindow('windowPatientDetailsId');
		$this->view->form = $this->_form;
		$this->view->facilityIterator = $facilityIterator;
		$this->view->reasons = $this->_getReasons();

		$this->view->statesList = Address::getStatesList();
		$this->render();
	}

	protected function _getReasons() {
		$reasons = array();
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName(PatientNote::ENUM_REASON_PARENT_NAME);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ctr = 0;
		foreach ($enumerationIterator as $enum) {
			// since data type of patient_note.reason is tinyint we simply use the counter as id
			$reasons[$ctr++] = $enum->name;
		}
		return $reasons;
	}

	public function processDetailsAction() {
		$retval = false;
		$params = $this->_getParam('patient');
		$patientId = (int)$params['personId'];
		if ($patientId > 0) {
			if (!(int)$params['person']['personId'] > 0) {
				$params['person']['personId'] = $patientId;
			}
			if (isset($params['person']['active']) && $params['person']['active']) {
				$params['person']['active'] = 1;
			}
			else {
				$params['person']['active'] = 0;
			}
			$patient = new Patient();
			$patient->person_id = $patientId;
			$patient->populate();
			$patient->populateWithArray($params);
			$patient->person->person_id = $patientId;
			$patient->person->populate();
			$patient->person->populateWithArray($params['person']);
			$patient->persist();
			$retval = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$data = __('Record updated successfully.');
		if ($retval == false) {
			$data = __('There was an error attempting to update patient details.');
		}
		$json->direct($data);
	}

	public function accountHistoryAction() {
	}

	public function processEditByFieldAction() {
		$personId = (int)$this->_getParam("personId");
		$type = $this->_getParam("type");
		$id = (int)$this->_getParam("id");
		$field = $this->_getParam("field");
		$value = $this->_getParam("value");

		$obj = null;
		switch ($type) {
			case 'address':
				$obj = new Address();
				$obj->person_id = $personId;
				break;
			case 'phone':
				$obj = new PhoneNumber();
				$obj->person_id = $personId;
				break;
			case 'note':
				$obj = new PatientNote();
				$obj->patient_id = $personId;
				if ($id === 0) {
					// defaults for new note
					$obj->note_date = date('Y-m-d H:i:s');
					$obj->user_id = (int)Zend_Auth::getInstance()->getIdentity()->personId;
					$obj->priority = 5;
					$obj->active = 1;
				}
				break;
			default:
				break;
		}

		$retVal = false;
		if ($obj !== null && in_array($field,$obj->ormFields())) {
			if ($id > 0) {
				foreach ($obj->_primaryKeys as $k) {
					$obj->$k = $id;
				}
				$obj->populate();
			}
			$obj->$field = $value;
			$obj->persist();
			$retVal = true;
		}
		if ($retVal) {
			$data = true;
		}
		else {
			$data = array('error' => __('There was an error attempting to update the selected record.'));
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processDeleteAction() {
		$personId = (int)$this->_getParam("personId");
		$type = $this->_getParam("type");
		$id = (int)$this->_getParam("id");

		$obj = null;
		switch ($type) {
			case 'address':
				$obj = new Address();
				break;
			case 'phone':
				$obj = new PhoneNumber();
				break;
			case 'note':
				$obj = new PatientNote();
				break;
			default:
				break;
		}

		$retVal = false;
		if ($obj !== null && $id > 0) {
			foreach ($obj->_primaryKeys as $k) {
				$obj->$k = $id;
			}
			$obj->setPersistMode(WebVista_Model_ORM::DELETE);
			$obj->persist();
			$retVal = true;
		}
		if ($retVal) {
			$data = true;
		}
		else {
			$data = array('error' => __('There was an error attempting to delete the selected record.'));
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function ajaxListPhonesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		$tmp = array();
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('number')
				->where('person_id = ?',$patientId);
		$phoneNumberIterator = new PhoneNumberIterator();
		$phoneNumberIterator->setDbSelect($sqlSelect);
		foreach ($phoneNumberIterator as $phone) {
			$tmp = array();
			$tmp['id'] = $phone->number_id;
			$tmp['data'][] = $phone->name;
			$tmp['data'][] = $phone->type;
			$tmp['data'][] = $phone->number;
			$tmp['data'][] = $phone->notes;
			$tmp['data'][] = $phone->active;
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function ajaxListAddressesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('address')
				->where('person_id = ?',$patientId);
		$addressIterator = new AddressIterator();
		$addressIterator->setDbSelect($sqlSelect);
		foreach ($addressIterator as $addr) {
			$tmp = array();
			$tmp['id'] = $addr->address_id;
			$tmp['data'][] = $addr->name;
			$tmp['data'][] = $addr->type;
			$tmp['data'][] = $addr->line1;
			$tmp['data'][] = $addr->line2;
			$tmp['data'][] = $addr->city;
			//$tmp['data'][] = $addr->region;
			$tmp['data'][] = $addr->state;
			$tmp['data'][] = $addr->postal_code;
			$tmp['data'][] = $addr->notes;
			$tmp['data'][] = $addr->active;
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function ajaxGetContextMenuAction() {
		header('Content-Type: application/xml;');
		$this->render();
	}

	public function listNotesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		$patientNote = new PatientNote();
		$patientNoteIterator = $patientNote->getIterator();
		$filters = array();
		$filters['patient_id'] = $patientId;
		$filters['active'] = 1;
		$filters['posting'] = 0;
		$patientNoteIterator->setFilters($filters);
		$reasons = $this->_getReasons();
		foreach ($patientNoteIterator as $note) {
			$tmp = array();
			$tmp['id'] = $note->patient_note_id;
			$tmp['data'][] = $note->priority;
			$tmp['data'][] = $note->note_date;
			$tmp['data'][] = $note->user->username;
			$tmp['data'][] = isset($reasons[$note->reason])?$reasons[$note->reason]:'';
			$tmp['data'][] = $note->note;
			$tmp['data'][] = ($note->active)?__('No'):__('Yes');
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function listInsurersAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		$insurancePrograms = InsuranceProgram::getInsurancePrograms();

		$insuredRelationship = new InsuredRelationship();
		$insuredRelationshipIterator = $insuredRelationship->getIteratorByPersonId($patientId);
		$subscribers = array(); // TODO: get the actual subscribers;
		foreach ($insuredRelationshipIterator as $item) {
			$company = '';
			$program = '';
			if (isset($insurancePrograms[$item->insuranceProgramId])) {
				$exp = explode('->',$insurancePrograms[$item->insuranceProgramId]);
				$company = $exp[0];
				$program = $exp[1];
			}
			$subscriber = '';
			if (isset($subscribers[$item->subscriberId])) {
				$subscriber = $subscribers[$item->subscriberId];
			}
			$effectiveEnd = date('m/d/Y',strtotime($item->effectiveEnd));
			$effective = 'Until';
			$effectiveToTime = strtotime($effectiveEnd);
			if ($effectiveToTime <= strtotime(date('m/d/Y'))) {
				$effective = 'Ended';
			}
			$effective .= ' '.$effectiveEnd;
			$tmp = array();
			$tmp['id'] = $item->insuredRelationshipId;
			$tmp['data'][] = $company;
			$tmp['data'][] = $program;
			$tmp['data'][] = $item->groupName;
			$tmp['data'][] = $item->groupNumber;
			$tmp['data'][] = $item->copay;
			$tmp['data'][] = $subscriber;
			$tmp['data'][] = $effective;
			$tmp['data'][] = ($item->active)?__('Yes'):__('No');
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function editInsurerAction() {
		$patientId = (int)$this->_getParam('patientId');
		$id = (int)$this->_getParam('id');
		$this->_insurer = new InsuredRelationship();
		$this->_insurer->personId = $patientId;
		if ($id > 0) {
			$this->_insurer->insuredRelationshipId = $id;
			$this->_insurer->populate();
		}

		$this->_form = new WebVista_Form(array('name' => 'edit-insurer'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "patient.raw/process-edit-insurer");
		$this->_form->loadORM($this->_insurer,"Insurer");
		$this->_form->setWindow('winEditInsurerId');
		$this->view->form = $this->_form;

		$insuranceProgram = new InsuranceProgram();
		$insurancePrograms = array(''=>'');
		foreach (InsuranceProgram::getInsurancePrograms() as $id=>$val) {
			$insurancePrograms[$id] = $val;
		}
		$this->view->insurancePrograms = $insurancePrograms;

		$assignings = array(''=>'');
		$this->view->assignings = $assignings;

		$subscribers = array(''=>'');
		$this->view->subscribers = $subscribers;
		$this->render('edit-insurer');
	}

	public function processEditInsurerAction() {
		$this->editInsurerAction();
		$params = $this->_getParam('insurer');
		$this->_insurer->populateWithArray($params);
		$this->_insurer->persist();
		$this->view->message = __('Record saved successfully');
		$this->render('edit-insurer');
	}

	public function processEditStatsAction() {
		$personId = (int)$this->_getParam('personId');
		$name = $this->_getParam('name');
		$value = $this->_getParam('value');
		$ret = PatientStatisticsDefinition::updatePatientStatistics($personId,$name,$value);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function listStatsAction() {
		$personId = (int)$this->_getParam('personId');
		$psd = new PatientStatisticsDefinition();
		$stats = PatientStatisticsDefinition::getPatientStatistics($personId);
		$psdIterator = $psd->getAllActive();
		$rows = array();
		foreach ($psdIterator as $row) {
			$tmp = array();
			$tmp['id'] = $row->name;
			$tmp['data'] = array();
			$tmp['data'][] = GrowthChartBase::prettyName($row->name);
			$tmp['data'][] = isset($stats[$row->name])?$stats[$row->name]:'';
			$options = array();
			if ($row->type == PatientStatisticsDefinition::TYPE_ENUM) {
				$enumerationClosure = new EnumerationClosure();
				$paths = $enumerationClosure->generatePaths($row->value);
				foreach ($paths as $id=>$name) {
					$options[] = array('key'=>$id,'value'=>$name);
				}
			}
			$tmp['userdata']['type'] = $row->type;
			$tmp['userdata']['options'] = $options;
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

}
