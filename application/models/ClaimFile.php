<?php
/*****************************************************************************
*       ClaimFile.php
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


class ClaimFile extends WebVista_Model_ORM {

	protected $claimFileId;
	protected $destination;
	protected $claimIds;
	protected $status;
	protected $dateTime;
	protected $userId;
	protected $user;

	protected $_table = 'claimFiles';
	protected $_primaryKeys = array('claimFileId');
	protected $_cascadePersist = false;

	public function __construct() {
		$this->user = new User();
	}

	public function persist() {
		$claimFileId = (int)$this->claimFileId;
		if (!$claimFileId > 0) $this->claimFileId = $this->nextSequenceId('claimSequences');
		return parent::persist();
	}

	public static function countVisitClaims($visitId) {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new self();
		$sqlSelect = $db->select()
				->from($orm->_table,'COUNT(visitId) AS ctr')
				->where('visitId = ?',(int)$visitId);
		$ret = 0;
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = $row['ctr'];
		}
		return $ret;
	}

	public static function listClaims(Array $filters) {
		$db = Zend_Registry::get('dbAdapter');

		$sqlSelect = $db->select()
				->from('claimFiles')
				->join('encounter','encounter.encounter_id = claimFiles.visitId')
				->order('claimFiles.dateTime DESC')
				->order('encounter.appointmentId');
		foreach ($filters as $key=>$value) {
			switch ($key) {
				case 'DOSDateRange':
					$sqlSelect->where("encounter.date_of_treatment BETWEEN '{$value['start']} 00:00:00' AND '{$value['end']} 23:59:59'");
					break;
				case 'facilities':
					// practice, building, room
					if (!is_array($value)) $value = array($value);
					$facilities = array();
					foreach ($value as $val) {
						$facilities[] = 'practice_id = '.(int)$val['practice'].' AND building_id = '.(int)$val['building'].' AND room_id = '.(int)$val['room'];
					}
					$sqlSelect->where(implode(' OR ',$facilities));
					break;
				case 'payers':
					$payers = array();
					foreach ($value as $payerId) {
						$payers[] = (int)$payerId;
					}
					$sqlSelect->where('activePayerId IN ('.implode(',',$payers).')');
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
			}
		}

		$columnMeta = array();
		$rows = array();
		$stmt = $db->query($sqlSelect);
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			if (!isset($rows[0])) {
				for ($i=0,$ctr=count($row);$i<$ctr;$i++) $columnMeta[$i] = $stmt->getColumnMeta($i);
			}

			$data = array();
			$col = 0;
			foreach ($columnMeta as $i=>$meta) {
				$data[$meta['table']][$meta['name']] = $row[$i];
			}
			$claimFile = new ClaimFile();
			$claimFile->populateWithArray($data[$claimFile->_table]);
			$visit = new Visit();
			$visit->populateWithArray($data[$visit->_table]);
			$rows[] = array(
				'claimFile'=>$claimFile,
				'visit'=>$visit
			);
		}
		return $rows;
	}

	public function getEnteredBy() {
		$ret = '';
		$userId = (int)$this->userId;
		if ($userId > 0) {
			$user = new User();
			$user->userId = $userId;
			$user->populate();
			$ret = $user->username;
		}
		return $ret;
	}

	public function getDisplayDestination() {
		$destinations = Claim::listOptions();
		$destination = $this->destination;
		return isset($destinations[$destination])?$destinations[$destination]:$destination;
	}

	public function listClaimLineIds($visitId) {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new self();
		$table = $orm->_table;
		$sqlSelect = $db->select()
				->from($table,array('claimLines.claimLineId AS claimLineId'))
				->join('claimLines','claimLines.claimId IN ('.implode(',',$this->claimIds).')')
				->where('claimLines.visitId = ?',(int)$visit);
		$claimLineIds = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$claimLineIds[] = (int)$row['claimLineId'];
			}
		}
		return $claimLineIds;
	}

	public static function claimLine($visitId,Array $claimIds) {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new ClaimLine();
		$orm->visitId = (int)$visitId;
		$table = $orm->_table;
		$sqlSelect = $db->select()
				->from($table)
				->where('claimId IN ('.implode(',',$claimIds).')')
				->where('visitId = ?',(int)$orm->visitId)
				->group('claimId')
				->limit(1);
		if ($row = $db->fetchRow($sqlSelect)) {
			$orm->populateWithArray($row);
		}
		return $orm;
	}

	public static function inquire($visitId) {
		return 'Approved';
	}

}
