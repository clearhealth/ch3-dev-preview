<?php
/*****************************************************************************
*       ClaimsController.php
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


class ClaimsController extends WebVista_Controller_Action {

	protected $_session;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		if (!isset($this->_session->filters)) {
			$filters = array();
			$filters['DOSStart'] = date('Y-m-d',strtotime('-1 month'));
			$filters['DOSEnd'] = date('Y-m-d');
			$filters['facility'] = '';
			$filters['insurer'] = '';
			$tmp = array('active'=>0,'operator'=>'=','operand1'=>'','operand2'=>'');
			$filters['total'] = $tmp;
			$filters['paid'] = $tmp;
			$filters['writeoff'] = $tmp;
			$filters['balance'] = $tmp;
			$this->_session->filters = $filters;
		}
		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice','Building','Room'));
		$this->view->facilityIterator = $facilityIterator;
		$this->view->insurers = InsuranceProgram::getInsurancePrograms();
		$this->view->filters = $this->_session->filters;
		$this->render();
	}

	public function advancedFiltersAction() {
		$this->view->balanceOperators = Claim::balanceOperators();
		$filters = $this->_session->filters;
		if (!isset($filters['total'])) {
			$filters['total'] = array('active'=>0,'operator'=>'=','operand1'=>'','operand2'=>'');
		}
		if (!isset($filters['paid'])) $filters['paid'] = '';
		if (!isset($filters['writeoff'])) $filters['writeoff'] = '';
		if (!isset($filters['openClosed'])) $filters['openClosed'] = 2;
		$this->view->filters = $filters;
		$this->render();
	}

	public function setFiltersAction() {
		$params = $this->_getParam('filters');
		if (is_array($params)) {
			$filters = $this->_session->filters;
			foreach ($params as $key=>$value) {
				$filters[$key] = $value;
			}
			$this->_session->filters = $filters;
		}
		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function getContextMenuAction() {
		header('Content-Type: application/xml;');
		$this->render('get-context-menu');
	}

	public function setBatchVisitsAction() {
		$type = $this->_getParam('type');
		$ids = $this->_getParam('ids');
		$data = false;
		if (strlen($ids) > 0) {
			foreach (explode(',',$ids) as $id) {
				$visit = new Visit();
				$visit->visitId = (int)$id;
				if (!$visit->populate()) continue;
				if ($type == 'open') {
					$visit->closed = 0;
				}
				else if ($type == 'closed') {
					$visit->closed = 1;
				}
				else {
					continue;
				}
				$visit->persist();
				$data = true;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listAction() {
		$sessions = $this->_session->filters;
		$filters = array();
		$filters['DOSDateRange'] = array('start'=>$sessions['DOSStart'],'end'=>$sessions['DOSEnd']);
		$facility = $sessions['facility'];
		if (strlen($facility) > 0) { // practiceId_buildingId_roomId
			$x = explode('_',$facility);
			$practiceId = $x[0];
			$buildingId = $x[1];
			$roomId = $x[2];
			$filters['facility'] = array('practice'=>$practiceId,'building'=>$buildingId,'room'=>$roomId);
		}
		$insurer = $sessions['insurer'];
		if (strlen($insurer) > 0) $filters['insurer'] = (int)$insurer;
		$filters['total'] = $sessions['total'];
		$filters['paid'] = $sessions['paid'];
		$filters['writeoff'] = $sessions['writeoff'];
		$filters['balance'] = $sessions['balance'];
		$filters['openClosed'] = isset($sessions['openClosed'])?(int)$sessions['openClosed']:2;

		$rows = array();
		$claimIterator = array();
		foreach (ClaimLine::claimsList($filters) as $claim) {
			$visit = $claim['visit'];
			if (!$visit->dateOfTreatment || $visit->dateOfTreatment == '0000-00-00 00:00:00') {
				$visit->dateOfTreatment = '0000-00-00';
			}
			else {
				$visit->dateOfTreatment = date('Y-m-d',strtotime($visit->dateOfTreatment));
			}
			$person = new Person();
			$person->personId = (int)$visit->patientId;
			$person->populate();
			$total = $claim['claims']['totalOrig'] + $claim['miscCharges']['total'];
			$billed = ($claim['claims']['totalOrig'] - $claim['claims']['totalDiscounted']) + $claim['miscCharges']['total'];
			$paid = $claim['payments']['total'];
			$writeoff = 0;
			$balance = $billed - $paid;

			$names = array('total','paid','writeoff','balance');
			foreach ($names as $name) {
				if (!$filters[$name]['active']) continue;
				$operator = $filters[$name]['operator'];
				$operand1 = $filters[$name]['operand1'];
				$operand2 = $filters[$name]['operand2'];
				if ($operator == '=' && $operand1 > 0 && !($$name == $operand1)) continue 2;
				else if ($operator == '>' && $operand1 > 0 && !($$name > $operand1)) continue 2;
				else if ($operator == '>=' && $operand1 > 0 && !($$name >= $operand1)) continue 2;
				else if ($operator == '<' && $operand1 > 0 && !($$name < $operand1)) continue 2;
				else if ($operator == '<=' && $operand1 > 0 && !($$name <= $operand1)) continue 2;
				else if ($operator == 'between' && $operand1 > 0 && $operand2 > 0 && !($$name >= $operand1 && $$name <= $operand2)) {
					continue 2;
				}
			}
			$row = array();
			$row['id'] = $visit->visitId;
			$row['data'] = array();
			$row['data'][] = $visit->dateOfTreatment;
			$row['data'][] = $person->displayName;
			$row['data'][] = number_format($total,2,'.',',');
			$row['data'][] = number_format($billed,2,'.',',');
			$row['data'][] = number_format($paid,2,'.',',');
			$row['data'][] = number_format($balance,2,'.',',');
			$row['data'][] = $visit->insuranceProgram;
			$row['data'][] = $visit->displayStatus;
			$rows[] = $row;
		}

		$data = array('rows'=>$rows);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listPatientClaimsAction() {
		$visitId = (int)$this->_getParam('visitId');
		$rows = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
			$personId = (int)$visit->patientId;

			$claimLineIterator = new ClaimLineIterator(null,false);
			$claimLineIterator->setFilters(array('visitId'=>$visitId));
			foreach ($claimLineIterator as $claimLine) {
				$rows[] = $this->_generateClaimRow($claimLine);
			}
		}
		$claimLine = new ClaimLine();
		$rows[] = $this->_generateClaimRow($claimLine);
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function diagnosesModifiersXmlAction() {
		header('Content-Type: application/xml;');
		$this->render('diagnosis-modifiers-xml');
	}

	protected function _generateClaimRow(ClaimLine $claimLine) {
		$row = array();
		$row['id'] = $claimLine->claimLineId;
		$row['data'] = array();
		$row['data'][] = $this->view->baseUrl.'/claims.raw/diagnoses-modifiers.xml';//$claimLine->procedureCode;
		$row['data'][] = $claimLine->procedureCode;
		$subrows = array();
		$subrow = array();
		$subrow['id'] = 1;
		$subrow['data'][] = 'Diagnoses';
		$subrow['rows'] = array(array('id'=>'3','data'=>array('401.1 Benign Hypertension')));
		$subrows[] = $subrow;
		$subrow = array();
		$subrow['id'] = 2;
		$subrow['data'][] = 'Modifiers';
		$subrow['rows'] = array(array('id'=>'4','data'=>array('A1 Ambulance')));
		$subrows[] = $subrow;
		$row['rows'] = $subrows;//= array(array('id'=>'123','data'=>array($claimLine->procedureCode,'diagnosis','modifiers')));
		//$row['data'][] = $claimLine->diagnosisCode1;
		//$row['data'][] = $claimLine->modifier1;
		$row['data'][] = '';//$claimLine->excludeFromDiscount;
		$row['data'][] = $claimLine->baseFee;
		$row['data'][] = $claimLine->adjustedFee;
		$row['userdata']['xDisc'] = $claimLine->excludeFromDiscount;
		$row['userdata']['xClaim'] = $claimLine->excludeFromClaim;
		return $row;
	}

	public function processEditClaimAction() {
		$params = $this->_getParam('claimLine');
		$ret = false;
		if (is_array($params) && isset($params['claimLineId']) && $params['claimLineId'] > 0) {
			$claimLineId = (int)$params['claimLineId'];
			$claimLine = new ClaimLine();
			$claimLine->claimLineId = $claimLineId;
			$claimLine->populate();
			$claimLine->populateWithArray($params);
			$claimLine->persist();
			$ret = $this->_generateClaimRow($claimLine);
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function ajaxGetMenuAction() {
		$this->view->type = $this->_getParam('type');
		header('Content-Type: application/xml;');
		$this->render('ajax-get-menu');
	}

	public function listProceduresAction() {
		$visitId = (int)$this->_getParam('visitId');
		$claimId = (int)$this->_getParam('claimId');
		$rows = array();
		$visit = new Visit();
		$visit->visitId = $visitId;
		$totalOrig = 0;
		$totalDiscounted = 0;
		if ($visit->populate()) {
			$patientId = (int)$visit->patientId;
			$insuranceProgramId = $visit->activePayerId;
			$dateOfVisit = date('Y-m-d',strtotime($visit->dateOfTreatment));
			$statistics = PatientStatisticsDefinition::getPatientStatistics($patientId);
			$familySize = isset($statistics['family_size'])?$statistics['family_size']:0;
			$monthlyIncome = isset($statistics['monthly_income'])?$statistics['monthly_income']:0;
			$flatDiscount = 0;
			$percentageDiscount = 0;
			$iterator = new ClaimLineIterator(null,false);
			$iterator->setFilters(array('visitId'=>$visitId));
			foreach ($iterator as $claim) {
				$code = $claim->procedureCode;
				$feeOrig = '-.--';
				$feeDiscounted = '-.--';
				$discountedRate = '';
				$retFee = FeeSchedule::checkFee($insuranceProgramId,$dateOfVisit,$code);
				if ($retFee !== false && (float)$retFee['fee'] != 0) {
					$feeOrig = (float)$retFee['fee'];
					$tmpFee = 0;
					for ($i = 1; $i <= 4; $i++) {
						$modifier = 'modifier'.$i;
						if (!strlen($claim->$modifier) > 0) continue;
						switch ($claim->$modifier) {
							case $retFee['modifier1']:
								$tmpFee += (float)$retFee['modifier1fee'];
								break;
							case $retFee['modifier2']:
								$tmpFee += (float)$retFee['modifier2fee'];
								break;
							case $retFee['modifier3']:
								$tmpFee += (float)$retFee['modifier3fee'];
								break;
							case $retFee['modifier4']:
								$tmpFee += (float)$retFee['modifier4fee'];
								break;
						}
					}
					if ($tmpFee > 0) $feeOrig = $tmpFee;
					$totalOrig += $feeOrig;
					$retDiscount = DiscountTable::checkDiscount($insuranceProgramId,$dateOfVisit,$familySize,$monthlyIncome);
					if ($retDiscount !== false) {
						$discount = (float)$retDiscount['discount'];
						$discountType = $retDiscount['discountType'];
						switch ($retDiscount['discountType']) {
							case DiscountTable::DISCOUNT_TYPE_FLAT_VISIT:
								$flatDiscount += $discount;
								break;
							case DiscountTable::DISCOUNT_TYPE_FLAT_CODE:
								$feeDiscounted = $feeOrig - abs($discount);
								$discountedRate = $discount;
								break;
							case DiscountTable::DISCOUNT_TYPE_PERC_VISIT:
								$percentageDiscount += $discount;
								break;
							case DiscountTable::DISCOUNT_TYPE_PERC_CODE:
								$percent = $discount / 100;
								$feeDiscounted = $feeOrig - ($feeOrig * $percent);
								$discountedRate = $discount.'%';
								break;
						}
					}
					$totalDiscounted += (float)$feeDiscounted;
				}
				$row = array();
				$row['id'] = $claim->claimLineId;
				$row['data'] = array();
				$row['data'][] = 'dummy.xml';
				$row['data'][] = $claim->procedureCode.': '.$claim->procedure;
				if ($feeOrig != '-.--') {
					$feeOrig = number_format($feeOrig,2,'.',',');
				}
				$row['data'][] = $feeOrig;
				if ($feeDiscounted != '-.--') {
					$feeDiscounted = number_format($feeDiscounted,2,'.',',');
				}
				$row['data'][] = $feeDiscounted;
				//$row['data'][] = $discountedRate;
				$rows[] = $row;
				if ($claimId > 0 && $claimId == $claim->claimLineId) break;
			}
			/*$discountedRate = '';
			$tmpTotal = $totalOrig;
			if ($flatDiscount != 0) {
				$totalDiscounted = $tmpTotal - abs($flatDiscount);
				$tmpTotal = $totalDiscounted;
				$discountedRate = $flatDiscount;
			}
			if ($percentageDiscount != 0) {
				$percent = $percentageDiscount / 100;
				$totalDiscounted = $tmpTotal - ($tmpTotal * $percent);
				$discountedRate = $percentageDiscount.'%';
			}
			$row = array();
			$row['id'] = 'totals';
			$row['data'] = array();
			$row['data'][] = '<strong>Total</strong>';
			$row['data'][] = number_format($totalOrig,2,'.',',');
			$row['data'][] = number_format($totalDiscounted,2,'.',',');
			$row['data'][] = $discountedRate;
			$rows[] = $row;*/
		}
		$data = array('rows'=>$rows);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);

	}

	public function listClaimsAction() {
		$visitId = (int)$this->_getParam('visitId');
		$rows = array();
		if ($visitId > 0) {
			$baseUrl = Zend_Registry::get('baseUrl');
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();
			$list = array(
				'procedures'=>'Procedures',
				'misc-charges'=>'Misc Charges',
				'misc-payments'=>'Misc Payments',
				'totals'=>'Totals',
			);
			foreach ($list as $key=>$value) {
				$row = array();
				$row['id'] = $key;
				$row['data'] = array();
				$url = $baseUrl.'claims.raw/list-'.$key.'?visitId='.$visitId;
				if ($key == 'totals') {
					$url = '';
				}
				$row['data'][] = $url;
				$row['data'][] = '<strong>'.$value.'</strong>';
				$rows[] = $row;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listMiscChargesAction() {
		$visitId = (int)$this->_getParam('visitId');
		$rows = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();

			$ctr = 1;
			$miscCharge = new MiscCharge();
			$results = $miscCharge->getUnpaidCharges();
			foreach ($results as $result) {
				$row = array();
				$row['id'] = $ctr++;
				$row['data'] = array();
				$row['data'][] = '';
				$row['data'][] = $result['note'];
				$row['data'][] = number_format($result['amount'],2,'.',',');
				$rows[] = $row;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listMiscPaymentsAction() {
		$visitId = (int)$this->_getParam('visitId');
		$rows = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();

			$ctr = 1;
			$payment = new Payment();
			$paymentIterator = $payment->getIteratorByVisitId($visit->visitId);
			foreach ($paymentIterator as $pay) {
				$row = array();
				$row['id'] = $ctr++;
				$row['data'] = array();
				$row['data'][] = '';
				$row['data'][] = $pay->title;
				$row['data'][] = number_format($pay->amount,2,'.',',');
				$rows[] = $row;
			}
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listDiagnosesAction() {
		$visitId = (int)$this->_getParam('visitId');
		$claimId = (int)$this->_getParam('claimId');
		$rows = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();

			$claim = new ClaimLine();
			$claim->claimLineId = $claimId;
			$claim->populate();
			$diagnoses = array();
			for ($i = 1; $i <= 8; $i++) {
				$field = 'diagnosisCode'.$i;
				if (strlen($claim->$field) > 0) {
					$diagnoses[$claim->$field] = $claim->$field;
				}
			}

			$enabled = array();
			$disabled = array();
			$patientDiagnosisIterator = new PatientDiagnosisIterator();
			$patientDiagnosisIterator->setFilters(array('patientId'=>(int)$visit->patientId));
			foreach ($patientDiagnosisIterator as $row) {
				$tmp = array();
				$tmp['id'] = $row->code;
				$tmp['data'] = array();
				$tmp['data'][] = isset($diagnoses[$row->code])?'1':'';
				$diagnosis = $row->code.': '.$row->diagnosis;
				if ($row->isPrimary) $diagnosis = '<strong>'.$diagnosis.'</strong>';
				$tmp['data'][] = $diagnosis;
				if ($tmp['data'][0] == '1') {
					$enabled[$diagnoses[$row->code]] = $tmp;
				}
				else {
					$disabled[] = $tmp;
				}
			}
			$tmp = $enabled;
			$enabled = array();
			foreach ($diagnoses as $diagnosis) {
				$enabled[] = $tmp[$diagnosis];
			}
			$rows = array_merge($enabled,$disabled);
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listModifiersAction() {
		$visitId = (int)$this->_getParam('visitId');
		$claimId = (int)$this->_getParam('claimId');
		$rows = array();
		if ($visitId > 0) {
			$visit = new Visit();
			$visit->visitId = $visitId;
			$visit->populate();

			$claim = new ClaimLine();
			$claim->claimLineId = $claimId;
			$claim->populate();
			$modifiers = array();
			for ($i = 1; $i <= 4; $i++) {
				$field = 'modifier'.$i;
				if (strlen($claim->$field) > 0) {
					$modifiers[$claim->$field] = $claim->$field;
				}
			}

			$enabled = array();
			$disabled = array();
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName('Procedure Modifiers');
			$closure = new EnumerationClosure();
			$descendants = $closure->getAllDescendants($enumeration->enumerationId,1,true);
			foreach ($descendants as $row) {
				$tmp = array();
				$tmp['id'] = $row->key;
				$tmp['data'] = array();
				$tmp['data'][] = isset($modifiers[$row->key])?'1':'';
				$tmp['data'][] = $row->key.': '.$row->name;
				if ($tmp['data'][0] == '1') {
					$enabled[$modifiers[$row->key]] = $tmp;
				}
				else {
					$disabled[] = $tmp;
				}
			}
			$tmp = $enabled;
			$enabled = array();
			foreach ($modifiers as $modifier) {
				$enabled[] = $tmp[$modifier];
			}
			$rows = array_merge($enabled,$disabled);
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processSetDiagnosesAction() {
		$claimId = (int)$this->_getParam('claimId');
		$state = (int)$this->_getParam('state');
		$code = $this->_getParam('code');
		$ret = $this->_processSetDiagnosisModifier('Diagnosis',$claimId,$state,$code);
		if (!$ret) {
			if ($state) {
				$ret = __('Maximum diagnoses reached');
			}
			else {
				$ret = __('Selected diagnosis does not exist');
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function processSetModifiersAction() {
		$claimId = (int)$this->_getParam('claimId');
		$state = (int)$this->_getParam('state');
		$code = $this->_getParam('code');
		$ret = $this->_processSetDiagnosisModifier('Modifier',$claimId,$state,$code);
		if (!$ret) {
			if ($state) {
				$ret = __('Maximum modifiers reached');
			}
			else {
				$ret = __('Selected modifier does not exist');
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	protected function _processSetDiagnosisModifier($type,$claimId,$state,$code) {
		$ret = false;
		$claim = new ClaimLine();
		$claim->claimLineId = $claimId;
		if (strlen($code) > 0 && $claim->populate()) {
			$method = 'setUnset'.$type;
			if ($claim->$method($code,$state)) {
				$claim->persist();
				$ret = true;
			}
		}
		return $ret;
	}

	public function processReorderDiagnosesAction() {
		$claimId = (int)$this->_getParam('claimId');
		$from = $this->_getParam('from');
		$to = $this->_getParam('to');
		$ret = $this->_processReorderDiagnosisModifier('Diagnosis',$claimId,$from,$to);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function processReorderModifiersAction() {
		$claimId = (int)$this->_getParam('claimId');
		$from = (int)$this->_getParam('from');
		$to = $this->_getParam('to');
		$ret = $this->_processReorderDiagnosisModifier('Modifier',$claimId,$from,$to);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	protected function _processReorderDiagnosisModifier($type,$claimId,$from,$to) {
		$ret = __('Failed to reorder');
		$claim = new ClaimLine();
		$claim->claimLineId = $claimId;
		if (strlen($from) > 0 && strlen($to) > 0 && $claim->populate()) {
			$method = 'reorder'.$type;
			$claim->$method($from,$to);
			$claim->persist();
			$ret = true;
		}
		return $ret;
	}

}
