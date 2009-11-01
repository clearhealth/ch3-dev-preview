<?php
/*****************************************************************************
*       Visit.php
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


class Visit extends WebVista_Model_ORM {
	protected $encounter_id;
	protected $encounter_reason;
	protected $patient_id;
	protected $building_id;
	protected $date_of_treatment;
	protected $treating_person_id;
	protected $timestamp;
	protected $last_change_user_id;
	protected $status;
	protected $occurence_id;
	protected $created_by_user_id;
	protected $payer_group_id;
	protected $current_payer;
	protected $room_id;
	protected $practice_id;
	protected $_providerDisplayName = ''; //placeholder for use in visit list iterator
	protected $_locationName = ''; //placeholder for use in visit list iterator
	protected $_table = "encounter";
	protected $_primaryKeys = array("encounter_id");

	function getIterator($objSelect = null) {
		return new VisitIterator($objSelect);
	}
	function setLocationName($locationName) {
		$this->_locationName = $locationName;
	}
	function getLocationName() {
		return $this->_locationName;
	}
	function setProviderDisplayName($providerDisplayName) {
		$this->_providerDisplayName = $providerDisplayName;
	}
	function getProviderDisplayName() {
		$provider = new Provider();
		$provider->person_id = $this->treating_person_id;
		$provider->populate();
		return $provider->person->getDisplayName();
	}

}
