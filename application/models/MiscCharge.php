<?php
/*****************************************************************************
*       MiscCharge.php
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


class MiscCharge extends WebVista_Model_ORM {

	protected $misc_charge_id;
	protected $encounter_id;
	protected $amount;
	protected $charge_date;
	protected $title;
	protected $note;
	protected $chargeType;
	protected $personId;
	protected $appointmentId;
	protected $claimLineId;
	protected $claimFileId;

	protected $_table = 'misc_charge';
	protected $_primaryKeys = array('misc_charge_id');
	protected $_legacyORMNaming = true;

	public function getIteratorByVisitId($visitId = null) {
		if ($visitId === null) {
			$visitId = $this->encounterId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('encounter_id = ?',(int)$visitId);
		return $this->getIterator($sqlSelect);
	}

	public function getUnpaidCharges() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('cc'=>'clearhealth_claim'))
				->joinLeft(array('fc'=>'fbclaim'),'fc.claim_id=cc.claim_id')
				->where('total_billed > total_paid');
		// identifier = note?
		$ret = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$ret[$row['claim_id']] = array(
					'date'=>date('Y-m-d',strtotime($row['timestamp'])),
					'type'=>'',
					'amount'=>($row['total_billed'] - $row['total_paid']),
					'note'=>'Encounter DOS: '.date('m/d/Y',strtotime($row['date_sent'])).' '.$row['identifier'],
				);
			}
		}
		return $ret;
	}

	public function getVisitId() {
		return $this->encounter_id;
	}

	public function setVisitId($id) {
		$this->encounter_id = $id;
	}

	public function getIteratorByAppointmentId($appointmentId = null) {
		if ($appointmentId === null) {
			$appointmentId = $this->appointmentId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('appointmentId = ?',(int)$appointmentId)
				->order('charge_date DESC');
		return $this->getIterator($sqlSelect);
	}

	public function getUnpaidChargesByVisit($visitId) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('cc'=>'clearhealth_claim'))
				->joinLeft(array('fc'=>'fbclaim'),'fc.claim_id=cc.claim_id')
				->where('total_billed > total_paid')
				->where('cc.encounter_id = ?',(int)$visitId);
		// identifier = note?
		$ret = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$ret[$row['claim_id']] = array(
					'date'=>date('Y-m-d',strtotime($row['timestamp'])),
					'type'=>'',
					'amount'=>($row['total_billed'] - $row['total_paid']),
					'note'=>'Encounter DOS: '.date('m/d/Y',strtotime($row['date_sent'])).' '.$row['identifier'],
				);
			}
		}
		return $ret;
	}

	public static function updateClaimIdByClaimFile(ClaimFile $claimFile) {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new self();
		$table = $orm->_table;
		$sql = 'UPDATE '.$table.' SET claimFileId = '.(int)$claimFile->claimFileId.' WHERE claimFileId = 0 AND encounter_id = '.(int)$claimFile->visitId;
		return $db->query($sql);
	}

	public static function total(Array $filters) {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new self();
		$sqlSelect = $db->select()
				->from($orm->_table,array('SUM(amount) AS total'));
		foreach ($filters as $key=>$value) {
			switch ($key) {
				case 'visitId':
					$sqlSelect->where('encounter_id = ?',(int)$value);
					break;
				case 'personId':
				case 'claimLineId':
				case 'claimFileId':
				case 'appointmentId':
					$sqlSelect->where($key.' = ?',(int)$value);
					break;
			}
		}
		$total = 0;
		if ($row = $db->fetchRow($sqlSelect)) {
			$total = (float)$row['total'];
		}
		return $total;
	}

	public static function listAccounts(Array $filters) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('misc_charge',array(
					'misc_charge.misc_charge_id AS id',
					'misc_charge.amount AS billed',
					'CONCAT(\'0\') AS paid',
					'CONCAT(\'0\') AS writeOff',
					'CONCAT(\'Misc Charge\') AS payer',
					'encounter.date_of_treatment AS dateOfTreatment',
					'misc_charge.charge_date AS dateBilled',
					'CONCAT(patient.last_name,\', \',patient.first_name,\' \',patient.middle_name) AS patientName',
					'CONCAT(\'\') AS facility',
					'CONCAT(provider.last_name,\', \',provider.first_name,\' \',provider.middle_name) AS providerName',
				))
				->join('encounter','encounter.encounter_id = misc_charge.encounter_id')
				->join(array('patient'=>'person'),'patient.person_id = encounter.patient_id')
				->join(array('provider'=>'person'),'provider.person_id = encounter.treating_person_id')
				->order('misc_charge.charge_date DESC');
		foreach ($filters as $key=>$value) {
			switch ($key) {
				case 'dateRange':
					$sqlSelect->where("encounter.date_of_treatment BETWEEN '{$value['start']} 00:00:00' AND '{$value['end']} 23:59:59'");
					break;
				case 'facilities':
					// practice, building, room
					if (!is_array($value)) $value = array($value);
					$facilities = array();
					foreach ($value as $val) {
						$facilities[] = 'encounter.practice_id = '.(int)$val['practice'].' AND encounter.building_id = '.(int)$val['building'].' AND encounter.room_id = '.(int)$val['room'];
					}
					$sqlSelect->where(implode(' OR ',$facilities));
					break;
				case 'payers':
					$payers = array();
					foreach ($value as $payerId) {
						$payers[] = (int)$payerId;
					}
					$sqlSelect->where('encounter.activePayerId IN ('.implode(',',$payers).')');
					break;
				case 'facility':
					// practice, building, room
					$sqlSelect->where('encounter.practice_id = ?',(int)$value['practice']);
					$sqlSelect->where('encounter.building_id = ?',(int)$value['building']);
					$sqlSelect->where('encounter.room_id = ?',(int)$value['room']);
					break;
				case 'insurer':
					$sqlSelect->where('encounter.activePayerId = ?',(int)$value);
					break;
				case 'visitId':
					$sqlSelect->where('encounter.encounter_id = ?',(int)$value);
					break;
				case 'provider':
					$sqlSelect->where('encounter.treating_person_id = ?',(int)$value);
					break;
				case 'providers':
					$providers = array();
					foreach ($value as $providerId) {
						$providers[] = (int)$providerId;
					}
					$sqlSelect->where('encounter.treating_person_id IN ('.implode(',',$providers).')');
					break;
			}
		}

		$rows = array();
		$stmt = $db->query($sqlSelect);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = $row;
		}
		return $rows;
	}

}
