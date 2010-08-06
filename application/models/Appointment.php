<?php
/*****************************************************************************
*       Appointment.php
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


class Appointment extends WebVista_Model_ORM {
    protected $appointmentId;
    protected $arrived;
    protected $title;
    protected $reason;
    protected $walkin;
    protected $createdDate;
    protected $lastChangeId;
    protected $lastChange;
    protected $lastChangeDate;
    protected $creatorId;
    protected $creator;
    protected $practiceId;
    protected $providerId;
    protected $patientId;
    protected $provider;
    protected $patient;
    protected $roomId;
    protected $appointmentCode;
    protected $start;
    protected $end;
    protected $_table = "appointments";
    protected $_primaryKeys = array("appointmentId");

	function __construct() {
		parent::__construct();
		$this->patient = new Patient();
		$this->patient->_cascadePersist = false;
		$this->provider = new Provider();
		$this->provider->_cascadePersist = false;
		$this->creator = new User();
		$this->creator->_cascadePersist = false;
		$this->lastChange = new User();
		$this->lastChange->_cascadePersist = false;
	}

	public function populate() {
		$ret = parent::populate();
		$this->patient = new Patient();
		$this->patient->setPersonId($this->patientId);
		$this->patient->populate();
		$this->provider = new Provider();
		$this->provider->setPersonId($this->providerId);
		$this->provider->populate();
		$this->creator->userId = $this->creatorId;
		$this->creator->populate();
		$this->lastChange->userId = $this->lastChangeId;
		$this->lastChange->populate();
		return $ret;
	}

    public static function getObject($mxdFilters = array()) {
        $objApp = new Appointment();
        $db = Zend_Registry::get('dbAdapter');
        $objSelect = $db->select()
                        ->from('appointments');

        if (is_string($mxdFilters)) {
            $objSelect->where($mxdFilters);
        }
        else if (is_array($mxdFilters)) {
            foreach ($mxdFilters as $fieldName=>$mxdValue) {
                // set the default operator to ==
                $fieldOperator = '=';
                $fieldValue = '';
                if (is_array($mxdValue)) {
                    $ctr = count($mxdValue);
                    // if empty array, just continue to the next item
                    if ($ctr < 1) {
                        continue;
                    }
                    else {
                        switch ($ctr) {
                            case 1:
                                $fieldValue = array_pop($mxdValue);
                                break;
                            case 2:
                                if (isset($mxdValue['operator'])) {
                                    $fieldOperator = $mxdValue['operator'];
                                    unset($mxdValue['operator']);
                                    $fieldValue = array_pop($mxdValue);
                                }
                                else {
                                    // use the first element of the array as its operator
                                    $fieldOperator = array_shift($mxdValue);
                                    // use the 2nd element of the array as its value
                                    $fieldValue = array_shift($mxdValue);
                                }
                                break;
                            default:
                                continue;
                                break;
                        }
                    }
                }

                if ($fieldValue == '') {
                    continue;
                }
                $objSelect->where("$fieldName $fieldOperator ?", $fieldValue);
            }
        }
        $objIterator = $objApp->getIterator($objSelect);
        return $objIterator;
    }

    function getIterator($objSelect = null) {
        return new AppointmentIterator($objSelect);
    }

	public function checkRules() {
		$ret = false;
		// check double booking
		if ($this->isDoubleBook()) {
			$ret = __('Double booking');
		}
		// check outside of schedule time
		else if ($this->isOutsideScheduleTime()) {
			$ret = __('Outside of schedule time');
		}
		return $ret;
	}

	public function isDoubleBook() {
		$ret = false;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('providerId = ?',$this->providerId)
				->where('`start` >= ?',date('Y-m-d H:i:s',strtotime($this->start)))
				->where('`end` <= ?',date('Y-m-d H:i:s',strtotime($this->end)))
				->limit(1);
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = true;
		}
		return $ret;
	}

	public function isOutsideScheduleTime() {
		$ret = true;
		$scheduleEvent = new ScheduleEvent();
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($scheduleEvent->_table)
				->where('providerId = ?',$this->providerId)
				->where("'".date('Y-m-d H:i:s',strtotime($this->start))."' BETWEEN `start` AND `end`")
				->where("'".date('Y-m-d H:i:s',strtotime($this->end))."' BETWEEN `start` AND `end`")
				->limit(1);
		//trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = false;
		}
		return $ret;
	}

}
