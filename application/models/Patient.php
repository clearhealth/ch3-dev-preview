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
		$this->homeAddress->_cascadePersist = false;
		$this->billingAddress = new Address();
		$this->billingAddress->_cascadePersist = false;
		//$this->phoneNumber = new PhoneNumber();
	}

	public function persist() {
		$this->homeAddress->type = 'HOME';
		$this->billingAddress->type = 'BILL';
		parent::persist();
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
				$this->homeAddress->_cascadePersist = false;
			}
			if ($this->billingAddress->personId > 0) {
				$this->billingAddress = new Address();
				$this->billingAddress->_cascadePersist = false;
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

	public function ssCheck() {
		$ret = array();

		// required SS: Name (last and first), Gender, Date of Birth
		$person = $this->person;
		$lastNameLen = strlen($person->lastName);
		if (!$lastNameLen > 0 || $lastNameLen > 35) {
			$ret[] = 'Last Name field must be supplied and not more than 35 characters';
		}
		$firstNameLen = strlen($person->firstName);
		if (!$firstNameLen > 0 || $firstNameLen > 35) {
			$ret[] = 'First Name field must be supplied and not more than 35 characters';
		}

		$enumeration = new Enumeration();
		$enumeration->enumerationId = $person->gender;
		$enumeration->populate();
		$gender = $enumeration->key;
		// Gender options = M, F, U
		$genderList = array('M'=>'Male','F'=>'Female','U'=>'Unknown');
		if (!isset($genderList[$gender])) {
			$ret[] = 'Gender is invalid';
		}
		// Patient DOB must not be future
		$date = date('Y-m-d');
		$dateOfBirth = date('Ymd',strtotime($person->dateOfBirth));
		if ($person->dateOfBirth == '0000-00-00' || strtotime($dateOfBirth) > strtotime($date)) {
			$ret[] = 'Date of birth is invalid';
		}

		// Have appropriate validation on patient address/phone as required by SS docs
		$address = new Address();
		$address->personId = $this->personId;
		$address->populateWithType('MAIN');
		$line1Len = strlen($address->line1);
		if (!$line1Len > 0 || $line1Len > 35) {
			$ret[] = 'Address line1 field must be supplied and not more than 35 characters';
		}
		$line2Len = strlen($address->line2);
		if ($line2Len > 0 && $line2Len > 35) {
			$ret[] = 'Address line2 must not be more than 35 characters';
		}
		$cityLen = strlen($address->city);
		if (!$cityLen > 0 || $cityLen > 35) {
			$ret[] = 'Address city field must be supplied and not more than 35 characters';
		}
		if (strlen($address->state) != 2) {
			$ret[] = 'Address state field must be supplied and not more than 2 characters';
		}
		$zipCodeLen = strlen($address->zipCode);
		if ($zipCodeLen != 5 && $zipCodeLen != 9) {
			$ret[] = 'Address zipcode must be supplied and must be 5 or 9 digit long';
		}

		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $person->personId;
		$phones = $phoneNumber->phoneNumbers;
		$hasTE = false;
		foreach ($phones as $phone) {
			if ($phone['type'] == 'TE') {
				$hasTE = true;
				$break;
			}
			if (strlen($phone['number']) < 11) {
				$ret[] = 'Phone number \''.$phone['number'].'\' is invalid';
			}
		}
		if (!$hasTE) {
			$ret[] = 'Phone must have at least one Emergency, Employer or Billing';
		}

		return $ret;
	}

}
