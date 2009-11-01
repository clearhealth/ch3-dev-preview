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
	static public function getEnumArray($name,$key='key',$value='name') {
                //$iter = self::getIterByEnumerationName($name);
                //return $iter->toArray($key, $value);

		$enumeration = new self();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enumeration) {
			$ret[$enumeration->$key] = $enumeration->$value;
		}
		return $ret;
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
		self::generateHSAPreferencesEnum();
		self::generateReasonPreferencesEnum();
		self::generateReactionTypePreferencesEnum();
		self::generateSeverityPreferencesEnum();
		self::generateSymptomPreferencesEnum();
		self::generateAppointmentReasonEnum();
		self::generateProcedurePreferencesEnum();
		self::generatePatientEducationPreferencesEnum();
		self::generateEducationTopicPreferencesEnum();
		self::generateEducationLevelPreferencesEnum();
		self::generatePatientExamPreferencesEnum();
		self::generateExamResultPreferencesEnum();
		self::generateExamOtherPreferencesEnum();
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

			$level11 = array();
			$level11['key'] = 'AP';
			$level11['name'] = 'Add Patient';
			$level11['active'] = 1;

			$level12 = array();
			$level12['key'] = 'SP';
			$level12['name'] = 'Select Patient';
			$level12['active'] = 1;

			$level13 = array();
			$level13['key'] = 'RSC';
			$level13['name'] = 'Review / Sign Changes';
			$level13['active'] = 1;

			$level14 = array();
			$level14['key'] = 'CP';
			$level14['name'] = 'Change Password';
			$level14['active'] = 1;

			$level15 = array();
			$level15['key'] = 'ESK';
			$level15['name'] = 'Edit Signing Key';
			$level15['active'] = 1;

			$level16 = array();
			$level16['key'] = 'Q';
			$level16['name'] = 'Quit';
			$level16['active'] = 1;

			$level1['data'] = array();
			$level1['data'][] = $level11;
			$level1['data'][] = $level12;
			$level1['data'][] = $level13;
			$level1['data'][] = $level14;
			$level1['data'][] = $level15;
			$level1['data'][] = $level16;

			$level2 = array();
			$level2['key'] = 'A';
			$level2['name'] = 'Action';
			$level2['active'] = 1;

			$level21 = array();
			$level21['key'] = 'AV';
			$level21['name'] = 'Add Vitals';
			$level21['active'] = 1;

			$level22 = array();
			$level22['key'] = 'P';
			$level22['name'] = 'Print';
			$level22['active'] = 1;

			$level221 = array();
			$level221['key'] = 'FS';
			$level221['name'] = 'Flow Sheet';
			$level221['active'] = 1;

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

			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);

			$menu = new MenuItem();
			$menu->siteSection = 'All';
			$menu->type = 'freeform';
			$menu->active = 1;
			$menu->title = $enumeration->name;
			$menu->displayOrder = 0;
			$menu->parentId = 0;
			$menu->persist();

			$enumeration->ormId = $menu->menuId;
			$enumeration->persist();
			self::_generateMenuEnumerationTree($enumeration);

			$ret = true;
		} while(false);
		return $ret;
	}

	protected static function _generateMenuEnumerationTree(Enumeration $enumeration) {
		$enumerationId = $enumeration->enumerationId;
		$enumerationsClosure = new EnumerationsClosure();
		$descendants = $enumerationsClosure->getEnumerationTreeById($enumerationId);
		$displayOrder = 0;
		foreach ($descendants as $enum) {
			$displayOrder += 10;
			$menu = new MenuItem();
			$menu->siteSection = 'All';
			$menu->type = 'freeform';
			$menu->active = 1;
			$menu->title = $enum->name;
			//$menu->displayOrder = $displayOrder;
			$menu->displayOrder = $enum->enumerationId; // temporarily set displayOrder using the enumerationId
			$menu->parentId = $enumerationId;
			$menu->persist();

			$enum->ormId = $menu->menuId;
			$enum->persist();

			if ($enumerationId != $enum->enumerationId) { // prevents infinite loop
				self::_generateMenuEnumerationTree($enum);
			}
		}
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
			$name = TeamMember::ENUM_PARENT_NAME;
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

	public static function generateHSAPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = HealthStatusAlert::ENUM_PARENT_NAME;
			$key = 'HP';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'LSA';
			$level1['name'] = 'Lab Status Alerts';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'VSA';
			$level2['name'] = 'Vitals Status Alerts';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'NSA';
			$level3['name'] = 'Note Status Alerts';
			$level3['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateReasonPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientNote::ENUM_REASON_PARENT_NAME;
			$key = 'RP';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'RPCB';
			$level1['name'] = 'Call Back';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'RPCP';
			$level2['name'] = 'Check Progress';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'RPC';
			$level3['name'] = 'Converted';
			$level3['active'] = 1;

			$level4 = array();
			$level4['key'] = 'RPRT';
			$level4['name'] = 'Repeat Test';
			$level4['active'] = 1;

			$level5 = array();
			$level5['key'] = 'RPO';
			$level5['name'] = 'Other';
			$level5['active'] = 1;

			$level6 = array();
			$level6['key'] = 'RPNA';
			$level6['name'] = 'N/A';
			$level6['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;
			$level0['data'][] = $level6;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateReactionTypePreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientAllergy::ENUM_REACTION_TYPE_PARENT_NAME;
			$key = 'REACTYPE';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'ALLERGY';
			$level1['name'] = 'Allergy';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'PHARMA';
			$level2['name'] = 'Pharmacological';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'UK';
			$level3['name'] = 'Unknown';
			$level3['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateSeverityPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientAllergy::ENUM_SEVERITY_PARENT_NAME;
			$key = 'SEVERITY';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'MILD';
			$level1['name'] = 'Mild';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'MOD';
			$level2['name'] = 'Moderate';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'SEVERE';
			$level3['name'] = 'Severe';
			$level3['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateSymptomPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientAllergy::ENUM_SYMPTOM_PARENT_NAME;
			$key = 'SYMPTOM';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'CONFUSE';
			$level1['name'] = 'CONFUSION';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'ITCHING';
			$level2['name'] = 'ITCHING, WATERING EYES';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'HYPO';
			$level3['name'] = 'HYPOTENSION';
			$level3['active'] = 1;

			$level4 = array();
			$level4['key'] = 'DROWSE';
			$level4['name'] = 'DROWSINESS';
			$level4['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateAppointmentReasonEnum($force = false) {
		$ret = false;
		do {
			$name = AppointmentTemplate::ENUM_PARENT_NAME;
			$key = 'APP_REASON';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$appointmentTemplate = new AppointmentTemplate();

			$level1 = array();
			$level1['key'] = 'Provider';
			$level1['name'] = 'Provider';
			$level1['active'] = 1;
			$level1['ormClass'] = 'AppointmentTemplate';
			$level1['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Provider';
			$appointmentTemplate->persist();
			$level1['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level2 = array();
			$level2['key'] = 'Specialist';
			$level2['name'] = 'Specialist';
			$level2['active'] = 1;
			$level2['ormClass'] = 'AppointmentTemplate';
			$level2['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Specialist';
			$appointmentTemplate->persist();
			$level2['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level3 = array();
			$level3['key'] = 'MedPhone';
			$level3['name'] = 'Medical Phone';
			$level3['active'] = 1;
			$level3['ormClass'] = 'AppointmentTemplate';
			$level3['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Medical Phone';
			$appointmentTemplate->persist();
			$level3['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level4 = array();
			$level4['key'] = 'MedPU';
			$level4['name'] = 'Medication PU';
			$level4['active'] = 1;
			$level4['ormClass'] = 'AppointmentTemplate';
			$level4['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Medication PU';
			$appointmentTemplate->persist();
			$level4['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level5 = array();
			$level5['key'] = 'Education';
			$level5['name'] = 'Education';
			$level5['active'] = 1;
			$level5['ormClass'] = 'AppointmentTemplate';
			$level5['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Education';
			$appointmentTemplate->persist();
			$level5['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level6 = array();
			$level6['key'] = 'Eligibility';
			$level6['name'] = 'Eligibility';
			$level6['active'] = 1;
			$level6['ormClass'] = 'AppointmentTemplate';
			$level6['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Eligibility';
			$appointmentTemplate->persist();
			$level6['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level7 = array();
			$level7['key'] = 'BlockedTime';
			$level7['name'] = 'Blocked Time';
			$level7['active'] = 1;
			$level7['ormClass'] = 'AppointmentTemplate';
			$level7['ormEditMethod'] = 'ormEditMethod';
			$appointmentTemplate->appointmentTemplateId = 0;
			$appointmentTemplate->name = 'Blocked Time';
			$appointmentTemplate->persist();
			$level7['ormId'] = $appointmentTemplate->appointmentTemplateId;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['ormClass'] = 'AppointmentTemplate';
			$level0['ormEditMethod'] = 'ormEditMethod';
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;
			$level0['data'][] = $level6;
			$level0['data'][] = $level7;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateProcedurePreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientProcedure::ENUM_PARENT_NAME;
			$key = 'ProcPref';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'GIPROC';
			$level1['name'] = 'GI PROCEDURES';
			$level1['active'] = 1;

			$level2['key'] = 'COLON';
			$level2['name'] = 'COLONOSCOPY';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generatePatientEducationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_PARENT_NAME;
			$key = 'PatEduPref';
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
			$level11['key'] = 'sections';
			$level11['name'] = 'Sections';
			$level11['active'] = 1;

			$data = array($level0);
			//self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateEducationTopicPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_TOPIC_PARENT_NAME;
			$key = 'Educ_Topic';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'HFA';
			$level1['name'] = 'HF ACTIVITY';
			$level1['active'] = 1;

			$level2['key'] = 'HFD';
			$level2['name'] = 'HF DIET';
			$level2['active'] = 1;

			$level3['key'] = 'HFDM';
			$level3['name'] = 'HF DISCHARGE MEDS';
			$level3['active'] = 1;

			$level4['key'] = 'HFF';
			$level4['name'] = 'HF FOLLOWUP';
			$level4['active'] = 1;

			$level5['key'] = 'HFS';
			$level5['name'] = 'HF SYMPTOMS';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateEducationLevelPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_LEVEL_PARENT_NAME;
			$key = 'Educ_Level';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'POOR';
			$level1['name'] = 'Poor';
			$level1['active'] = 1;

			$level2['key'] = 'FAIR';
			$level2['name'] = 'Fair';
			$level2['active'] = 1;

			$level3['key'] = 'GOOD';
			$level3['name'] = 'Good';
			$level3['active'] = 1;

			$level4['key'] = 'GNA';
			$level4['name'] = 'Group-no assessment';
			$level4['active'] = 1;

			$level5['key'] = 'REFUSED';
			$level5['name'] = 'Refused';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generatePatientExamPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_PARENT_NAME;
			$key = 'PatExPref';
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
			$level11['key'] = 'sections';
			$level11['name'] = 'Sections';
			$level11['active'] = 1;

			$data = array($level0);
			//self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateExamResultPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_RESULT_PARENT_NAME;
			$key = 'Exam_Res';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'Exam_Abn';
			$level1['name'] = 'Abnormal';
			$level1['active'] = 1;

			$level2['key'] = 'Exam_Norm';
			$level2['name'] = 'Normal';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateExamOtherPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_OTHER_PARENT_NAME;
			$key = 'Exam_Other';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'EXAM_ABD';
			$level1['name'] = 'ABDOMEN EXAM';
			$level1['active'] = 1;

			$level2['key'] = 'EXAM_AMS';
			$level2['name'] = 'AUDIOMETRIC SCREENING';
			$level2['active'] = 1;

			$level3['key'] = 'EXAM_AMT';
			$level3['name'] = 'AUDIOMETRIC THRESHOLD';
			$level3['active'] = 1;

			$level4['key'] = 'EXAM_BREAST';
			$level4['name'] = 'BREAST EXAM';
			$level4['active'] = 1;

			$level5['key'] = 'EXAM_CHEST';
			$level5['name'] = 'CHEST EXAM';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateVitalUnitsEnum($force = false) {
		self::generateVitalUnitHeightEnum();
		self::generateVitalUnitWeightEnum();
		self::generateVitalUnitTemperatureEnum();
	}

	public static function generateVitalUnitHeightEnum($force = false) {
		$ret = false;
		do {
			$name = 'Height';
			$key = 'Height';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'IN';
			$level1['name'] = 'IN';
			$level1['active'] = 1;

			$level2['key'] = 'CM';
			$level2['name'] = 'CM';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateVitalUnitWeightEnum($force = false) {
		$ret = false;
		do {
			$name = 'Weight';
			$key = 'Weight';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'LB';
			$level1['name'] = 'LB';
			$level1['active'] = 1;

			$level2['key'] = 'KG';
			$level2['name'] = 'KG';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateVitalUnitTemperatureEnum($force = false) {
		$ret = false;
		do {
			$name = 'Temperature';
			$key = 'Temper';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key && !$force) {
				break;
			}

			$level1 = array();
			$level1['key'] = 'F';
			$level1['name'] = 'F';
			$level1['active'] = 1;

			$level2['key'] = 'C';
			$level2['name'] = 'C';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
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

	public static function enumerationToJson($name) {
		$enumeration = new self();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		return $enumerationIterator->toJsonArray('enumerationId',array('name'));
	}

}
