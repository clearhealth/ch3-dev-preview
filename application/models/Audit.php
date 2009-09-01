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
	protected $_table = "audits";
	protected $_primaryKeys = array('auditId');
	protected $_persistMode = WebVista_Model_ORM::INSERT;

	public function persist() {
		if ($this->shouldAudit()) {
			$sql = $this->toSQL();
			AuditLog::appendSql($sql);
		}
	}
}
