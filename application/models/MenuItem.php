<?php
/*****************************************************************************
*	MenuItem.php
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


class MenuItem extends WebVista_Model_ORM {
    protected $menuId;
    protected $siteSection;
    protected $parentId;
    protected $dynamicKey;
    protected $section;
    protected $displayOrder;
    protected $title;
    protected $action;
    protected $prefix;
    protected $active;
    protected $type;

    protected $_table = "mainmenu";
    protected $_primaryKeys = array("menuId");

	function updateDisplayOrder($displayOrder) {
		$db = Zend_Registry::get('dbAdapter');
		$db->beginTransaction();
		try {
			$sql = "update mainmenu set displayOrder = displayOrder-1 where parentId = " . $db->quote($this->parentId) . " and displayOrder > " . (int)$this->displayOrder;
			//trigger_error("id beofre:" . $sql,E_USER_NOTICE);
			$db->query($sql);
			$sql = "update mainmenu set displayOrder = " . (int) $displayOrder . " where menuId = " . $this->menuId;
			//trigger_error("id beofre:" . $sql,E_USER_NOTICE);
			$db->query($sql);

		}
		catch (Exception $e) {
			$db->rollBack();
			return false;
		}
		$db->commit();
		return true;
	}

    public function delete() {
        $db = Zend_Registry::get('dbAdapter');
        $sql = "DELETE FROM `mainmenu` WHERE `menuId`='{$this->menuId}'";
        return $db->query($sql);
    }
}
