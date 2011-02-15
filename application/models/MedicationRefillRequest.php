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
	protected $_cascadePersist = false;

	function __construct() {
		parent::__construct();
		$this->medication = new Medication();
		$this->medication->_cascadePersist = false;
		$this->refillResponse = new MedicationRefillResponse();
		$this->refillResponse->_cascadePersist = false;
	}

	public function populate() {
		$sql = "SELECT * from " . $this->_table . " WHERE 1 ";
		$doPopulate = false;
		foreach($this->_primaryKeys as $key) {
			$doPopulate = true;
			$sql .= " and $key = '" . preg_replace('/[^0-9a-z_A-Z-\.]/','',$this->$key) . "'";
		}
		if ($doPopulate == false) return false;
		$retval = false;
		$retval = $this->populateWithSql($sql);
		$this->postPopulate();
		return $retval;
	}

	public function getMedicationRefillRequestId() {
		return $this->messageId;
	}

	public function setMedicationRefillRequestId($id) {
		$this->setMessageId($id);
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
				->order('r.dateTime DESC')
				->group('r.messageId');
		//trigger_error($sqlSelect->__toString());
		return $this->getIterator($sqlSelect);
	}

	public static function refillRequestDatasourceHandler(Audit $auditOrm,$eachTeam=true) {
		$ret = array();
		if ($auditOrm->objectClass != 'MedicationRefillRequest') {
			WebVista::debug('Audit:objectClass is not MedicationRefillRequest');
			return $ret;
		}

		$orm = new self();
		$orm->messageId = $auditOrm->objectId;
		if (!$orm->populate()) {
			WebVista::debug('Failed to populate');
			return $ret;
		}
		$personId = (int)$orm->medication->personId;
		$providerId = (int)$orm->medication->prescriberPersonId;
		if ($personId <= 0 || $providerId <= 0) return $ret;
		$teamId = $orm->medication->patient->teamId;
		$alert = new GeneralAlert();
		$filters = array();
		$filters['objectClass'] = $auditOrm->objectClass;
		//$filters['objectId'] = $personId;
		if ($eachTeam) {
			$filters['teamId'] = $teamId;
		}
		else {
			$filters['userId'] = (int)$providerId;
		}
		$alert->populateOpenedAlertByFilters($filters);
		$messages = array();
		if (strlen($alert->message) > 0) { // existing general alert
			$messages[] = $alert->message;
		}
		else { // new general alert
			$alert->urgency = 'High';
			$alert->status = 'new';
			$alert->dateTime = date('Y-m-d H:i:s');
			$alert->objectClass = $auditOrm->objectClass;
			$alert->objectId = $auditOrm->objectId;
			$alert->userId = (int)$providerId;
			if ($eachTeam) $alert->teamId = $teamId;
		}
		$messages[] = 'Refill request pending. '.$orm->details;
		$alert->message = implode("\n",$messages);
		return $alert->toArray();
	}

	public static function refillRequestActionHandler(Audit $auditOrm,array $dataSourceData) {
		if (!count($dataSourceData) > 0) {
			WebVista::debug('Received an empty datasource');
			return false;
		}
		$orm = new GeneralAlert();
		$orm->populateWithArray($dataSourceData);
		$orm->persist();
		return true;

	}

	public static function getControllerName() {
		return 'MedicationsController';
	}

	public function getPersonId() {
		return $this->medication->personId;
	}

}
