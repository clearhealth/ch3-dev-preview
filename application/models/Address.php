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
	protected $_table = "address";
	protected $_primaryKeys = array('address_id');
	protected $_legacyORMNaming = true;
	
	public function __construct() {
		parent::__construct();
	}

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
		$row = $db->query($sql)->fetchAll();
		return $row[0]['value'];
	}

}
