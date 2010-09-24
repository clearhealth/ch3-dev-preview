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

}
