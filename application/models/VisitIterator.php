<?php
/*****************************************************************************
*       VisitIterator.php
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


class VisitIterator extends WebVista_Model_ORMIterator implements Iterator {

	public function __construct($dbSelect = null) {
		parent::__construct("Visit",$dbSelect);
	}

	function setFilters($filters) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from('encounter',array('*','DATE_FORMAT(dateOfService,"%Y-%m-%d") AS dateOfService'));
		$dbSelect->join('person', 'person.person_id = encounter.treating_person_id',array('concat(person.last_name, ", ", person.first_name, " ", person.middle_name) as providerDisplayName'));
		$dbSelect->joinLeft('buildings', 'encounter.building_id = buildings.id', array('buildings.name as locationName'));
		$dbSelect->where("patient_id = ?", $filters['patientId']);
		$dbSelect->order('dateOfService DESC');
		//$dbSelect->where("building_id = ?", $filters['locationId']);
		//trigger_error($dbSelect->__toString(),E_USER_NOTICE);
		$this->_dbSelect = $dbSelect;
		$this->_dbStmt = $db->query($this->_dbSelect);
	}

}
