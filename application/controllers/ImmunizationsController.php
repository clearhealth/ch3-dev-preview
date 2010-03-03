<?php
/*****************************************************************************
*       ImmunizationsController.php
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


/**
 * Immunization controller
 */
class ImmunizationsController extends WebVista_Controller_Action {

	public function selectionListAction() {
		$id = (int)$this->_getParam('id');
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($id,1);
		$lists = array();
		foreach ($enumerationIterator as $enum) {
			$lists[$enum->key] = $enum->name;
		}
		$this->view->jsCallback = $this->_getParam('jsCallback','');
		$this->view->lists = $lists;
		$this->render();
	}

	public function immunizationsListAction() {
		$immunization = new ProcedureCodesImmunization();
		$immunizationIterator = $immunization->getIterator();
		$data = array();
		$data['rows'] = $immunizationIterator->toJsonArray('code',array('textShort','code'));
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processPatientImmunizationAction() {
		$patientId = (int)$this->_getParam("patientId");
		$immunizations = $this->_getParam("immunizations");
		if ($patientId > 0) {
			$patientImmunizationIterator = new PatientImmunizationIterator();
			$filter = array();
			$filter['patientId'] = $patientId;
			$patientImmunizationIterator->setFilter($filter);
			$existingImmunizations = $patientImmunizationIterator->toArray('code','immunization');
			if (is_array($immunizations)) {
				foreach ($immunizations as $code=>$immunization) {
					if (isset($existingImmunizations[$code])) {
						unset($existingImmunizations[$code]);
					}
					$patientImmunization = new PatientImmunization();
					$immunization['code'] = $code;
					$immunization['patientId'] = $patientId;
					trigger_error(print_r($immunization,true),E_USER_NOTICE);
					$patientImmunization->populateWithArray($immunization);
					$patientImmunization->persist();
				}
			}
			// delete un-used records
			foreach ($existingImmunizations as $code=>$immunization) {
				$patientImmunization = new PatientImmunization();
				$patientImmunization->code = $code;
				$patientImmunization->setPersistMode(WebVista_Model_ORM::DELETE);
				$patientImmunization->persist();
			}
		}
		$data = array();
		$data['msg'] = __("Record saved successfully");
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listPatientImmunizationsJsonAction() {
		$patientId = (int)$this->_getParam("patientId");
		$rows = array();
		if ($patientId > 0) {
			$patientImmunizationIterator = new PatientImmunizationIterator();
			$filter = array();
			$filter['patientId'] = $patientId;
			$patientImmunizationIterator->setFilter($filter);
			$rows = $patientImmunizationIterator->toJsonArray('code',array('dateAdministered','lot','route','site','series','reaction','immunization','patientReported','comment'));
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processEditImmunizationAction() {
		$immunizations = $this->_getParam("immunizations");
		$patientImmunization = new PatientImmunization();
		$patientImmunization->populateWithArray($immunizations);
		$patientImmunization->persist();
		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processDeleteImmunizationAction() {
		$code = $this->_getParam('code');
		$patientImmunization = new PatientImmunization();
		$patientImmunization->code = $code;
		$patientImmunization->setPersistMode(WebVista_Model_ORM::DELETE);
		$patientImmunization->persist();
		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function immunizationContextMenuAction() {
		header('Content-Type: application/xml;');
		$this->render();
	}

	public function listImmunizationSectionNameAction() {
		$sectionId = (int)$this->_getParam('sectionId');
		if (!$sectionId > 0) $this->_helper->autoCompleteDojo(array());
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($sectionId,1);
		$rows = array();
		foreach ($enumerationIterator as $enum) {
			$tmp = array();
			$tmp['id'] = $enum->key;
			$tmp['data'][] = '';
			$tmp['data'][] = $enum->name;
			$tmp['data'][] = '';//$enum->key;
			$rows[] = $tmp;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows' => $rows),true);
	}

}
