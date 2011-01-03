<?php
/*****************************************************************************
*       ClaimLine.php
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


class ClaimLine extends WebVista_Model_ORM {

	protected $claimLineId;
	protected $visitId;
	protected $insuranceProgramId;
	protected $procedureCode;
	protected $units;
	protected $diagnosisCode1;
	protected $diagnosisCode2;
	protected $diagnosisCode3;
	protected $diagnosisCode4;
	protected $diagnosisCode5;
	protected $diagnosisCode6;
	protected $diagnosisCode7;
	protected $diagnosisCode8;
	protected $modifier1;
	protected $modifier2;
	protected $modifier3;
	protected $modifier4;
	protected $excludeFromDiscount;
	protected $excludeFromClaim;
	protected $mappedCode;
	protected $baseFee;
	protected $adjustedFee;
	protected $unitsDoesNotEffectFee;
	protected $linkedMedicationId;
	protected $ndc;

	protected $_table = 'claimLines';
	protected $_primaryKeys = array('claimLineId');

	public static function doesVisitProcedureRowExist($visitId,$procedureCode) {
		$orm = new self();
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($orm->_table,'claimLineId')
				->where('visitId = ?',(int)$visitId)
				->where('procedureCode = ?',$procedureCode.'');
		$ret = false;
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = true;
		}
		return $ret;
	}

	public function getProcedure() {
		$db = Zend_Registry::get('dbAdapter');
		$procedure = new PatientProcedure();
		$sqlSelect = $db->select()
				->from($procedure->_table,'procedure')
				->where('code = '.$db->quote($this->procedureCode))
				->limit(1);
		$ret = '';
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = $row['procedure'];
		}
		return $ret;
	}

	public function setUnsetDiagnosis($code,$state) {
		return $this->_setUnsetDiagnosisModifier('diagnosisCode',8,$code,$state);
	}

	public function setUnsetModifier($code,$state) {
		return $this->_setUnsetDiagnosisModifier('modifier',4,$code,$state);
	}

	protected function _setUnsetDiagnosisModifier($prefix,$ctr,$code,$state) {
		$ret = false;
		for ($i = 1; $i <= $ctr; $i++) {
			$field = $prefix.$i;
			if ($state) { // add
				if (!strlen($this->$field) > 0) {
					$this->$field = $code;
					$ret = true;
					break;
				}
			}
			else { // remove
				if ($this->$field == $code) {
					$this->$field = '';
					$ret = true;
					break;
				}
			}
		}
		return $ret;
	}

	public function reorderDiagnosis($from,$to) {
		return $this->_reorderDiagnosisModifier('diagnosisCode',8,$from,$to);
	}

	public function reorderModifier($from,$to) {
		return $this->_reorderDiagnosisModifier('modifier',4,$from,$to);
	}

	protected function _reorderDiagnosisModifier($prefix,$ctr,$from,$to) {
		$ret = false;
		$indexFrom = 0;
		$indexTo = 0;
		for ($i = 1; $i <= $ctr; $i++) {
			$field = $prefix.$i;
			if ($this->$field == $from) {
				$indexFrom = $i;
			}
			else if ($this->$field == $to) {
				$indexTo = $i;
			}
		}
		if ($indexFrom != 0 && $indexTo != 0) {
			if ($indexFrom > $indexTo) { // bottom to top
				$field = $prefix.($indexTo+1);
				$val = $this->$field;
				$this->$field = $from;
				for ($i = ($indexTo+2); $i <= $indexFrom; $i++) {
					$field = $prefix.$i;
					$tmp = $this->$field;
					$this->$field = $val;
					$val = $tmp;
				}
			}
			else { // top to bottom
				for ($i = $indexFrom; $i < $indexTo; $i++) {
					$field = $prefix.$i;
					$nextField = $prefix.($i+1);
					$this->$field = $this->$nextField;
				}
				$field = $prefix.$indexTo;
				$this->$field = $from;
			}
		}
		return $ret;
	}

	public static function claimsList(Array $filters) {
		$db = Zend_Registry::get('dbAdapter');
		$identity = Zend_Auth::getInstance()->getIdentity();
		$sqlSelect = $db->select()
				->from('encounter')
				->where('treating_person_id = ?',(int)$identity->personId)
				->order('date_of_treatment DESC');
		foreach ($filters as $key=>$value) {
			switch ($key) {
				case 'DOSDateRange':
					$sqlSelect->where("date_of_treatment BETWEEN '{$value['start']} 00:00:00' AND '{$value['end']} 23:59:59'");
					break;
				case 'facility':
					// practice, building, room
					$sqlSelect->where('practice_id = ?',(int)$value['practice']);
					$sqlSelect->where('building_id = ?',(int)$value['building']);
					$sqlSelect->where('room_id = ?',(int)$value['room']);
					break;
				case 'insurer':
					$sqlSelect->where('activePayerId = ?',(int)$value);
					break;
				case 'openClosed':
					if ($value == '0') $sqlSelect->where('closed = 0');
					else if ($value == '1') $sqlSelect->where('closed = 1');
					break;
			}
		}
		$rows = array();
		$visitIterator = new VisitIterator($sqlSelect);
		foreach ($visitIterator as $visit) {
			$row = array();
			$row['visit'] = $visit;

			// PROCEDURES
			$totalOrig = 0;
			$totalDiscounted = 0;
			$insuranceProgramId = $visit->activePayerId;
			$dateOfVisit = date('Y-m-d',strtotime($visit->dateOfTreatment));
			$statistics = PatientStatisticsDefinition::getPatientStatistics((int)$visit->patientId);
			$familySize = isset($statistics['family_size'])?$statistics['family_size']:0;
			$monthlyIncome = isset($statistics['monthly_income'])?$statistics['monthly_income']:0;
			$flatDiscount = 0;
			$percentageDiscount = 0;
			$row['claims'] = array();
			$row['claims']['details'] = array();
			$iterator = new ClaimLineIterator(null,false);
			$iterator->setFilters(array('visitId'=>$visit->visitId));
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
				$row['claims']['details'][$claim->claimLineId] = array();
				$row['claims']['details'][$claim->claimLineId]['claim'] = $claim;
				$row['claims']['details'][$claim->claimLineId]['feeOrig'] = $feeOrig;
				$row['claims']['details'][$claim->claimLineId]['feeDiscounted'] = $feeDiscounted;
			}
			$row['claims']['totalOrig'] = $totalOrig;
			$row['claims']['totalDiscounted'] = $totalDiscounted;

			// MISC CHARGES
			$row['miscCharges'] = array();
			$row['miscCharges']['details'] = array();
			$miscCharge = new MiscCharge();
			$results = $miscCharge->getUnpaidChargesByVisit($visit->visitId);
			$totalMiscCharges = 0;
			foreach ($results as $result) {
				$amount = (float)$result['amount'];
				$row['miscCharges']['details'][] = $amount;
				$totalMiscCharges += $amount;
			}
			$row['miscCharges']['total'] = $totalMiscCharges;

			// PAYMENTS
			$row['payments'] = array();
			$row['payments']['details'] = array();
			$payment = new Payment();
			$paymentIterator = $payment->getIteratorByVisitId($visit->visitId);
			$totalPayments = 0;
			foreach ($paymentIterator as $pay) {
				$amount = (float)$pay->amount;
				$row['payments']['details'][] = $amount;
				$totalPayments += $amount;
			}
			$row['payments']['total'] = $totalPayments;
			$rows[] = $row;
		}
		return $rows;
	}

}
