<?php
/*****************************************************************************
*       PhoneNumber.php
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


class PhoneNumber extends WebVista_Model_ORM {
	protected $number_id;
	protected $person_id;
	protected $name;
	protected $type;
	protected $notes;
	protected $number;
	protected $active;
	protected $_table = "number";
	protected $_primaryKeys = array('number_id');
	protected $_legacyORMNaming = true;
	
	public function getIteratorByPatientId($patientId = null) {
		if ($patientId === null) {
			$patientId = $this->personId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('person_id = ?',(int)$patientId);
		return $this->getIterator($sqlSelect);
	}

	public function getPhoneNumberId() {
		return $this->number_id;
	}

	public function setPhoneNumberId($id) {
		$this->number_id = $id;
	}

	public function __isset($key) {
		$ret = false;
		if (method_exists($this,"get" . ucfirst($key))) {
			$ret = true;
		}
		elseif ($this->_legacyORMNaming == true && strpos($key,'_') === false) {
			$newKey = strtolower(preg_replace('/([A-Z]{1})/','_\1',$key));
			if (strpos($newKey,'_') !== false && in_array($newKey,$this->ORMFields())) {
				$ret = true;
			}
		}
		if (isset($this->$key)) {
			$ret = true;
		}
		return $ret;
	}

        public function populateWithPersonId() {
                $db = Zend_Registry::get('dbAdapter');
                //address_type 3 is work
                $sql = "SELECT * from " . $this->_table 
                        ." INNER JOIN person_number per2num on per2num.number_id = number.number_id WHERE 1 and number.number_type = 3 and per2num.person_id = " . (int) $db->quote($this->person_id);
                $this->populateWithSql($sql);
        }
}
