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
    protected $start;
    protected $end;
    protected $_table = "scheduleEvents";
    protected $_primaryKeys = array("scheduleEventId");

    function __construct() {
        parent::__construct();
	$this->provider = new Provider();
	$this->room = new Room();
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
		elseif (!is_null(parent::__get($key))) {
			return parent::__get($key);
		}
		elseif (!is_null($this->provider->__get($key))) {
			return $this->provider->__get($key);
		}
		elseif (!is_null($this->room->__get($key))) {
			return $this->room->__get($key);
		}
		return parent::__get($key);
	}
}
