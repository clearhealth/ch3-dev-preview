<?php
/*****************************************************************************
*       ScheduleEvent.php
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


class ScheduleEvent extends WebVista_Model_ORM {
    protected $scheduleEventId;
    protected $title;
    protected $scheduleCode;
    protected $providerId;
    protected $provider;
    protected $roomId;
    protected $room;
    protected $scheduleId;
	protected $schedule;
    protected $start;
    protected $end;
	protected $buildingId;
    protected $_table = "scheduleEvents";
    protected $_primaryKeys = array("scheduleEventId");

	public function __construct() {
		parent::__construct();
		$this->provider = new Provider();
		$this->provider->_cascadePersist = false;
		$this->room = new Room();
		$this->room->_cascadePersist = false;
		$this->schedule = new Schedule();
		$this->schedule->_cascadePersist = false;
	}

	public function setProviderId($val) {
		$this->providerId = (int)$val;
		$this->provider->personId = $this->providerId;
	}

	public function setRoomId($val) {
		$this->roomId = (int)$val;
		$this->room->roomId = $this->roomId;
	}

	public function setScheduleId($val) {
		$this->scheduleId = (int)$val;
		$this->schedule->scheduleId = $this->scheduleId;
	}

    public function populateWithFilter($eventFilter) {
	$db = Zend_Registry::get('dbAdapter');
        $dbjSelect = $db->select()->from($this->_table);
        foreach ($eventFilter as $name=>$value) {
            $dbSelect->where("$name = ?", $value);
        }
    }

	public function getTitleByProviderId($providerId) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from($this->_table)
				->where('providerId=?', $providerId);
		$row = $db->fetchRow($dbSelect);
		$this->populateWithArray($row);
	}

	public function __get($key) {
		if (in_array($key,$this->ORMFields())) {
			return $this->$key;
		}
		elseif (in_array($key,$this->provider->ORMFields())) {
			return $this->provider->__get($key);
		}
		elseif (in_array($key,$this->room->ORMFields())) {
			return $this->room->__get($key);
		}
		elseif (in_array($key,$this->schedule->ORMFields())) {
			return $this->schedule->__get($key);
		}
		elseif (!is_null(parent::__get($key))) {
			return parent::__get($key);
		}
		elseif (!is_null($this->provider->__get($key))) {
			return $this->provider->__get($key);
		}
		elseif (!is_null($this->room->__get($key))) {
			return $this->room->__get($key);
		}
		elseif (!is_null($this->schedule->__get($key))) {
			return $this->schedule->__get($key);
		}
		return parent::__get($key);
	}

	public function deleteByDateRange() {
		$db = Zend_Registry::get('dbAdapter');
		$where = array();
		//$where[] = 'scheduleId = '.(int)$this->scheduleId;
		$where[] = 'providerId = '.(int)$this->providerId;
		$where[] = 'roomId = '.(int)$this->roomId;
		$where[] = 'start >= '.$db->quote($this->start);
		$where[] = 'end <= '.$db->quote($this->end);
		$where = implode(' AND ',$where);
		$db->delete($this->_table,$where);
	}

	public static function computeWeekDates($date) {
		$ret = array('start'=>$date,'end'=>$date);
		$strtotime = strtotime($date);
		$weekday = date('N',$strtotime); // 1 = Monday to 7 = Sunday
		$monday = 1;
		$sunday = 7;
		$weekend = $sunday - $weekday;
		$weekstart = $weekday - $monday;
		if ($weekend == 0) {
			$weekstart = 6;
		}
		else if ($weekstart == 0) {
			$weekend = 6;
		}
		$ret['start'] = date('Y-m-d',strtotime("-{$weekstart} days",$strtotime));
		$ret['end'] = date('Y-m-d',strtotime("+{$weekend} days",$strtotime));
		return $ret;
	}

	public static function getNumberOfEvents($providerId,$roomId,$dateStart,$dateEnd) {
		$ret = 0;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('scheduleEvents','COUNT(*) AS ctr')
				->where("roomId = ?",(int)$roomId)
				->where("providerId = ?",(int)$providerId)
				->where("start >= ?",$dateStart)
				->where("end <= ?",$dateEnd);
		trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = $row['ctr'];
		}
		return $ret;
	}

}
