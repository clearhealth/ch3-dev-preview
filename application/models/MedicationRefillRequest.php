<?php
/*****************************************************************************
*       MedicationRefillRequest.php
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


class MedicationRefillRequest extends WebVista_Model_ORM {

	protected $messageId;
	protected $medicationId;
	protected $medication;
	protected $action;
	protected $status;
	protected $dateStart;
	protected $details;
	protected $dateTime;
	protected $refillResponse;

	protected $_table = 'medicationRefillRequests';
	protected $_primaryKeys = array('messageId');

	function __construct() {
		parent::__construct();
		$this->medication = new Medication();
		$this->medication->_cascadePersist = false;
		$this->refillResponse = new MedicationRefillResponse();
		$this->refillResponse->_cascadePersist = false;
	}

	public function setMessageId($id) {
		$this->messageId = $id;
		$this->refillResponse->messageId = $this->messageId;
	}

	public function setMedicationId($id) {
		$this->medicationId = (int)$id;
		$this->medication->medicationId = $this->medicationId;
	}

	public function getIteratorByPersonId($personId) {
		$db = Zend_Registry::get("dbAdapter");
		$sqlSelect = $db->select()
				->from(array('r'=>$this->_table))
				->joinLeft(array('m'=>'medications'),'m.medicationId = r.medicationId')
				->joinLeft(array('msg'=>'messaging'),'msg.messagingId = r.messageId',array('personId','rawMessage'))
				->where('msg.personId = ?',(int)$personId)
				//->where('m.personId = ?',(int)$personId)
				->order('r.dateTime DESC')
				->group('r.messageId');
		//trigger_error($sqlSelect->__toString());
		return $this->getIterator($sqlSelect);
	}

}
