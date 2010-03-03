<?php
/*****************************************************************************
*       DiagnosisController.php
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
 * Diagnosis controller
 */
class DiagnosisController extends WebVista_Controller_Action {

	public function lookupAction() {
		$this->view->jsCallback = $this->_getParam('jsCallback','');
		$this->render();
	}

	public function lookupDiagnosisAction() {
		$q = $this->_getParam('q');
		$q = preg_replace('/[^a-zA-Z0-9\%\.]/','',$q);

		$rows = array();
		if (strlen($q) > 0) {
			$diagnosisCodeIterator = new DiagnosisCodesICDIterator();
			$diagnosisCodeIterator->setFilter($q);
			$icd = $diagnosisCodeIterator->toJsonArray('code',array('textShort','code'));

			$diagnosisCodeSNOMEDIterator = new DiagnosisCodesSNOMEDIterator();
			$diagnosisCodeSNOMEDIterator->setFilter($q);
			$snomed = $diagnosisCodeSNOMEDIterator->toJsonArray('snomedId',array('description','snomedId'));
			$rows = array_merge($icd,$snomed);
		}

		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listPatientDiagnosesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		if ($patientId > 0) {
			$filters = array();
			$filters['status'] = 'Active';
			$filters['personId'] = $patientId;
			$problemListIterator = new ProblemListIterator();
			$problemListIterator->setFilters($filters);
			$diagnosesSections = array();
			foreach ($problemListIterator as $problem) {
				$diagnosesSections[$problem->code] = $problem->codeTextShort;
			}
			// add to problem list, primary, diagnosis, comment
			$patientDiagnosisIterator = new PatientDiagnosisIterator();
			$patientDiagnosisIterator->setFilters(array('patientId'=>$patientId));
			foreach ($patientDiagnosisIterator as $patientDiagnosis) {
				$tmp = array();
				$tmp['id'] = $patientDiagnosis->code;
				$tmp['data'][] = $patientDiagnosis->addToProblemList;
				$tmp['data'][] = $patientDiagnosis->isPrimary;
				$tmp['data'][] = $patientDiagnosis->diagnosis;
				$tmp['data'][] = $patientDiagnosis->comments;
				$tmp['data'][] = isset($diagnosesSections[$patientDiagnosis->code])?'1':'';
				$rows[] = $tmp;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processPatientDiagnosesAction() {
		$patientId = (int)$this->_getParam('patientId');
		$diagnoses = $this->_getParam('diagnoses');
		if ($patientId > 0) {
			$patientDiagnosisIterator = new PatientDiagnosisIterator();
			$patientDiagnosisIterator->setFilters(array('patientId'=>$patientId));
			$existingDiagnoses = $patientDiagnosisIterator->toArray('code','patientId');
			foreach ($diagnoses as $code=>$diagnosis) {
				if (isset($existingDiagnoses[$code])) {
					unset($existingDiagnoses[$code]);
				}
				$diagnosis['code'] = $code;
				$diagnosis['patientId'] = $patientId;
				$patientDiagnosis = new PatientDiagnosis();
				$patientDiagnosis->code = $code;
				$patientDiagnosis->populate();
				if ($patientDiagnosis->dateTime == '0000-00-00 00:00:00') {
					$diagnosis['dateTime'] = date('Y-m-d H:i:s');
				}
				$patientDiagnosis->populateWithArray($diagnosis);
				$patientDiagnosis->persist();
			}
			// delete un-used records
			foreach ($existingDiagnoses as $code=>$patientId) {
				$patientDiagnosis = new PatientDiagnosis();
				$patientDiagnosis->code = $code;
				$patientDiagnosis->patientId = $patientId;
				$patientDiagnosis->setPersistMode(WebVista_Model_ORM::DELETE);
				$patientDiagnosis->persist();
			}
		}
		$data = array();
		$data['msg'] = __('Record saved successfully');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$this->_getDiagnoses()),true);
        }

	protected function _getDiagnoses() {
		// STUB method?
		$ret = array();
		$types = array();
		$types['problem_list_items'] = 'Problem List Items';
		//$types['personal_diagnoses'] = 'Personal Diagnoses List Items';
		foreach ($types as $id=>$type) {
			$data = array();
			$data['id'] = $id;
			$data['data'][] = $type;
			$ret[] = $data;
		}
		return $ret;
	}

	public function listSectionAction() {
		$sections = array();
		$diagnosis = $this->_getParam('diagnosis',null);
		if ($diagnosis !== null) {
			$sections  = $this->_getDiagnosesSection($diagnosis);
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$sections),true);
        }

	protected function _getDiagnosesSection($diagnosis) {
		// STUB method?
		$ret = array();
		switch ($diagnosis) {
			case 'problem_list_items':
				$personId = (int)$this->_getParam('personId');
				$filters = array();
				$filters['status'] = 'Active';
				$filters['personId'] = $personId;
				$problemListIterator = new ProblemListIterator();
				$problemListIterator->setFilters($filters);
				foreach ($problemListIterator as $problem) {
					$tmp = array();
					$tmp['id'] = $problem->code;
					$tmp['data'][] = '';
					$tmp['data'][] = $problem->codeTextShort;
					$tmp['data'][] = $problem->code;
					$ret[] = $tmp;
				}
				break;
		}
		return $ret;
	}



}
