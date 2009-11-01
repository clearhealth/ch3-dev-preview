<?php
/*****************************************************************************
*       ScheduleEventIterator.php
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


class ScheduleEventIterator extends WebVista_Model_ORMIterator {

	public function __construct($dbSelect = null) {
		parent::__construct("ScheduleEvent",$dbSelect);
	}

	public function current() {
		$ormObj = new $this->_ormClass();
		$row = $this->_dbStmt->fetch(null,null,$this->_offset);
		$ormObj->populateWithArray($row);
		$ormObj->provider->populateWithArray($row);
		$ormObj->room->populateWithArray($row);
		return $ormObj;
	}

	public function setFilter($filter) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from('scheduleEvents');
		$dbSelect->joinLeft("provider","scheduleEvents.providerId=provider.person_id");
		$dbSelect->joinLeft("rooms","scheduleEvents.roomId=rooms.id");
		$dbSelect->where("providerId = ?", $filter['providerId']);
		$dbSelect->where("start >= ?", $filter['start']);
		$dbSelect->where("end <= ?", $filter['end']);
		$dbSelect->order("start ASC");
		//trigger_error($dbSelect->__toString(),E_USER_NOTICE);
		$this->_dbSelect = $dbSelect;
		$this->_dbStmt = $db->query($this->_dbSelect);
	}
}
