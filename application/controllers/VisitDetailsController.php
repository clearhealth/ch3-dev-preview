<?php
/*****************************************************************************
*       VisitDetailsController.php
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


class VisitDetailsController extends WebVista_Controller_Action {

	/* VISIT TYPES SECTION */

	public function visitTypeJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $this->_getVisitTypes()),true);
        }

	private function _getVisitTypes() {
		// STUB method?
		$ret = array();
		$types = array();
		$types['new_patient'] = 'New Patient';
		$types['established_patient'] = 'Established Patient';
		$types['consultations'] = 'Consultations';
		foreach ($types as $id=>$type) {
			$data = array();
			$data['id'] = $id;
			$data['data'][] = $type;
			$ret[] = $data;
		}
		return $ret;
	}

	public function visitSectionJsonAction() {
		$sections = array();
		$visitType = $this->_getParam('visitType',null);
		if ($visitType !== null) {
			$sections  = $this->_getVisitSections($visitType);
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $sections),true);
        }

	private function _getVisitSections($visitType) {
		// STUB method?
		$ret = array();
		switch ($visitType) {
			case 'established_patient':
				$data = array();
				$data['id'] = '99211';
				$data['data'][] = '';
				$data['data'][] = 'Brief Exam 1-5Min';
				$data['data'][] = '99211';
				$ret[] = $data;
				$data = array();
				$data['id'] = '99212';
				$data['data'][] = '';
				$data['data'][] = 'Limited Exam 6-10Min';
				$data['data'][] = '99212';
				$ret[] = $data;
				$data = array();
				$data['id'] = '99213';
				$data['data'][] = '';
				$data['data'][] = 'Intermediate Exam 11-19Min';
				$data['data'][] = '99213';
				$ret[] = $data;
				$data = array();
				$data['id'] = '99214';
				$data['data'][] = '';
				$data['data'][] = 'Extended Exam 20-30Min';
				$data['data'][] = '99214';
				$ret[] = $data;
				$data = array();
				$data['id'] = '99215';
				$data['data'][] = '';
				$data['data'][] = 'Comprehensive Exam 31+ Min';
				$data['data'][] = '99215';
				$ret[] = $data;
				break;
		}
		return $ret;
	}

	public function providersJsonAction() {
		$providerIterator = new ProviderIterator();
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $providerIterator->toJsonArray('personId',array('displayName'))),true);
	}

	public function currentProvidersJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => array()),true);
	}

	public function visitModifiersJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => array()),true);
	}


	/* VITALS SECTION */

	public function vitalsJsonAction() {
		$rows = array();
		$vitals = new VitalSignGroup();
		$vitalsIter = $vitals->getIterator();
		foreach ($vitalsIter as $vitals) {
			foreach ($vitals->vitalSignValues as $vitalSign) {
				$tmp = array();
				$tmp['id'] = $vitalSign->vitalSignValueId;
				$tmp['data'][] = $vitals->dateTime;
				$tmp['data'][] = $vitalSign->vital;
				$tmp['data'][] = $vitalSign->value; //USS Value
				$tmp['data'][] = 'Metric Value';
				$tmp['data'][] = ''; //Qualifiers
				$tmp['data'][] = $vitals->enteringUserId;
				$rows[] = $tmp;
			}
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $rows),true);
        }


	/* IMMUNIZATION SECTION */

	public function immunizationsJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $this->_getImmunization()),true);
        }

	private function _getImmunization() {
		$ret = array();
		$immunizationName = "Sections";
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($immunizationName);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = $enumerationIterator->toJsonArray('enumerationId',array('name'));
		return $ret;
	}

	public function immunizationsSectionJsonAction() {
		$immunizations = $this->_getParam('immunizations','');
		$otherPref = 'other_';
		$rows = array();
		if (substr($immunizations,0,strlen($otherPref)) == $otherPref) { // others/immunization
			$immunizations = substr($immunizations,strlen($otherPref));
			$procedureCodesImmunizationIterator = new ProcedureCodesImmunizationIterator();
			$procedureCodesImmunizationIterator->setFilters($immunizations);
			foreach ($procedureCodesImmunizationIterator as $procedure) {
				$tmp = array();
				$tmp['id'] = $otherPref.$procedure->code;
				$tmp['data'][] = '';
				$tmp['data'][] = $procedure->textLong;
				$tmp['data'][] = $procedure->code;
				$rows[] = $tmp;
			}
		}
		else { // enumeration
			$enumeration = new Enumeration();
			$enumeration->enumerationId = (int)$immunizations;
			$enumeration->populate();
			$enumerationsClosure = new EnumerationsClosure();
			$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
			//$rows = $enumerationIterator->toJsonArray('enumerationId',array('name'));
			foreach ($enumerationIterator as $enum) {
				$tmp = array();
				$tmp['id'] = $enum->enumerationId;
				$tmp['data'][] = '';
				$tmp['data'][] = $enum->name;
				$tmp['data'][] = $enum->key;
				$rows[] = $tmp;
			}
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => $rows),true);
        }

	public function immunizationsSeriesJsonAction() {
		$id = (int)$this->_getParam("id");
		$enumeration = new Enumeration();
		$enumeration->enumerationId = (int)$id;
		$enumeration->populate();
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		//$rows = $enumerationIterator->toJsonArray('enumerationId',array('name'));
		$rows = array();
		foreach ($enumerationIterator as $enum) {
			$rows[] = array('id'=>$enum->enumerationId,'data'=>$enum->name);
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows"=>$rows),true);
        }


	/* HEALTH STATUS (HSA) SECTION */

	public function hsaJsonAction() {
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName(HealthStatusAlert::ENUM_PARENT_NAME);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$rows = $enumerationIterator->toJsonArray('enumerationId',array('name'));
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$rows),true);
        }

	public function hsaSectionJsonAction() {
		$hsa = $this->_getParam('hsa');
		$enumeration = new Enumeration();
		$enumeration->enumerationId = (int)$hsa;
		$enumeration->populate();
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$rows = array();
		foreach ($enumerationIterator as $enum) {
			$tmp = array();
			$tmp['id'] = $enum->enumerationId;
			$tmp['data'][] = '';
			$tmp['data'][] = $enum->name;
			$tmp['data'][] = $enum->key;
			$rows[] = $tmp;
		}
		// temporarily set rows to all defined HSA handlers
		$rows = array();
		$handler = new Handler(Handler::HANDLER_TYPE_HSA);
		$handlerIterator = $handler->getIterator();
		foreach ($handlerIterator as $row) {
			$tmp = array();
			$tmp['id'] = $row->handlerId;
			$tmp['data'][] = '';
			$tmp['data'][] = $row->name;
			$tmp['data'][] = $row->timeframe;
			$rows[] = $tmp;
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$rows),true);
        }


	/* EXAMS SECTION */

	public function examsJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => array()),true);
        }

	public function examsSectionJsonAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => array()),true);
        }


	public function listPatientVisitTypesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		if ($patientId > 0) {
			$patientVisitTypeIterator = new PatientVisitTypeIterator();
			$patientVisitTypeIterator->setFilters(array('patientId'=>$patientId));
			$providerIterator = new ProviderIterator();
			$listProviders = $providerIterator->toArray('personId','displayName');
			foreach ($patientVisitTypeIterator as $visitType) {
				$provider = '';
				if (isset($listProviders[$visitType->providerId])) {
					$provider = $listProviders[$visitType->providerId];
				}
				$tmp = array();
				$tmp['id'] = $visitType->providerId;
				$tmp['data'][] = $provider;
				$tmp['data'][] = ($visitType->isPrimary)?__('Primary'):'';
				$rows[] = $tmp;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processPatientVisitTypesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$visitTypes = $this->_getParam('visitTypes');
		if ($patientId > 0) {
			$patientVisitTypeIterator = new PatientVisitTypeIterator();
			$patientVisitTypeIterator->setFilters(array('patientId'=>$patientId));
			$existingVisitTypes = $patientVisitTypeIterator->toArray('providerId','patientId');
			if (is_array($visitTypes)) {
				foreach ($visitTypes as $providerId=>$visitType) {
					if (isset($existingVisitTypes[$providerId])) {
						unset($existingVisitTypes[$providerId]);
					}
					$visitType['providerId'] = $providerId;
					$visitType['patientId'] = $patientId;
					$patientVisitType = new PatientVisitType();
					$patientVisitType->populateWithArray($visitType);
					$patientVisitType->persist();
				}
			}
			// delete un-used records
			foreach ($existingVisitTypes as $providerId=>$patientId) {
				$patientVisitType = new PatientVisitType();
				$patientVisitType->providerId = $providerId;
				$patientVisitType->patientId = $patientId;
				$patientVisitType->setPersistMode(WebVista_Model_ORM::DELETE);
				$patientVisitType->persist();
			}
		}
		$data = array();
		$data['msg'] = __('Record saved successfully');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}
}
