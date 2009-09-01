<?php
/*****************************************************************************
*       Enumeration.php
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


class Enumeration extends WebVista_Model_ORM implements NSDRMethods {

	protected $enumerationId;
	protected $guid;
	protected $name;
	protected $key;
	protected $active;
	protected $category;
	protected $ormClass;
	protected $ormId;
	protected $ormEditMethod;

	protected $_table = "enumerations";
	protected $_primaryKeys = array('enumerationId');

	protected $_context = '*';
	protected $_alias = null;

	public function __construct($context='*',$alias=null) {
		$this->_context = $context;
		$this->_alias = $alias;
		parent::__construct();
	}

	public function nsdrPersist($tthis,$context,$data) {
	}

	public function nsdrPopulate($tthis,$context,$data) {
		$ret = '';
		if (preg_match('/^com\.clearhealth\.enumerations\.(.*)$/',$this->_alias,$matches)) {
			$name = $matches[1];
			$enumeration = new self();
			$enumeration->populateByFilter('key',$name);
			$enumerationsClosure = new EnumerationsClosure();
			$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
			$data = array();
			foreach ($enumerationIterator as $enum) {
				if ($this->_context != '*' && $this->_context == $enum->key) {
					$data = $enum->toArray();
					break;
				}
				$data[] = $enum->toArray();
			}
			$ret = $data;
		}
		return $ret;
	}

	public function nsdrMostRecent($tthis,$context,$data) {
	}

	static public function getIterByEnumerationId($enumerationId) {
		$enumerationId = (int) $enumerationId;
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$enumSelect = $db->select()
			->from($enumeration->_table)
			->where('parentId = ' . $enumerationId);
		$iter = $enumeration->getIterator($enumSelect);
		return $iter;
		
	}

	static public function getIterByEnumerationName($name) {
                $enumeration = new self();
                $db = Zend_Registry::get('dbAdapter');
                $enumSelect = $db->select()
                        ->from($enumeration->_table)
                        ->where('parentId = (select enumerationId from enumerations where name=' . $db->quote($name) .')');
                $iter = $enumeration->getIterator($enumSelect);
                return $iter;

        }
	static public function getEnumArray($name,$key = "key", $value = "name") {
                $iter = self::getIterByEnumerationName($name);
                return $iter->toArray($key, $value);

        }

	/**
	 * Get Enumeration Iterator by distinct category
	 *
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByDistinctCategories() {
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($enumeration->_table)
			       ->where("category != ''")
			       ->group("category");
		return $enumeration->getIterator($dbSelect);
	}

	/**
	 * Get Enumeration Iterator by distinct name given an enumerationId of category
	 *
	 * @param int $enumerationId Enumeration ID of category
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByDistinctNames($enumerationId) {
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$innerDbSelect = $db->select()->from($enumeration->_table,"category")
				    ->where('enumerationId = ?',(int)$enumerationId);
		$dbSelect = $db->select()->from(array('e1'=>$enumeration->_table))
			       ->joinLeft(array('e2'=>$enumeration->_table),'e2.category = e1.category',array())
			       ->where("e2.category = ({$innerDbSelect})")
			       ->group('e1.name');
		//trigger_error($dbSelect,E_USER_NOTICE);
		return $enumeration->getIterator($dbSelect);
	}


	/**
	 * Get Enumeration Iterator by name given an enumeration's name
	 *
	 * @param string $name Enumeration's name
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByName($name) {
		$name = preg_replace('/[^a-z_0-9-]/i','',$name);
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from($enumeration->_table)
			       ->where('name = ?',$name);
		//trigger_error($dbSelect,E_USER_NOTICE);
		return $enumeration->getIterator($dbSelect);
	}

	public function populateByEnumerationName($name) {
		return $this->populateByFilter('name',$name);
	}

	public function populateByFilter($key,$val) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where("`{$key}` = ?",$val);
		$retval = $this->populateWithSql($dbSelect->__toString());
		$this->postPopulate();
		return $retval;
	}

	public static function generateTestData() {
		self::generateContactPreferencesEnum();
		self::generateMenuEnum();
		self::generateGenderEnum();
		self::generateMaritalStatusEnum();
		self::generateImmunizationPreferencesEnum();
		self::generateTeamPreferencesEnum();
	}

	public static function generateContactPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Contact Preferences';
			$key = 'CP';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}
			$level1 = array();
			$level1['key'] = 'PT';
			$level1['name'] = 'Phone Types';
			$level1['active'] = 1;

			$level11 = array();
			$level11['key'] = 'HM';
			$level11['name'] = 'Home';
			$level11['active'] = 1;

			$level12 = array();
			$level12['key'] = 'WK';
			$level12['name'] = 'Work';
			$level12['active'] = 1;

			$level13 = array();
			$level13['key'] = 'BL';
			$level13['name'] = 'Billing';
			$level13['active'] = 1;

			$level14 = array();
			$level14['key'] = 'MB';
			$level14['name'] = 'Mobile';
			$level14['active'] = 1;

			$level15 = array();
			$level15['key'] = 'EM';
			$level15['name'] = 'Emergency';
			$level15['active'] = 1;

			$level16 = array();
			$level16['key'] = 'FX';
			$level16['name'] = 'Fax';
			$level16['active'] = 1;

			$level1['data'] = array();
			$level1['data'][] = $level11;
			$level1['data'][] = $level12;
			$level1['data'][] = $level13;
			$level1['data'][] = $level14;
			$level1['data'][] = $level15;
			$level1['data'][] = $level16;

			$level2 = array();
			$level2['key'] = 'AT';
			$level2['name'] = 'Address Types';
			$level2['active'] = 1;

			$level21 = array();
			$level21['key'] = 'HM';
			$level21['name'] = 'Home';
			$level21['active'] = 1;

			$level22 = array();
			$level22['key'] = 'EMP';
			$level22['name'] = 'Employer';
			$level22['active'] = 1;

			$level23 = array();
			$level23['key'] = 'BL';
			$level23['name'] = 'Billing';
			$level23['active'] = 1;

			$level24 = array();
			$level24['key'] = 'OT';
			$level24['name'] = 'Other';
			$level24['active'] = 1;

			$level25 = array();
			$level25['key'] = 'MN';
			$level25['name'] = 'Main';
			$level25['active'] = 1;

			$level26 = array();
			$level26['key'] = 'SC';
			$level26['name'] = 'Secondary';
			$level26['active'] = 1;

			$level2['data'] = array();
			$level2['data'][] = $level21;
			$level2['data'][] = $level22;
			$level2['data'][] = $level23;
			$level2['data'][] = $level24;
			$level2['data'][] = $level25;
			$level2['data'][] = $level26;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateMenuEnum($force = false) {
		$ret = false;
		do {
			$name = 'Menu';
			$key = 'M';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}
			$level1 = array();
			$level1['key'] = 'F';
			$level1['name'] = 'File';
			$level1['active'] = 1;
			$level1['ormClass'] = 'MenuItem';
			$level1['ormEditMethod'] = 'ormEditMethod';

			$level11 = array();
			$level11['key'] = 'SP';
			$level11['name'] = 'Select Patient';
			$level11['active'] = 1;
			$level11['ormClass'] = 'MenuItem';
			$level11['ormEditMethod'] = 'ormEditMethod';

			$level12 = array();
			$level12['key'] = 'AP';
			$level12['name'] = 'Add Patient';
			$level12['active'] = 1;
			$level12['ormClass'] = 'MenuItem';
			$level12['ormEditMethod'] = 'ormEditMethod';

			$level13 = array();
			$level13['key'] = 'RSC';
			$level13['name'] = 'Review / Sign Changes';
			$level13['active'] = 1;
			$level13['ormClass'] = 'MenuItem';
			$level13['ormEditMethod'] = 'ormEditMethod';

			$level14 = array();
			$level14['key'] = 'Q';
			$level14['name'] = 'Quit';
			$level14['active'] = 1;
			$level14['ormClass'] = 'MenuItem';
			$level14['ormEditMethod'] = 'ormEditMethod';

			$level1['data'] = array();
			$level1['data'][] = $level11;
			$level1['data'][] = $level12;
			$level1['data'][] = $level13;
			$level1['data'][] = $level14;

			$level2 = array();
			$level2['key'] = 'A';
			$level2['name'] = 'Action';
			$level2['active'] = 1;
			$level2['ormClass'] = 'MenuItem';
			$level2['ormEditMethod'] = 'ormEditMethod';

			$level21 = array();
			$level21['key'] = 'AV';
			$level21['name'] = 'Add Vitals';
			$level21['active'] = 1;
			$level21['ormClass'] = 'MenuItem';
			$level21['ormEditMethod'] = 'ormEditMethod';

			$level22 = array();
			$level22['key'] = 'P';
			$level22['name'] = 'Print';
			$level22['active'] = 1;
			$level22['ormClass'] = 'MenuItem';
			$level22['ormEditMethod'] = 'ormEditMethod';

			$level221 = array();
			$level221['key'] = 'FS';
			$level221['name'] = 'Flow Sheet';
			$level221['active'] = 1;
			$level221['ormClass'] = 'MenuItem';
			$level221['ormEditMethod'] = 'ormEditMethod';

			$level22['data'] = array();
			$level22['data'][] = $level221;

			$level2['data'] = array();
			$level2['data'][] = $level21;
			$level2['data'][] = $level22;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['ormClass'] = 'MenuItem';
			$level0['ormEditMethod'] = 'ormEditMethod';
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateGenderEnum($force = false) {
		$ret = false;
		do {
			$name = 'Gender';
			$key = 'G';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}
			$data = array();
			$tmp = array();
			$tmp['key'] = $key;
			$tmp['name'] = $name;
			$tmp['category'] = 'System';
			$tmp['active'] = 1;
			$tmp['data'][] = array('key'=>'M','name'=>'Male','active'=>1);
			$tmp['data'][] = array('key'=>'F','name'=>'Female','active'=>1);
			$tmp['data'][] = array('key'=>'U','name'=>'Unknown','active'=>1);
			$data[] = $tmp;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateMaritalStatusEnum($force = false) {
		$ret = false;
		do {
			$name = 'Status';
			$key = 'S';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}
			$data = array();
			$tmp = array();
			$tmp['key'] = $key;
			$tmp['name'] = $name;
			$tmp['category'] = 'System';
			$tmp['active'] = 1;
			$tmp['data'][] = array('key'=>'S','name'=>'Single','active'=>1);
			$tmp['data'][] = array('key'=>'M','name'=>'Married','active'=>1);
			$tmp['data'][] = array('key'=>'D','name'=>'Divorced','active'=>1);
			$data[] = $tmp;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateImmunizationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Immunization Preferences';
			$key = 'IP';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();

			$level11 = array();
			$level11['key'] = "sections";
			$level11['name'] = "Sections";
			$level11['active'] = 1;

			$enumerationLevel2 = array();
			$enumerationLevel2[] = 'BCG';
			$enumerationLevel2[] = 'DT (pediatric)';
			$enumerationLevel2[] = 'DTaP';
			$enumerationLevel2[] = 'DTaP-Hep B-IPV';
			$enumerationLevel2[] = 'DTaP-Hib';
			$enumerationLevel2[] = 'DTaP-Hib-IPV';
			$enumerationLevel2[] = 'DTP';
			$enumerationLevel2[] = 'DTP-Hib';
			$enumerationLevel2[] = 'DTP-Hib-Hep B';
			$enumerationLevel2[] = 'Hep A-Hep B';
			$enumerationLevel2[] = 'Hep A, adult';
			$enumerationLevel2[] = 'Hep A, NOS';

			foreach ($enumerationLevel2 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = $key;
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$level11['data'][] = $tmp;
			}
			$level2 = array();
			$level2['key'] = count($level11['data']);
			$level2['name'] = 'Hep A, pediatric, NOS';
			$level2['active'] = 1;
			$level2['data'] = array();
			$enumerationLevel3 = array();
			$enumerationLevel3[] = 'Hep A, ped/adol, 2 dose';
			$enumerationLevel3[] = 'Hep A, ped/adol, 3 dose';
			foreach ($enumerationLevel3 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = $key;
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$level2['data'][] = $tmp;
			}
			$level11['data'][] = $level2;

			$enumerationLevel2[] = 'Hep B, adolescent or pediatric';
			$enumerationLevel2[] = 'Hep B, adolescent/high risk infant';
			$enumerationLevel2[] = 'Hep B, adult4';
			$enumerationLevel2[] = 'Hep B, dialysis';
			$enumerationLevel2[] = 'Hib (PRP-OMP)';
			$enumerationLevel2[] = 'Hib-Hep B';
			$enumerationLevel2[] = 'Hib, NOS';
			$enumerationLevel2[] = 'IG';
			$enumerationLevel2[] = 'influenza, live, intranasal';
			$enumerationLevel2[] = 'influenza, NOS';
			$enumerationLevel2[] = 'influenza, split (incl. purified surface antigen)';
			$enumerationLevel2[] = 'IPV';
			$enumerationLevel2[] = 'Japanese encephalitis';
			$enumerationLevel2[] = 'M/R';
			$enumerationLevel2[] = 'measles';
			$enumerationLevel2[] = 'meningococcal';
			$enumerationLevel2[] = 'meningococcal A,C,Y,W-135 diphtheria conjugate';
			$enumerationLevel2[] = 'MMR';
			$enumerationLevel2[] = 'MMRV';
			$enumerationLevel2[] = 'mumps';
			$enumerationLevel2[] = 'OPV';
			$enumerationLevel2[] = 'pneumococcal';
			$enumerationLevel2[] = 'pneumococcal conjugate';
			$enumerationLevel2[] = 'pneumococcal, NOS';
			$enumerationLevel2[] = 'polio, NOS';
			$enumerationLevel2[] = 'rabies, NOS';
			$enumerationLevel2[] = 'RIG';
			$enumerationLevel2[] = 'rotavirus, monovalent';
			$enumerationLevel2[] = 'rotavirus, NOS';
			$enumerationLevel2[] = 'rotavirus, pentavalent';
			$enumerationLevel2[] = 'rotavirus, tetravalent';
			$enumerationLevel2[] = 'rubella';
			$enumerationLevel2[] = 'rubella/mumps';
			$enumerationLevel2[] = 'Td (adult)';
			$enumerationLevel2[] = 'Tdap';
			$enumerationLevel2[] = 'typhoid, oral';
			$enumerationLevel2[] = 'typhoid, ViCPs';
			$enumerationLevel2[] = 'varicella';
			$enumerationLevel2[] = 'yellow fever';
			$enumerationLevel2[] = 'zoster';
			foreach ($enumerationLevel2 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = $key;
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$level11['data'][] = $tmp;
			}

			$level12 = array();
			$level12['key'] = "reactions";
			$level12['name'] = "Reactions";
			$level12['active'] = 1;
			$enumerationLevel2 = array();
			$enumerationLevel2[] = "Fever";
			$enumerationLevel2[] = "Irritability";
			$enumerationLevel2[] = "Local reaction or swelling";
			$enumerationLevel2[] = "Vomiting";
			foreach ($enumerationLevel2 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = $key;
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$level12['data'][] = $tmp;
			}

			$level0['data'][] = $level11;
			$level0['data'][] = $level12;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateTeamPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Team Preferences';
			$key = 'TP';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}
			$level111 = array();
			$level111['key'] = 'N';
			$level111['name'] = 'Nurse';
			$level111['active'] = 1;
			$level111['ormClass'] = 'TeamMember';
			$level111['ormEditMethod'] = 'ormEditMethod';

			$level112 = array();
			$level112['key'] = 'PA';
			$level112['name'] = 'Physician Assistant';
			$level112['active'] = 1;
			$level112['ormClass'] = 'TeamMember';
			$level112['ormEditMethod'] = 'ormEditMethod';

			$level113 = array();
			$level113['key'] = 'NP1';
			$level113['name'] = 'Nurse Practitioner';
			$level113['active'] = 1;
			$level113['ormClass'] = 'TeamMember';
			$level113['ormEditMethod'] = 'ormEditMethod';

			$level114 = array();
			$level114['key'] = 'NP2';
			$level114['name'] = 'Nurse Practitioner';
			$level114['active'] = 1;
			$level114['ormClass'] = 'TeamMember';
			$level114['ormEditMethod'] = 'ormEditMethod';

			$level11 = array();
			$level11['key'] = 'A';
			$level11['name'] = 'Attending';
			$level11['active'] = 1;
			$level11['ormClass'] = 'TeamMember';
			$level11['ormEditMethod'] = 'ormEditMethod';
			$level11['data'] = array();
			$level11['data'][] = $level111;
			$level11['data'][] = $level112;
			$level11['data'][] = $level113;
			$level11['data'][] = $level114;

			$level1 = array();
			$level1['key'] = 'B';
			$level1['name'] = 'Blue';
			$level1['active'] = 1;
			$level1['ormClass'] = 'TeamMember';
			$level1['ormEditMethod'] = 'ormEditMethod';
			$level1['data'] = array();
			$level1['data'][] = $level11;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['ormClass'] = 'TeamMember';
			$level0['ormEditMethod'] = 'ormEditMethod';
			$level0['data'] = array();
			$level0['data'][] = $level1;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	protected static function _saveEnumeration($data,$parentId=0) {
		$enumerationsClosure = new EnumerationsClosure();
		foreach ($data as $item) {
			$enumerationId = $enumerationsClosure->insertEnumeration($item,$parentId);
			if (isset($item['data'])) {
				self::_saveEnumeration($item['data'],$enumerationId);
			}
		}
	}

}
