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

}
