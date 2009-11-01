<?php
/*****************************************************************************
*       EducationsController.php
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
 * Educations controller
 */
class EducationsController extends WebVista_Controller_Action {

	public function listAction() {
                $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
                $json->direct(array('rows'=>Enumeration::enumerationToJson(PatientEducation::ENUM_PARENT_NAME)),true);
        }

	protected function _getEnumerationByName($name) {
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enumeration) {
			$ret[$enumeration->key] = $enumeration->name;
		}
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

	public function lookupAction() {
		$this->view->jsCallback = $this->_getParam('callback','');
		$this->view->listEducationTopics = $this->_getEnumerationByName(PatientEducation::ENUM_TOPIC_PARENT_NAME);
		$this->render('lookup');
	}

	public function processPatientEducationsAction() {
		$patientId = (int)$this->_getParam('patientId');
		$educations = $this->_getParam('education');
		if ($patientId > 0) {
			$patientEducationIterator = new PatientEducationIterator();
			$patientEducationIterator->setFilters(array('patientId'=>$patientId));
			$existingEducations = $patientEducationIterator->toArray('code','patientId');
			if (is_array($educations)) {
				foreach ($educations as $code=>$education) {
					if (isset($existingEducations[$code])) {
						unset($existingEducations[$code]);
					}
					$education['code'] = $code;
					$education['patientId'] = $patientId;
					$patientEducation = new PatientEducation();
					$patientEducation->populateWithArray($education);
					$patientEducation->persist();
				}
			}
			// delete un-used records
			foreach ($existingEducations as $code=>$patientId) {
				$patientEducation = new PatientEducation();
				$patientEducation->code = $code;
				$patientEducation->patientId = $patientId;
				$patientEducation->setPersistMode(WebVista_Model_ORM::DELETE);
				$patientEducation->persist();
			}
		}
		$data = array();
		$data['msg'] = __('Record saved successfully');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listPatientEducationsAction() {
		$patientId = (int)$this->_getParam('patientId');
		$rows = array();
		if ($patientId > 0) {
			$patientEducationIterator = new PatientEducationIterator();
			$patientEducationIterator->setFilters(array('patientId'=>$patientId));
			$listLevels = $this->_getEnumerationByName(PatientEducation::ENUM_LEVEL_PARENT_NAME);
			foreach ($patientEducationIterator as $education) {
				$level = '';
				if (isset($listLevels[$education->level])) {
					$level = $listLevels[$education->level];
				}
				$tmp = array();
				$tmp['id'] = $education->code;
				$tmp['data'][] = $level;
				$tmp['data'][] = $education->education;
				$tmp['data'][] = $education->level;
				$tmp['data'][] = $education->comments;
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
		Enumeration::generatePatientEducationPreferencesEnum();
		Enumeration::generateEducationTopicPreferencesEnum();
		Enumeration::generateEducationLevelPreferencesEnum();
		echo 'Done';
		die;
	}

}
