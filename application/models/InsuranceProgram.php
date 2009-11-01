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
			       ->order('c.name');
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
			       ->where('company_id = ?',(int)$companyId);
		return $this->getIterator($dbSelect);
	}

}
