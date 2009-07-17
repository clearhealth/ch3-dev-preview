<?php
/*****************************************************************************
*	FacilityIterator.php
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


class FacilityIterator extends WebVista_Model_ORMIterator implements Iterator {

	protected $_filters = array();
	protected $_tmpData = array();
	protected $_data = array();
	protected $_classes = array();

	public function __construct() {
	}

	public function setFilter($filters) {
		if (empty($filters)) {
			throw new Exception(__("Filter must not be empty"));
		}
		$this->_classes = array();
		$this->_filters = array();
		$ctr = 0;
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
			$this->_classes[$ctr] = $class;
			$this->_filters[$ctr] = $filter;
			$ctr++;
		}

		$this->_tmpData = array();
		$this->_data = array();

                $db = Zend_Registry::get('dbAdapter');
		$ctr = count($this->_classes);
		if ($ctr >= 1) {
			$i = 0;
			$this->populateData($i);
			$this->_data[] = $this->_tmpData;
		}
		return $this;
	}

	protected function populateData($ctr) {
		if (!isset($this->_classes[$ctr])) {
			return;
		}
                $db = Zend_Registry::get('dbAdapter');
		$class = $this->_classes[$ctr];

		$dbSelect = $db->select();
		$dbSelect->from($class->_table);

		$previousCtr = $ctr - 1;
		if (isset($this->_classes[$previousCtr])) {
			// check if reference id exists
			$previousClass = $this->_classes[$previousCtr];
			$previousClassName = lcfirst($this->_filters[$previousCtr]);

			$tmpData = $this->_tmpData;
			while ($data = array_pop($tmpData)) {
				if (!$data instanceof $this->_filters[$ctr]) {
					if ($data instanceof $this->_filters[$previousCtr]) {
						$id = $data->_primaryKeys[0];
						$parentId = $data->$id;
						break;
					}
				}
			}
			// return if parent id does not exists
			if (!isset($parentId)) {
				return;
			}

			$reflectionClass = new ReflectionClass($this->_filters[$ctr]);
			// check the camelCase first
			$properties = $reflectionClass->getDefaultProperties();
			$suffix = '';
			if (array_key_exists($previousClassName . 'Id',$properties)) {
				$suffix = 'Id';
			}
			else if (array_key_exists($previousClassName . '_id',$properties)) {
				$suffix = '_id';
			}
			else {
				// do not include the next filter if don't have relationship with the current filter 
				return;
			}
			$dbSelect->where($previousClassName . $suffix . ' = ?',$parentId);
			unset($parentId);
		}

		if ($rowset = $db->fetchAll($dbSelect)) {
			$ctr++;
			foreach ($rowset as $row) {
				$tmp = clone $class;
				$tmp->populateWithArray($row);
				$this->_tmpData[] = $tmp;
				$this->populateData($ctr);
			}
		}
	}

	public function rewind() {
		$this->_offset = 0;
		return $this;
	}

	public function valid() {
		return isset($this->_data[$this->_offset]);

		if ($this->_offset) {
			return false;
		}
		return true;
	}

	public function key() {
		return $this->_offset;
	}

	public function current() {
		return $this->_data[$this->_offset];
	}

	public function seek(int $offset) {
		$this->_offset = $offset;
		return $this;
	}

	public function next() {
		$this->_offset++;
	}

}
