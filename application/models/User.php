<?php
/*****************************************************************************
*       User.php
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


class User extends WebVista_Model_ORM {
	protected $user_id;
	protected $person_id;
	protected $person;
	protected $username;
	protected $password;
	protected $default_location_id;

	protected $_table = "user";
	protected $_primaryKeys = array("user_id");
	protected $_legacyORMNaming = true;

	public function __construct() {
		parent::__construct();
		$this->person = new Person();
	}

	public function populateWithUsername($username = null) {
		if ($username === null) {
			$username = $this->username;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sql = "SELECT * from " . $this->_table . " WHERE 1 "
		  . " and username = " . $db->quote($username);
                $this->populateWithSql($sql);
		$this->person->person_id = $this->person_id;
		$this->person->populate();
	}

	public function populateWithPersonId($personId = null) {
		if ($personId === null) {
			$personId = $this->person_id;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sql = "SELECT * from " . $this->_table . " WHERE 1 "
		  . " and person_id = " . $db->quote($personId);
                $this->populateWithSql($sql);
		$this->person->person_id = $this->person_id;
		$this->person->populate();
	}

	public function __get($key) {
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

}
