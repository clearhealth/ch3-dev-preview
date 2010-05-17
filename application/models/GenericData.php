<?php
/*****************************************************************************
*       GenericData.php
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


class GenericData extends WebVista_Model_ORM implements NSDRMethods {
	protected $genericDataId;
	protected $objectClass;
	protected $objectId;
	protected $dateTime;
	protected $name;
	protected $value;
	protected $revisionId;

	protected $_table = "genericData";
	protected $_primaryKeys = array("genericDataId");

	public function getIteratorByFilters($filters) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->group('revisionId')
				->order('revisionId DESC')
				->order('dateTime DESC');
		foreach ($filters as $name=>$value) {
			$sqlSelect->where($name.' = ?',$value);
		}
		return $this->getIterator($sqlSelect);
	}

	public function doesRowExist($autoPopulate = false) {
		$ret = false;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('objectClass = ?',$this->objectClass)
				->where('objectId = ?',$this->objectId)
				->where('`name` = ?',$this->name)
				->order('revisionId DESC')
				->limit(1);
		if ($row = $db->fetchRow($sqlSelect)) {
			if ($autoPopulate) {
				$this->populateWithArray($row);
			}
			$ret = true;
		}
		return $ret;
	}

	public function loadValue() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('objectClass = ?',$this->objectClass)
				->where('objectId = ?',$this->objectId)
				->where('`name` = ?',$this->name)
				->where('revisionId = ?',(int)$this->revisionId);
		return $this->populateWithSql($sqlSelect->__toString());
	}

	public function deleteByRevisionId($revisionId = null) {
		if ($revisionId === null) {
			$revisionId = $this->revisionId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$db->delete($this->_table,'revisionId = '.(int)$revisionId);
	}

	public static function createRevision($objectClass,$objectId,$revisionId=0) {
		$db = Zend_Registry::get('dbAdapter');
		$gd = new self();
		if ($revisionId <= 0) {
			$revisionId = self::getUnsignedRevisionId($objectClass,$objectId);
		}
		$sqlSelect = $db->select()
				->from($gd->_table)
				->where('objectClass = ?',$objectClass)
				->where('objectId = ?',$objectId)
				->where('revisionId = ?',$revisionId);
		//trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		if ($rows = $db->fetchAll($sqlSelect)) {
			$newRevisionId = WebVista_Model_ORM::nextSequenceId();
			foreach ($rows as $row) {
				$gd = new self();
				$gd->populateWithArray($row);
				$gd->genericDataId = 0;
				$gd->revisionId = $newRevisionId;
				//trigger_error(print_r($gd->toArray(),true),E_USER_NOTICE);
				$gd->persist();
			}
		}
	}

	public static function getMostRecentRevisionId($objectClass,$objectId) {
		$db = Zend_Registry::get('dbAdapter');
		$gd = new self();
		$sqlSelect = $db->select()
				->from($gd->_table,array('revisionId'))
				->where('objectClass = ?',$objectClass)
				->where('objectId = ?',$objectId)
				->order('revisionId DESC')
				->limit(1);
		//trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		$revisionId = 0;
		if ($row = $db->fetchRow($sqlSelect)) {
			$revisionId = $row['revisionId'];
		}
		//trigger_error('recentRevisionId:'.$revisionId,E_USER_NOTICE);
		return $revisionId;
	}

	public static function getUnsignedRevisionId($objectClass,$objectId) {
		$db = Zend_Registry::get('dbAdapter');
		$gd = new self();
		$esig = new ESignature();
		$sqlSelect = $db->select()
				->from(array('gd'=>$gd->_table),array('gd.revisionId AS revisionId'))
				->join(array('esig'=>$esig->_table),'gd.revisionId = esig.objectId')
				->where('gd.objectClass = ?',$objectClass)
				->where('gd.objectId = ?',(int)$objectId)
				->where("esig.signature = ''")
				->limit(1);
		//trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		if ($row = $db->fetchRow($sqlSelect)) {
			$revisionId = $row['revisionId'];
		}
		else {
			$revisionId = self::getMostRecentRevisionId($objectClass,$objectId);
		}
		//trigger_error('unsignedRevisionId:'.$revisionId,E_USER_NOTICE);
		return $revisionId;
	}

	public static function getObjectIdByRevisionId($revisionId) {
		$db = Zend_Registry::get('dbAdapter');
		$gd = new self();
		$sqlSelect = $db->select()
				->from($gd->_table,array('objectId'))
				->where('revisionId = ?',(int)$revisionId)
				->limit(1);
		//trigger_error($sqlSelect->__toString(),E_USER_NOTICE);
		$objectId = 0;
		if ($row = $db->fetchRow($sqlSelect)) {
			$objectId = $row['objectId'];
		}
		//trigger_error('objectId:'.$objectId,E_USER_NOTICE);
		return $objectId;
	}

	public static function getAllRevisions($objectClass,$objectId) {
		$db = Zend_Registry::get('dbAdapter');
		$gd = new self();
		$esig = new ESignature();
		$sqlSelect = $db->select()
				->from(array('gd'=>$gd->_table))
				->join(array('esig'=>$esig->_table),'gd.revisionId = esig.objectId',array())
				->where('gd.objectClass = ?',$objectClass)
				->where('gd.objectId = ?',(int)$objectId)
				->where("esig.signature != ''")
				->group('gd.revisionId');
		return $gd->getIterator($sqlSelect);
	}

	public function nsdrPersist($tthis,$context,$data) {
		$context = (int)$context;
		$attributes = $tthis->_attributes;
		$nsdrNamespace = $tthis->_nsdrNamespace;
		$aliasedNamespace = $tthis->_aliasedNamespace;
		if ($context == '*') {
			if (isset($attributes['isDefaultContext']) && $attributes['isDefaultContext']) { // get genericData
				$objectClass = 'ClinicalNote';
				$clinicalNoteId = 0;
				if (isset($attributes['clinicalNoteId'])) {
					$clinicalNoteId = (int)$attributes['clinicalNoteId'];
				}
				$revisionId = 0;
				if (isset($attributes['revisionId'])) {
					$revisionId = (int)$attributes['revisionId'];
				}
				if (!$revisionId > 0) {
					$revisionId = GenericData::getUnsignedRevisionId($objectClass,$clinicalNoteId);
				}
				$gd = new self();
				$gd->objectClass = $objectClass;
				$gd->objectId = $clinicalNoteId;
				$gd->name = preg_replace('/[-\.]/','_',$nsdrNamespace);
				$gd->revisionId = $revisionId;
				$gd->loadValue();

				$gd->dateTime = date('Y-m-d H:i:s');
				if (is_array($data)) {
					$data = array_shift($data);
				}
				$gd->value = $data;
				return $gd->persist();
			}
			else { // all
				$ret = false;
				if (isset($data[0])) {
					$ret = true;
					foreach ($data as $row) {
						$gd = new self();
						$gd->populateWithArray($row);
						$gd->persist();
					}
				}
				return $ret;
			}
		}
		$gd = new self();
		$gd->genericDataId = $context;
		$gd->populate();
		$gd->populateWithArray($data);
		return $gd->persist();
	}

	public function nsdrPopulate($tthis,$context,$data) {
		$context = (int)$context;
		$attributes = $tthis->_attributes;
		$nsdrNamespace = $tthis->_nsdrNamespace;
		$aliasedNamespace = $tthis->_aliasedNamespace;
		if ($context == '*') {
			if (isset($attributes['isDefaultContext']) && $attributes['isDefaultContext']) { // get genericData
				$objectClass = 'ClinicalNote';
				$clinicalNoteId = 0;
				if (isset($attributes['clinicalNoteId'])) {
					$clinicalNoteId = (int)$attributes['clinicalNoteId'];
				}
				$revisionId = 0;
				if (isset($attributes['revisionId'])) {
					$revisionId = (int)$attributes['revisionId'];
				}
				if (!$revisionId > 0) {
					$revisionId = GenericData::getUnsignedRevisionId($objectClass,$clinicalNoteId);
				}
				$gd = new self();
				$gd->objectClass = $objectClass;
				$gd->objectId = $clinicalNoteId;
				$gd->name = preg_replace('/[-\.]/','_',$nsdrNamespace);
				$gd->revisionId = $revisionId;
				$gd->loadValue();
				return $gd->value;
			}
			else { // all
				$ret = array();
				$gd = new self();
				$gdIterator = $gd->getIterator();
				foreach ($gdIterator as $g) {
					$ret[] = $g->toArray();
				}
				return $ret;
			}
		}
		$gd = new self();
		$gd->genericDataId = $context;
		$gd->populate();
		return $gd->toArray();
	}

	public function nsdrMostRecent($tthis,$context,$data) {
	}

}
