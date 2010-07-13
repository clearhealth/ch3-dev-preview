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
	protected $displayOrder;
	protected $practiceId;
	protected $_table = "number";
	protected $_primaryKeys = array('number_id');
	protected $_legacyORMNaming = true;
	
	const TYPE_HOME = 'HOME';
	const TYPE_WORK = 'WORK';
	const TYPE_BILLING = 'BILL';
	const TYPE_EMPLOYER = 'EMPL';
	const TYPE_MOBILE = 'MOB';
	const TYPE_EMERGENCY = 'EMER';
	const TYPE_FAX = 'FAX';
	const TYPE_HOME_EVE = 'HOME_EVE';
	const TYPE_HOME_DAY = 'HOME_DAY';
	const TYPE_BEEPER = 'BEEPER';

	public function getIteratorByPatientId($patientId = null,$active = false) {
		if ($patientId === null) {
			$patientId = $this->personId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('active = ?',(int)$active)
				->where('person_id = ?',(int)$patientId);
		$iter = $this->getIterator();
		$iter->setDbSelect($sqlSelect);
		return $iter;
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

	public function populateWithType($type) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('person_id = ?',(int)$this->person_id)
//				->where('type = ?',(int)$type) // temporarily comment out
				->where('active = 1')
				->limit(1);
		$this->populateWithSql($sqlSelect->__toString());
	}

	public static function getListPhoneTypes() {
		$enumeration = new Enumeration();
		$enumeration->populateByUniqueName('Contact Preferences');

		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enum) {
			if ($enum->name != 'Phone Types') continue;
			$ret = $enumerationsClosure->getAllDescendants($enum->enumerationId,1)->toArray('key','name');
			break;
		}
		return $ret;
	}

	public static function nextDisplayOrder($personId) {
		$orm = new self();
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($orm->_table,'MAX(displayOrder) AS displayOrder')
				->where('person_id = ?',(int)$personId);
		$ret = 1;
		if ($row = $db->fetchRow($sqlSelect)) {
			$ret = $row['displayOrder'] + 1;
		}
		return $ret;
	}

	public function getPhoneNumbers() {
		$phoneNumberIterator = $this->getIteratorByPatientId(null,true);
		$phones = array();
		foreach ($phoneNumberIterator as $number) {
			//if (!strlen($number->number) > 0) continue;
			// SS Type options: BN - Beeper, CP - Cellular, FX - Fax, HP - Home, NP - Night, TE – Telephone*, WP – Work
			$type = '';
			switch ($number->type) {
				case self::TYPE_HOME:
				case self::TYPE_HOME_DAY:
					$type = 'HP'; 
					break;
				case self::TYPE_WORK:
					$type = 'WP';
					break;
				case self::TYPE_MOBILE:
					$type = 'CP';
					break;
				case self::TYPE_FAX:
					$type = 'FX';
					break;
				case self::TYPE_HOME_EVE:
					$type = 'NP';
					break;
				case self::TYPE_BEEPER:
					$type = 'BN';
					break;
				case self::TYPE_EMERGENCY:
				case self::TYPE_EMPLOYER:
				case self::TYPE_BILLING:
					$type = 'TE';
					break;
				default:
					continue;
			}
			// auto-format phone number
			$number->number = self::autoFixNumber($number->number);
			$phones[$type][] = array('number'=>$number->number,'type'=>$type);
		}
		$telephoneNumbers = array();
		$te = null;
		if (isset($phones['TE'])) {
			$te = array_pop($phones['TE']);
			$telephoneNumbers = $phones['TE'];
			unset($phones['TE']);
		}
		$faxNumbers = array();
		$fx = null;
		if (isset($phones['FX'])) {
			$fx = array_pop($phones['FX']);
			$faxNumbers = $phones['FX'];
			unset($phones['FX']);
		}
		if ($te === null) {
			if (count($phones) > 0) {
				if (isset($phones['HP'])) {
					$te = array_pop($phones['HP']);
				}
				else if (isset($phones['WP'])) {
					$te = array_pop($phones['WP']);
				}
				else if (isset($phones['CP'])) {
					$te = array_pop($phones['CP']);
				}
				else if (isset($phones['NP'])) {
					$te = array_pop($phones['NP']);
				}
				else if (isset($phones['BN'])) {
					$te = array_pop($phones['BN']);
				}
			}
			else if ($fx !== null) {
				$te = $fx;
			}
		}
		/*if ($fx === null) {
			if (count($phones) > 0) {
				$fx = array_pop($phones);
			}
			else if ($te !== null) {
				$fx = $te;
			}
		}*/

		$ret = array();
		if ($te !== null) {
			$te['type'] = 'TE';
			$ret[] = $te;
		}
		if ($fx !== null) {
			$fx['type'] = 'FX';
			$ret[] = $fx;
		}
		foreach ($telephoneNumbers as $p) {
			$ret[] = $p;
		}
		foreach ($faxNumbers as $p) {
			$ret[] = $p;
		}
		foreach ($phones as $type=>$p) {
			foreach ($p as $v) {
				$ret[] = $v;
			}
		}
		return $ret;
	}

	public function persist() {
		if ($this->_persistMode != WebVista_Model_ORM::DELETE && (int)$this->displayOrder <= 0) {
			$this->displayOrder = self::nextDisplayOrder($this->personId);
		}
		return parent::persist();
	}

	public static function autoFixNumber($number) {
		$x = explode('x',strtolower($number));
		$ret = preg_replace('/[^0-9]*/','',$x[0]);
		if (strlen($ret) == 10) {
			$ret = str_pad($ret,11,'1',STR_PAD_LEFT);
		}
		if (isset($x[1])) { // with extension
			$ret .= 'x'.preg_replace('/[^0-9]*/','',$x[1]);
		}
		return $ret;
	}

	public function populateWithPracticeIdType($practiceId = null,$type = null) {
		if ($practiceId === null) {
			$practiceId = $this->practiceId;
		}
		if ($type === null) {
			$type = $this->type;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('practiceId = ?',(int)$practiceId)
				->where('type = ?',$type);
		$this->populateWithSql($sqlSelect->__toString());
	}

}
