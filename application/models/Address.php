<?php
/*****************************************************************************
*       Address.php
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


class Address extends WebVista_Model_ORM {

	protected $address_id;
	protected $person_id;
	protected $name;
	protected $type;
	protected $active;
	protected $line1;
	protected $line2;
	protected $city;
	protected $region;
	protected $county;
	protected $state;
	protected $postal_code;
	protected $notes;
	protected $practiceId;
	protected $displayOrder;
	protected $_table = 'address';
	protected $_primaryKeys = array('address_id');
	protected $_legacyORMNaming = true;

	const ENUM_STATES_NAME = 'States';
	const ENUM_COUNTRIES_NAME = 'Countries';

	const TYPE_MAIN = 'MAIN';
	const TYPE_SEC = 'SEC';

	public function populateWithPersonId() {
		$db = Zend_Registry::get('dbAdapter');
		//address_type 4 is main
		$sql = "SELECT * from " . $this->_table  
			." INNER JOIN person_address per2add on per2add.address_id = address.address_id WHERE 1 and per2add.address_type = 4  and per2add.person_id = " . (int) $db->quote($this->person_id);
		$this->populateWithSql($sql);
	}

	public function getPrintState() {
		$db = Zend_Registry::get('dbAdapter');
		$sql = "select * from enumeration_definition enumDef 
			inner join enumeration_value enumVal on enumVal.enumeration_id = enumDef.enumeration_id
			where enumDef.name = 'state' and enumVal.key = " . (int) $this->state;
		$ret = '';
		if ($row = $db->query($sql)->fetchAll()) {
			$ret = $row[0]['value'];
		}
		return $ret;
	}

	public function getDisplayCounty() {
		$enumeration = new Enumeration();
		$enumeration->populateByFilter('key',$this->county);
		$ret = '';
		if (strlen($enumeration->name) > 0) {
			$ret = $enumeration->name;
		}
		return $ret;
	}

	public function getDisplayState() {
		$enumeration = new Enumeration();
		$enumeration->populateByFilter('key',$this->state);
		$ret = '';
		if (strlen($enumeration->name) > 0) {
			$ret = $enumeration->name;
		}
		return $ret;
	}

	public static function getCountriesList() {
		$name = self::ENUM_COUNTRIES_NAME;
		$enumerationIterator = self::_getEnumerationIterator($name);
		$ret = array();
		foreach ($enumerationIterator as $enumeration) {
			$ret[$enumeration->key] = $enumeration->name;
		}
		return $ret;
	}

	public static function getStatesList() {
		$name = self::ENUM_STATES_NAME;
		$enumerationIterator = self::_getEnumerationIterator($name);
		$ret = array();
		foreach ($enumerationIterator as $enumeration) {
			$ret[$enumeration->key] = $enumeration->name;
		}
		return $ret;
	}

	protected static function _getEnumerationIterator($name) {
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumeration->populate();

		$enumerationsClosure = new EnumerationsClosure();
		return $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
	}

	public function populateWithPracticeIdType($practiceId=null,$type=null) {
		if ($practiceId === null) {
			$practiceId = (int)$this->practiceId;
		}
		if ($type === null) {
			$type = $this->type;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('practiceId = ?',(int)$practiceId)
				->where('type = ?',$type)
				->limit(1);
		$this->populateWithSql($sqlSelect->__toString());
	}

	public function populateWithType($type,$forced=false) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('person_id = ?',(int)$this->person_id)
//				->where('type = ?',$type) // temporarily comment out
				->where('active = 1')
				->limit(1);
		if ($forced) {
			$sqlSelect->where('type = ?',$type);
		}
		$this->populateWithSql($sqlSelect->__toString());
	}

	public static function getListAddressTypes() {
		$enumeration = new Enumeration();
		$enumeration->populateByUniqueName('Contact Preferences');

		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enum) {
			if ($enum->name != 'Address Types') continue;
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

	public function persist() {
		if ($this->_persistMode != WebVista_Model_ORM::DELETE && (int)$this->displayOrder <= 0) {
			$this->displayOrder = self::nextDisplayOrder($this->personId);
		}
		return parent::persist();
	}

	public function getZipCode() {
		return preg_replace('/[^0-9]*/','',$this->postal_code);
	}

}
