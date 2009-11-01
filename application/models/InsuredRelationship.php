<?php
/*****************************************************************************
*       InsuredRelationship.php
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


class InsuredRelationship extends WebVista_Model_ORM {

	protected $insured_relationship_id;
	protected $insurance_program_id;
	protected $insuranceProgram;
	protected $person_id;
	protected $person;
	protected $subscriber_id;
	protected $subscriber_to_patient_relationship;
	protected $copay;
	protected $assigning;
	protected $group_name;
	protected $group_number;
	protected $default_provider;
	protected $program_order;
	protected $effective_start;
	protected $effective_end;
	protected $active;

	protected $_table = 'insured_relationship';
	protected $_primaryKeys = array('insured_relationship_id');
	protected $_legacyORMNaming = true;
	protected $_cascadePersist = false;

	public function __construct() {
		parent::__construct();
		$this->insuranceProgram = new InsuranceProgram();
		$this->insuranceProgram->_cascadePersist = $this->_cascadePersist;
		$this->person = new Person();
		$this->person->_cascadePersist = $this->_cascadePersist;
	}

	public function getIteratorByPersonId($personId = null) {
		if ($personId === null) {
			$personId = $this->personId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where('person_id = ?',(int)$personId);
		return $this->getIterator($dbSelect);
	}

}
