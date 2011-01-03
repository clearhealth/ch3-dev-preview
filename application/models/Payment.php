<?php
/*****************************************************************************
*       Payment.php
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


class Payment extends WebVista_Model_ORM {

	protected $payment_id;
	protected $foreign_id;
	protected $encounter_id;
	protected $payment_type;
	protected $ref_num;
	protected $amount;
	protected $writeoff;
	protected $user_id;
	protected $timestamp;
	protected $payer_id;
	protected $payment_date;
	protected $title;
	protected $personId;
	protected $appointmentId;

	protected $_table = 'payment';
	protected $_primaryKeys = array('payment_id');
	protected $_legacyORMNaming = true;

	public function getIteratorByPayerId($payerId = null) {
		if ($payerId === null) {
			$payerId = $this->payerId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('payer_id = ?',(int)$payerId);
		return $this->getIterator($sqlSelect);
	}

	public function getIteratorByVisitId($visitId = null) {
		if ($visitId === null) {
			$visitId = $this->encounterId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where("payment_date BETWEEN '".date('Y-m-d',strtotime('-30 days'))."' AND '".date('Y-m-d')."'")
				->where('encounter_id = ?',(int)$visitId)
				->order('timestamp DESC');
		return $this->getIterator($sqlSelect);
	}

	public function getMostRecentPayments() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where("payment_date BETWEEN '".date('Y-m-d',strtotime('-30 days'))."' AND '".date('Y-m-d')."'");
		return $this->getIterator($sqlSelect);
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
				->order('timestamp DESC');
		return $this->getIterator($sqlSelect);
	}

}
