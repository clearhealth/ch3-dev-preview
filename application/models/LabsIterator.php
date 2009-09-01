<?php
/*****************************************************************************
*       LabsIterator.php
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


class LabsIterator extends WebVista_Model_ORMIterator implements Iterator {

	public function __construct($dbSelect = null) {
		if ($dbSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$dbSelect = $db->select()
				       ->from('lab_result')
				       ->joinLeft('lab_test','lab_test.lab_test_id=lab_result.lab_test_id')
				       ->joinLeft('lab_order','lab_order.lab_order_id=lab_test.lab_order_id')
				       ->order('lab_result.observation_time DESC');
			trigger_error($dbSelect->__toString(),E_USER_NOTICE);
		}
		parent::__construct("LabResult",$dbSelect);
	}

	public function current() {
		$ormObj = new $this->_ormClass();
		$row = $this->_dbStmt->fetch(null,null,$this->_offset);
		$ormObj->populateWithArray($row);
		if (isset($row['lt_observation_time'])) {
			// conflicts in observation_time
			$row['observation_time'] = $row['lt_observation_time'];
		}
		$ormObj->labTest->populateWithArray($row);
		$ormObj->labTest->labOrder->populateWithArray($row);
		return $ormObj;
	}

	public function setFilters($filters) {
		$db = Zend_Registry::get('dbAdapter');
		$labTestCols = array();
		$labTestCols[] = 'lab_test_id';
		$labTestCols[] = 'lab_order_id';
		$labTestCols[] = 'order_num';
		$labTestCols[] = 'filer_order_num';
		$labTestCols[] = 'specimen_received_time';
		$labTestCols[] = 'report_time';
		$labTestCols[] = 'ordering_provider';
		$labTestCols[] = 'service';
		$labTestCols[] = 'component_code';
		$labTestCols[] = 'status';
		$labTestCols[] = 'clia_disclosure';
		// observation_time is conflict with lab_result
		$labTestCols['lt_observation_time'] = 'observation_time';
		$dbSelect = $db->select()
			       ->from('lab_result')
			       ->joinLeft('lab_test','lab_test.lab_test_id=lab_result.lab_test_id',$labTestCols)
			       ->joinLeft('lab_order','lab_order.lab_order_id=lab_test.lab_order_id')
			       ->where('lab_order.patient_id = ?',$filters['patientId'])
			       ->order('lab_result.observation_time DESC');
		if (strtotime($filters['dateEnd']) > 100000 && $filters['dateEnd'] != '*') {
			$dateBegin = date('Y-m-d H:i:s',strtotime($filters['dateBegin']));
			if ($filters['dateBegin'] == $filters['dateEnd']) {
				// date range are the same
				$dateEnd = date('Y-m-d H:i:s',strtotime("+1 day",strtotime($filters['dateEnd'])));
			}
			else {
				$dateEnd = date('Y-m-d H:i:s',strtotime($filters['dateEnd']));
			}
			$dbSelect->where("lab_result.observation_time BETWEEN '{$dateBegin}' AND '{$dateEnd}'");
		}
		if ((int)$filters['limit'] > 0) {
			$dbSelect->limit((int)$filters['limit']);
		}
		trigger_error($dbSelect,E_USER_WARNING);
		$this->_dbSelect = $dbSelect;
		$this->_dbStmt = $db->query($this->_dbSelect);
	}

}
