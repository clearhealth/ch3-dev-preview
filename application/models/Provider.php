<?php
/*****************************************************************************
*       Provider.php
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


class Provider extends WebVista_Model_ORM {
	protected $person_id;
	protected $person;
	protected $state_license_number;
	protected $clia_number;
	protected $dea_number;
	protected $bill_as;
	protected $report_as;
	protected $routing_station;
	protected $color;
	protected $sureScriptsSPI;
	protected $_table = "provider";
	protected $_primaryKeys = array("person_id");
	protected $_legacyORMNaming = true;

	function __construct() {
		$this->person = new Person();
                parent::__construct();
        }


	static public function getIter() {
		$provider = new Provider();
                $db = Zend_Registry::get('dbAdapter');
                $provSelect = $db->select()
                        ->from('provider')
			->joinUsing('person','person_id')
			->order('person.last_name')
			->order('person.first_name');
                $iter = $provider->getIterator($provSelect);
		//trigger_error($provSelect, E_USER_NOTICE);
                return $iter;
        }

	static public function getArray($key = "person_id", $value = "optionName") {
                $iter = Provider::getIter();
                return $iter->toArray($key, $value);

        }	

	function getIterator($provSelect = null) {
		return new ProviderIterator($provSelect);
	}

	function getOptionName() {
		return $this->person->getDisplayName();
	}

	function getPersonId() {
		return $this->person_id;
	}


	function setPersonId($key) {
		if ($this->person->person_id > 0 && (int)$key != $this->person_id) {
			$person = new Person();
			unset($this->person);
			$this->person = $person;
		}
		$this->person_id = (int)$key;
		$this->person->person_id = (int)$key;
	}

	function __get($key) {
		if (in_array($key,$this->ORMFields())) {
			return $this->$key;
		}
		elseif (in_array($key,$this->person->ORMFields())) {
			return $this->person->__get($key);
		}
		elseif (!is_null(parent::__get($key))) {
			return parent::__get($key);
		}
		elseif (!is_null($this->person->__get($key))) {
			return $this->person->__get($key);
		}
		return parent::__get($key);
	}

}
