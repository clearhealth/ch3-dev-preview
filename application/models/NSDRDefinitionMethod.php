<?php
/*****************************************************************************
*       NSDRDefinitionMethod.php
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


/**
 * WebVista_Model_ORM
 */
require_once 'WebVista/Model/ORM.php';

/**
 * Zend_Registry
 */
require_once 'Zend/Registry.php';

class NSDRDefinitionMethod extends WebVista_Model_ORM {

	protected $uuid;
	protected $nsdrDefinitionUuid;
	protected $methodName;
	protected $method;
	protected $_table = "nsdrDefinitionMethods";
	protected $_primaryKeys = array("uuid");

	// overrides parent populate due to key problem
	public function populate() {
		return $this->populateBy('uuid',$this->uuid);
	}

	public function populateByNamespace($namespace) {
		return $this->populateBy('namespace',$namespace);
	}

	public function populateBy($field,$value) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where("`{$field}` = ?",$value);
		$retval = $this->populateWithSql($dbSelect->__toString());
		$this->postPopulate();
		return $retval;
	}

	public function getIteratorByParentId($id) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where('`nsdrDefinitionUuid` = ?',$id);
		return parent::getIterator($dbSelect);
	}
}
