<?php
/*****************************************************************************
*       VitalSignGroup.php
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


class VitalSignGroup extends WebVista_Model_ORM implements NSDRMethods {
	protected $vitalSignGroupId;
	protected $personId;
	protected $dateTime;
	protected $enteringUserId;
	protected $visitId;
	protected $vitalSignTemplateId;
	protected $vitalSignValues = array();
	protected $_primaryKeys = array('vitalSignGroupId');
	protected $_table = "vitalSignGroups";
	
	function __construct($context = '') {
		parent::__construct();
	}

	function setVitalSignValues(array $vitalSigns) {
		foreach($vitalSigns as $vitalSignData) {
			$vitalSignValue = new VitalSignValue();
			$vitalSignValue->populateWithArray($vitalSignData);
			$this->vitalSignValues[] = $vitalSignValue;
		}
	}
	function getVitalSignValues() {
		return $this->vitalSignValues;
	}
	
	function setVitalSignGroupId($id) {
		$this->vitalSignGroupId = (int)$id;
		foreach ($this->vitalSignValues as $vitalSign) {
			$vitalSign->vitalSignGroupId = (int)$id;
		}
	}

	static public function getBMIVitalsForPatientId($personId) {
		$personId = (int)$personId;
		$db = Zend_Registry::get('dbAdapter');
		$vitalSelect = $db->select()
			->from('vitalSignValues', array('vital','value','units'))
			->where('vitalSignValues.vitalSignValueId = (
				select vitalSignValueId from vitalSignGroups
				inner join vitalSignValues on vitalSignValues.vitalSignGroupId = vitalSignGroups.vitalSignGroupId
				where vitalSignGroups.personId = ' . $personId . '	
				and vital = "height"
				and value != ""
				order by dateTime DESC
				limit 1)')
			->orWhere('vitalSignValues.vitalSignValueId = (
				select vitalSignValueId from vitalSignGroups
				inner join vitalSignValues on vitalSignValues.vitalSignGroupId = vitalSignGroups.vitalSignGroupId
				where vitalSignGroups.personId = ' . (int)$personId . '
				and vital = "weight"
				and value != ""
				order by dateTime DESC
				limit 1)');
		return $db->query($vitalSelect)->fetchAll();
	}

	public function nsdrPersist($tthis,$context,$data) {
		if ((int)$context > 0) {
			$this->vitalSignsGroupId = $context;
			if (!$this->populate()) {
				$msg = __('populate failed with supplied non-zero context');
				throw new Exception($msg);
			}
		}
		$this->populateArray($data);
		if (!$this->persist()) {
			$msg = __('persist failed');
			throw new Exception($msg);
		}
		return true;
	}

	public function nsdrPopulate($tthis,$context,$data) {
		$ret = array();
		if ($context == '*') {
			if (!class_exists('VitalSignGroupIterator')) {
				$msg = __('* context not supported by nsdrPopulate method');
				throw new Exception($msg);
			}
			// TODO: Use VitalSignGroupIterator to populates data
		}
		else {
			if ((int)$context > 0) {
				$this->vitalSignsGroupId = $context;
				if (!$this->populate()) {
					//throw error, populate failed with supplied non-zero context
					$msg = __('populate failed with supplied non-zero context');
					throw new Exception($msg);
				}
				$ret[] = $this->toArray();
			}
		}
		return $ret;
	}

	public function nsdrMostRecent($tthis,$context,$data) {
		$ret = array();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from('vitalSignGroups')
			       ->order('dateTime DESC')
			       ->limit(1);
		if (is_array($context) && isset($context['personId'])) {
			$dbSelect->where("personId = ?",(int)$context['personId']);
		}
		else if (isset($data['personId'])) {
			$dbSelect->where("personId = ?",(int)$data['personId']);
		}
		else {
			if (is_array($context) && isset($context['*']['filters'])) {
				list($k,$v) = each($context['*']['filters']);
				$context = $k;
			}
			$dbSelect->where("personId = ?",(int)$context);
		}
		$dbJoinSelect = $db->select()
				   ->from(array('vsg'=>$dbSelect))
				   ->joinLeft(array('vsv'=>'vitalSignValues'),"vsg.vitalSignGroupId = vsv.vitalSignGroupId");

		if ($rows = $db->fetchAll($dbJoinSelect)) {
			foreach ($rows as $row) {
				$ret[] = $row['vital'] . ': ' . $row['value'] . ' ' . $row['units'];
			}
		}
		return $ret;
	}

	static public function getMostRecentVitalsForPatientId($personId) {
		$personId = (int)$personId;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('vitalSignGroups')
				->joinUsing('vitalSignValues','vitalSignGroupId')
				->joinLeft('user','user.user_id = vitalSignGroups.enteringUserId')
				->joinLeft('person','person.person_id = user.person_id')
				->where('vitalSignGroups.personId = ' . (int)$personId)
				->where("vitalSignGroups.vitalSignGroupId = (select vitalSignGroups.vitalSignGroupId from vitalSignGroups where personId = " . (int)$personId . " order by vitalSignGroups.dateTime DESC limit 1)");
		return $db->query($sqlSelect)->fetchAll();
	}

}
