<?php
/*****************************************************************************
*       OrderIterator.php
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


class OrderIterator extends WebVista_Model_ORMIterator implements Iterator {

	public function __construct($dbSelect = null) {
		parent::__construct("Order",$dbSelect);
	}	

	public function current() {
		$ormObj = new $this->_ormClass();
		$row = $this->_dbStmt->fetch(null,null,$this->_offset);
		$ormObj->populateWithArray($row);
		$ormObj->provider->populateWithArray($row);
		return $ormObj;
	}

	public function setFilter($filter) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from('orders');

		$currentDate = date('Y-m-d');
		switch ($filter) {
			case 'current_orders':
				$dbSelect->orWhere('status = ?','Active');
				$dbSelect->orWhere('status = ?','Pending');
				break;
			case 'expiring_orders':
				// currently set to 1 week for expiring orders
				$nextDate = date('Y-m-d',strtotime('+1week',strtotime($currentDate)));
				$dbSelect->where("dateStop BETWEEN '{$currentDate}' AND '{$nextDate}'");
				break;
			case 'unsigned_orders':
				$dbSelect->where('eSignatureId = ?',0);
				break;
			case 'recently_expired_orders':
				$dbSelect->where("dateStop LIKE '{$currentDate}%'");
				break;
			default:
				$dbSelect->orWhere('status = ?','Active');
				$dbSelect->orWhere('status = ?','Pending');
				$dbSelect->orWhere("dateStart LIKE '{$currentDate}%'");
				break;
		}

		trigger_error($dbSelect->__toString(),E_USER_NOTICE);
		$this->_dbSelect = $dbSelect;
		$this->_dbStmt = $db->query($this->_dbSelect);
	}

}
