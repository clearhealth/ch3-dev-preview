<?php
/*****************************************************************************
*       FacilityIterator.php
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


class FacilityIterator extends WebVista_Model_ORMIterator implements Iterator {

	protected $_filters = array();
	protected $_classes = array();
	protected $_columnMeta = array();
	public $currentPractice;
	public $currentBuilding;
	public $currentRoom;

	public function __construct() {

	}

	public function setFilter($filters) {
		if (empty($filters)) {
			throw new Exception(__("Filter must not be empty"));
		}
		$this->_classes = array();
		$this->_filters = array();
		foreach ($filters as $filter) {
			if (!class_exists($filter)) {
				$msg = __("Filter {$filter} does not exists");
				throw new Exception($msg);
			}
			$class = new $filter();
			if (!$class instanceof WebVista_Model_ORM) {
				$msg = __("Filter {$filter} is not an instance of WebVista_Model_ORM");
				throw new Exception($msg);
			}
			$this->_classes[] = $class;
			$this->_filters[] = $filter;
		}
		$db = Zend_Registry::get('dbAdapter');
                $dbSelect = $db->select()->from($this->_classes[0]->_table);
		if (in_array("Practice",$this->_filters) && in_array("Building",$this->_filters) ) {
			$dbSelect->join("buildings","buildings.practice_id = practices.id");
		}
		if (in_array("Building",$this->_filters) && in_array("Room",$this->_filters) ) {
			$dbSelect->join("rooms","rooms.building_id = buildings.id");
		}

		$this->_dbSelect = $dbSelect;
                $this->_dbStmt = $db->query($this->_dbSelect);

		return $this;
	}
	public function current() {
		if (count($this->_classes) == 1) {
                	$row = $this->_dbStmt->fetch(null,null,$this->_offset);
                	$ormObj = new $this->_classes[0]();
                	$ormObj->populateWithArray($row);
			return $ormObj;
		}

                $row = $this->_dbStmt->fetch(PDO::FETCH_NUM,null,$this->_offset);
		if ($this->_offset == 0) {
			for($i=0;$i<count($row);$i++) {	$this->_columnMeta[$i] = $this->_dbStmt->getColumnMeta($i); }
		}
		$Practice = new Practice();
		$Building = new Building();
		$Room = new Room();
		$returnArray = array();
		$col = 0;
		foreach ($this->_filters as $filter) {
			$data = array();
			while ($col < count($this->_columnMeta)  && $this->_columnMeta[$col]['table'] == $$filter->_table) {
				$data[$this->_columnMeta[$col]['name']] = $row[$col];
				$col++;
			}
			$$filter->populateWithArray($data);
			$returnArray[$filter] = $$filter;
		}
                return $returnArray;
        }

}
