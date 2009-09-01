<?php
/*****************************************************************************
*       Patient.php
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


class Patient extends WebVista_Model_ORM {

	protected $person_id;
        protected $person;
        //protected $address;
	protected $homeAddress;
	protected $billingAddress;
        //protected $phone_number;
        protected $default_provider;
        protected $record_number;
        protected $confidentiality;
	protected $defaultPharmacyId;
        //protected $registration_location_id;
	protected $signedHipaaDate;
	protected $teamId;
        protected $_vitals;

        protected $_primaryKeys = array('person_id');
        protected $_table = "patient";
        protected $_legacyORMNaming = true;

	function __construct() {
		parent::__construct();
		$this->person = new Person();
		$this->homeAddress = new Address();
		$this->billingAddress = new Address();
		//$this->phoneNumber = new PhoneNumber();
	}

	function setPersonId($key) {
		if ($this->person->person_id > 0 && (int)$key != $this->person_id) {
                        $person = new Person();
                        unset($this->person);
                        $this->person = $person;
                }
		$this->person_id = (int)$key;
                $this->person->person_id = (int)$key;
		//$this->address->personId = (int)$key;
		//$this->phoneNumber->personId = (int)$key;
	}
	function __get($key) {
                if (in_array($key,$this->ORMFields())) {
                        return $this->$key;
                }
                elseif (in_array($key,$this->person->ORMFields())) {
                        return $this->person->__get($key);
                }
                elseif (!is_null($this->person->__get($key))) {
                        return $this->person->__get($key);
                }
                return parent::__get($key);
        }
	function getDefaultPharmacyId() {
		return $this->defaultPharmacyId;
	}
	function setDefaultPharmacyId($value) {
		$this->defaultPharmacyId = $value;
	}
	function getWeight() {
		//return "141 lb.";
		if (count($this->_vitals) == 0) $this->_loadVitals();
		foreach ($this->_vitals as $vital) {
			if ($vital['vital'] == "weight") return $vital['value'] . strtolower($vital['units']);
		}
	}
	
	function getHeight() {
		//return "5' 4\" (64\")";
		if (count($this->_vitals) == 0) $this->_loadVitals();
		foreach ($this->_vitals as $vital) {
			if ($vital['vital'] == "height") return $vital['value'] . strtolower($vital['units']);
		}
	}

	private function _loadAddresses() {
                $addressIterator = $this->homeAddress->getIterator();
                $addressIterator->setFilters(array('personId' => $this->personId,'class'=>'person'));
                foreach($addressIterator as $address) {
			switch ($address->type) {
				case 2:
					$this->homeAddress = $address;
				break;
			}
                }
	}

	function populate() {
		$retval = parent::populate();
		$this->_loadAddresses();
		return $retval;
	}

	function getBMI() {
		if (count($this->_vitals) == 0) $this->_loadVitals();
		$weight = 0;
		$height = 0;
		$bmi = 0;
		foreach($this->_vitals as $vital) {
			if ($vital['vital'] == "height" && $vital['units'] == "CM") {
				$height = $vital['value']/100;
			}
			elseif ($vital['vital'] == "weight") {
				$weight = $vital['value'];
			}
		}
		if ($height > 0 && $weight > 0) {
			$bmi = ($weight / ($height * $height));
		}
		if (is_numeric($bmi) && $bmi > 0) {
			return round($bmi,2);
		}
		return "0.00";
	}

	function _loadVitals() {
		$this->_vitals = VitalSignGroup::getBMIVitalsForPatientId($this->personId);
	}

}
