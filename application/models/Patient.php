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

	public function setPerson_id($key) {
		$this->setPersonId($key);
	}

	function setPersonId($key) {
		$id = (int)$key;
		if ($id != $this->person_id) { // personId has been changed
			if ($this->person->personId > 0) {
				$this->person = new Person();
			}
			if ($this->homeAddress->personId > 0) {
				$this->homeAddress = new Address();
			}
			if ($this->billingAddress->personId > 0) {
				$this->billingAddress = new Address();
			}
		}
		$this->person_id = $id; // person_id MUST be the same name as declared
		$this->person->personId = $id;
		$this->homeAddress->personId = $id;
		$this->billingAddress->personId = $id;
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
                elseif (!is_null(parent::__get($key))) {
                        return parent::__get($key);
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

	public function getBMI() {
		if (count($this->_vitals) == 0) $this->_loadVitals();
		foreach ($this->_vitals as $vital) {
			if ($vital['vital'] == 'BMI') return $vital['value'];
		}

		return '0.00';
	}

	public function getBSA() {
		if (count($this->_vitals) == 0) $this->_loadVitals();
		foreach ($this->_vitals as $vital) {
			if ($vital['vital'] == 'BSA') return sprintf('%.2f',$vital['value']);
		}

		return '0.00';
	}

	function _loadVitals() {
		$this->_vitals = VitalSignGroup::getBMIVitalsForPatientId($this->personId);
	}

	public function populateWithMRN($mrn = null) {
		if ($mrn === null) {
			$mrn = $this->recordNumber;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('patient','person_id')
				->where('record_number = ?',$mrn);
		$ret = false;
		if ($row = $db->fetchRow($sqlSelect)) {
			$this->personId = $row['person_id'];
			$this->populate();
			$ret = true;
		}
		return $ret;
	}

}
