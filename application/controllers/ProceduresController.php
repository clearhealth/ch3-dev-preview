<?php
/*****************************************************************************
*       ProceduresController.php
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
 * Procedures controller
 */
class ProceduresController extends WebVista_Controller_Action {

	public function listAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$this->_getProcedures()),true);
        }

	protected function _getProcedures() {
		$ret = array();
		$name = PatientProcedure::ENUM_PARENT_NAME;
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = $enumerationIterator->toJsonArray('enumerationId',array('name'));
		return $ret;
	}

	public function listSectionAction() {
		$sectionId = (int)$this->_getParam('section');
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($sectionId,1);
		$rows = array();
		foreach ($enumerationIterator as $enum) {
			$tmp = array();
			$tmp['id'] = $enum->key;
			$tmp['data'][] = '';
			$tmp['data'][] = $enum->name;
			$tmp['data'][] = $enum->key;
			$rows[] = $tmp;
		}
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>$rows),true);
        }

	public function listModifiersAction() {
		$code = $this->_getParam('code','');
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array("rows" => array()),true);
        }

	public function lookupAction() {
		$this->view->jsCallback = $this->_getParam('callback','');
		$this->render('lookup');
	}

	public function processPatientProcedureAction() {
		$patientId = (int)$this->_getParam('patientId');
		$procedures = $this->_getParam('procedures');
		if ($patientId > 0) {
			$patientProcedureIterator = new PatientProcedureIterator();
			$patientProcedureIterator->setFilters(array('patientId'=>$patientId));
			$existingProcedures = $patientProcedureIterator->toArray('code','patientId');
			if (is_array($procedures)) {
				foreach ($procedures as $code=>$procedure) {
					if (isset($existingProcedures[$code])) {
						unset($existingProcedures[$code]);
					}
					$procedure['code'] = $code;
					$procedure['patientId'] = $patientId;
					$patientProcedure = new PatientProcedure();
					$patientProcedure->populateWithArray($procedure);
					$patientProcedure->persist();
				}
			}
			// delete un-used records
			foreach ($existingProcedures as $code=>$patientId) {
				$patientProcedure = new PatientProcedure();
				$patientProcedure->code = $code;
				$patientProcedure->patientId = $patientId;
				$patientProcedure->setPersistMode(WebVista_Model_ORM::DELETE);
				$patientProcedure->persist();
			}
		}
		$data = array();
		$data['msg'] = __('Record saved successfully');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listPatientProceduresAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		if ($patientId > 0) {
			$patientProcedureIterator = new PatientProcedureIterator();
			$patientProcedureIterator->setFilters(array('patientId'=>$patientId));
			$providerIterator = new ProviderIterator();
			$listProviders = $providerIterator->toArray('personId','displayName');
			foreach ($patientProcedureIterator as $proc) {
				$quantity = $proc->quantity;
				if ($quantity > 2) {
					$quantity .= ' times';
				}
				else {
					$quantity .= ' time';
				}
				$provider = '';
				if (isset($listProviders[$proc->providerId])) {
					$provider = $listProviders[$proc->providerId];
				}
				$tmp = array();
				$tmp['id'] = $proc->code;
				$tmp['data'][] = $quantity;
				$tmp['data'][] = $proc->procedure;
				$tmp['data'][] = $provider;
				$tmp['data'][] = $proc->providerId;
				$tmp['data'][] = $proc->comments;
				$rows[] = $tmp;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function generateTestEnumDataAction() {
		Enumeration::generateProcedurePreferencesEnum();
		echo 'Done';
		die;
	}

}
