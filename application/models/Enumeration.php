<?php
/*****************************************************************************
*	Enumeration.php
*
*	Author:  ClearHealth Inc. (www.clear-health.com)	2009
*	
*	ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*	respective logos, icons, and terms are registered trademarks 
*	of ClearHealth Inc.
*
*	Though this software is open source you MAY NOT use our 
*	trademarks, graphics, logos and icons without explicit permission. 
*	Derivitive works MUST NOT be primarily identified using our 
*	trademarks, though statements such as "Based on ClearHealth(TM) 
*	Technology" or "incoporating ClearHealth(TM) source code" 
*	are permissible.
*
*	This file is licensed under the GPL V3, you can find
*	a copy of that license by visiting:
*	http://www.fsf.org/licensing/licenses/gpl.html
*	
*****************************************************************************/


class Enumeration extends WebVista_Model_ORM {

	protected $enumerationId;
	protected $uuid;
	protected $name;
	protected $key;
	protected $active;
	protected $type;
	protected $parentId;
	protected $lft;
	protected $rgt;

	protected $_table = "enumerations";
	protected $_primaryKeys = array('enumerationId');

	static public function getIterByEnumerationId($enumerationId) {
		$enumerationId = (int) $enumerationId;
		$enumeration = new Enumeration();
		$db = Zend_Registry::get('dbAdapter');
		$enumSelect = $db->select()
			->from($enumeration->_table)
			->where('parentId = ' . $enumerationId);
		$iter = $enumeration->getIterator($enumSelect);
		return $iter;
		
	}

	static public function getIterByEnumerationName($name) {
                $enumeration = new Enumeration();
                $db = Zend_Registry::get('dbAdapter');
                $enumSelect = $db->select()
                        ->from($enumeration->_table)
                        ->where('parentId = (select enumerationId from enumerations where name=' . $db->quote($name) .')');
                $iter = $enumeration->getIterator($enumSelect);
                return $iter;

        }
	static public function getEnumArray($name,$key = "key", $value = "name") {
                $iter = Enumeration::getIterByEnumerationName($name);
                return $iter->toArray($key, $value);

        }
}
