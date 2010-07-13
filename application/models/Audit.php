<?php
/*****************************************************************************
*       Audit.php
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


class Audit extends WebVista_Model_ORM {
	protected $auditId;
	protected $objectClass;
	protected $objectId;
	protected $userId;
	protected $type;
	protected $message;
	protected $dateTime;
	protected $startProcessing;
	protected $endProcessing;
	protected $ipAddress;

	protected $_table = 'audits';
	protected $_primaryKeys = array('auditId');
	protected $_persistMode = WebVista_Model_ORM::INSERT;
	protected $_ormPersist = false;

	public function persist() {
		if (!strlen($this->ipAddress) > 0 && isset($_SERVER['REMOTE_ADDR'])) {
			$this->ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		if ($this->shouldAudit()) {
			$sql = $this->toSQL();
			AuditLog::appendSql($sql);
		}
		if ($this->_ormPersist) {
			parent::persist();
		}
	}

	public function getIteratorByCurrentDate() {
		$currentDate = date('Y-m-d');
		$db = Zend_Registry::get("dbAdapter");
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where('dateTime LIKE ?',$currentDate.'%')
			       ->order('dateTime');
		return $this->getIterator($dbSelect);
	}
}
