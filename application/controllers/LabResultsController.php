<?php
/*****************************************************************************
*       LabResultsController.php
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
 * Lab Results controller
 */
class LabResultsController extends WebVista_Controller_Action {

	protected $_session = null;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		$this->render();
	}

	/**
	 * Results in JSON format
	 */
	public function resultsJsonAction() {
		$labs = array();
		$labsIterator = new LabsIterator();
		$filters = array();
		$filters['patientId'] = (int)$this->_getParam('patientId');
		$filters['dateBegin'] = date('Y-m-d H:i:s',strtotime($this->_getParam('dateBegin')));
		$filters['dateEnd'] = date('Y-m-d H:i:s',strtotime($this->_getParam('dateEnd')));
		$filters['limit'] = (int)$this->_getParam('limit');
		$selectedLabTests = $this->_getParam('selectedLabTests',0);
		if ($selectedLabTests) {
			$filters['selectedLabTests'] = $this->_session->selectedLabTests;
		}
		$labsIterator->setFilters($filters);
		foreach ($labsIterator as $lab) {
			$tmpArr = array();
			$tmpArr[] = $lab->observationTime;
			$tmpArr[] = $lab->description;
			$tmpArr[] = $lab->value;
			$tmpArr[] = $lab->units;
			$tmpArr[] = $lab->referenceRange;
			$tmpArr[] = $lab->abnormalFlag;
			$tmpArr[] = $lab->resultStatus;
			$tmpValue = $lab->value;
			if (!is_numeric($tmpValue)) {
				if (strtolower($lab->abnormalFlag) != "abnormal") { // normal
					$tmpValue = 1;
				}
				else { // abnormal
					$tmpValue = 0;
				}
			}
			$tmpArr[] = date('Y-m-d',strtotime($lab->observationTime)).'::'.$tmpValue;
			$labs[$lab->labResultId] = $tmpArr;
		}
		$data = array();
		$rows = $this->_toJsonArray($labs);
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	/**
	 * Report Tests View
	 */
	public function reportTestsViewAction() {
		$this->render();
	}

	/**
	 * Lab Tests View
	 */
	public function labTestsViewAction() {
		$this->render();
	}

	/**
	 * Select Lab Tests
	 */
	public function selectLabTestsAction() {
		$tests = array();
		$labsIterator = new LabsIterator();
		foreach ($labsIterator as $lab) {
			// filter empty service lab test (do not include?)
			if (!strlen($lab->labTest->service) > 0) {
				continue;
			}
			$tests[$lab->labTest->labTestId] = $lab->labTest->service;
		}
		asort($tests);
		$this->view->tests = $tests;
		$this->render();
	}

	/**
	 * Reports text/label in JSON format
	 */
	public function sessionSelectedLabTestsAction() {
		//$this->_session->selectedLabTests = $this->_getParam("labTests","");
		$selectedLabTests = $this->_getParam("availableLabTests","");
		$this->_session->selectedLabTests = $selectedLabTests;
		$data = array();
		$data['rows'] = $selectedLabTests;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	/**
	 * Select Lab Tests for Worksheet
	 */
	public function worksheetAction() {
		$tests = array();
		$labsIterator = new LabsIterator();
		foreach ($labsIterator as $lab) {
			// filter empty service lab test (do not include?)
			if (!strlen($lab->labTest->service) > 0) {
				continue;
			}
			$tests[$lab->labTest->labTestId] = $lab->labTest->service;
		}
		$this->view->tests = $tests;
		$this->render();
	}

	/**
	 * Select a Date Range
	 */
	public function dateRangeAction() {
		$this->render();
	}

	/**
	 * Reports text/label in JSON format
	 */
	public function reportsJsonAction() {
		$data = array();
		$data['rows'] = $this->_toJsonArray($this->_getReports());
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	/**
	 * Date Range text/label in JSON format
	 */
	public function dateRangeJsonAction() {
		$data = array();
		$data['rows'] = $this->_toJsonArray($this->_getDateRange());
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	/**
	 * Lab Orders text/label in JSON format
	 * TODO: to be implemented (currently returns empty JSON)
	 */
	public function labOrdersJsonAction() {
		$data = array();
		$data['rows'] = array();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	/**
	 * Convert array to JSON array format helper
	 */
	protected function _toJsonArray($data) {
		$rows = array();
		foreach ($data as $id=>$val) {
			$tmp = array();
			$tmp['id'] = $id;
			if (!is_array($val)) {
				$val = array($val);
			}
			foreach ($val as $v) {
				$tmp['data'][] = $v;
			}
			$rows[] = $tmp;
		}
		return $rows;
	}

	/**
	 * List of available Reports menu/text/label
	 * reportsJsonAction() helper
	 */
	protected function _getReports() {
		$list = array();
		$list['tests_by_date'] = __('Tests By Date');
		$list['selected_tests_by_date'] = __('Selected Tests By Date');
		//$list['worksheet'] = __('Worksheet');
		//$list['cumulative'] = __('Cumulative');
		//$list['microbiology'] = __('Microbiology');
		//$list['anatomic_pathology'] = __('Anatomic Pathology');
		//$list['blood_bank'] = __('Blood Bank');
		//$list['lab_status'] = __('Lab Status');
		return $list;
	}

	/**
	 * List of available Date Range menu/text/label
	 * dateRangeJsonAction() helper
	 */
	protected function _getDateRange() {
		$list = array();
		$list['date_range'] = __('Date Range');
		$list['today'] = __('Today');
		$list['one_week'] = __('One Week');
		$list['two_weeks'] = __('Two Weeks');
		$list['one_month'] = __('One Month');
		$list['six_months'] = __('Six Months');
		$list['one_year'] = __('One Year');
		$list['two_years'] = __('Two Years');
		$list['all_results'] = __('All Results');
		return $list;
	}

	public function loadTestLabsAction() {
		$f = fopen('/tmp/labdata.csv',"r");
		$counter = 0;
		$labOrderId = 0;
		$labTestId = 0;
		while (($line = fgetcsv($f)) && $counter< 100) {
			$date = date('Y-m-d H:i:s',  mt_rand(strtotime('2000-01-01'),strtotime('today')));
			if ($counter == 0 || $counter % 20 == 0) {
				$lo = new LabOrder();
				$lo->patientId = 65650;
				$lo->status = "F";
				$lo->persist();
				$labOrderId = $lo->labOrderId;
				$lt = new LabTest();
				$lt->labOrderId = $labOrderId;
				$lt->observationTime = $date;
				$lt->status = "F";
				$lt->persist();
				$labTestId = $lt->labTestId;
			}
			$lr = new LabResult();
			$lr->value = $line[2];
			$lr->description = $line[0];
			$lr->labTestId = $labTestId;
			//echo $lr->toString();
			$lr->persist();
			$counter++;
		}
		exit;
		
	}

	public function menuXmlAction() {
		$this->render();
	}
}
