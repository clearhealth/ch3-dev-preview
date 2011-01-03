<?php
/*****************************************************************************
*       InsuranceProgram.php
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


class InsuranceProgram extends WebVista_Model_ORM {

	protected $insurance_program_id;
	protected $payer_type;
	protected $company_id;
	protected $company;
	protected $name;
	protected $fee_schedule_id;
	protected $x12_sender_id;
	protected $x12_receiver_id;
	protected $x12_version;
	protected $address_id;
	protected $address;
	protected $funds_source;
	protected $program_type;
	protected $payer_identifier;

	protected $_table = 'insurance_program';
	protected $_primaryKeys = array('insurance_program_id');
	protected $_legacyORMNaming = true;
	protected $_cascadePersist = false;

	const INSURANCE_ENUM_NAME = 'Insurance Preferences';
	const INSURANCE_ENUM_KEY = 'INSPREF';
	const INSURANCE_ASSIGNING_ENUM_NAME = 'Assigning';
	const INSURANCE_ASSIGNING_ENUM_KEY = 'ASSIGNING';
	const INSURANCE_SUBSCRIBER_ENUM_NAME = 'Subscriber';
	const INSURANCE_SUBSCRIBER_ENUM_KEY = 'SUBSCRIBER';
	const ENUM_INSURANCE_PREFERENCES = 'Insurance Preferences';
	const ENUM_PROGRAM_PREFERENCES = 'Program Preferences';
	const ENUM_PROGRAM_TYPES = 'Program Types';

	public function __construct() {
		parent::__construct();
		$this->company = new Company();
		$this->company->_cascadePersist = $this->_cascadePersist;
		$this->address = new Address();
		$this->address->_cascadePersist = $this->_cascadePersist;
	}

	public static function getInsurancePrograms() {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from(array('ip'=>'insurance_program'),array('insurance_program_id','name'))
			       ->join(array('c'=>'company'),'c.company_id = ip.company_id',array('company_name'=>'name'))
			       ->order('c.name')
			       ->order('ip.name');
		$insurancePrograms = array();
		foreach ($db->fetchAll($dbSelect) as $row) {
			$insurancePrograms[$row['insurance_program_id']] = $row['company_name'].'->'.$row['name'];
		}
		return $insurancePrograms;
	}

	public function getIteratorByCompanyId($companyId = null) {
		if ($companyId === null) {
			$companyId = $this->companyId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where('company_id = ?',(int)$companyId)
			       ->order('name');
		return $this->getIterator($dbSelect);
	}

	public static function getListProgramTypes() {
		$enumeration = new Enumeration();
		$enumeration->populateByUniqueName(self::ENUM_INSURANCE_PREFERENCES);

		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enum) {
			if ($enum->name != self::ENUM_PROGRAM_PREFERENCES) continue;
			$enumPRIterator = $enumerationsClosure->getAllDescendants($enum->enumerationId,1);
			foreach ($enumPRIterator as $prog) {
				if ($prog->name != self::ENUM_PROGRAM_TYPES) continue;
				$enumPTIterator = $enumerationsClosure->getAllDescendants($prog->enumerationId,1);
                		$ret = $enumPTIterator->toArray('enumerationId','name');
				break;
			}
			break;
		}
		return $ret;
	}

	public static function getInsuranceProgram($insuranceProgramId) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('ip'=>'insurance_program'),array('insurance_program_id','name'))
				->join(array('c'=>'company'),'c.company_id = ip.company_id',array('company_name'=>'name'))
				->where('ip.insurance_program_id = ?',(int)$insuranceProgramId)
				->order('c.name')
				->order('ip.name');
		$insuranceProgram = '';
		if ($row = $db->fetchRow($sqlSelect)) {
			$insuranceProgram = $row['company_name'].'->'.$row['name'];
		}
		return $insuranceProgram;
	}

	public static function getInsuranceProgramsByIds($ids) {
		$x = explode(',',$ids);
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('ip'=>'insurance_program'),array('insurance_program_id','name'))
				->join(array('c'=>'company'),'c.company_id = ip.company_id',array('company_name'=>'name'))
				->order('c.name')
				->order('ip.name');
		foreach ($x as $id) {
			$sqlSelect->orWhere('ip.insurance_program_id = ?',(int)$id);
		}
		$insurancePrograms = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$insurancePrograms[$row['insurance_program_id']] = $row['company_name'].'->'.$row['name'];
			}
		}
		return $insurancePrograms;
	}

}
