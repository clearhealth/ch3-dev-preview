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
		$this->view->jsCallback = $this->_getParam('jsCallback','');
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
			$rows = $patientImmunizationIterator->toJsonArray('code',array('reportedNotAdministered','series','reaction','repeatContraindicated','immunization','comment','reportedNotAdministered','patientReported'));
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
