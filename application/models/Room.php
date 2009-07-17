<?php
/*****************************************************************************
*	Room.php
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


class Room extends WebVista_Model_ORM {
    protected $id;
	protected $description;
	protected $number_seats;
	protected $building_id;
	protected $name;
	protected $color;
	protected $routing_station;
    protected $_table = "rooms";
    protected $_primaryKeys = array("id");

    public function getRoomId() {
        return $this->id;
    }

    public function setRoomId($id) {
        $this->id = $id;
    }

	public static function getArray() {
		$ret = array();
		$provider = new Provider();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			 	->from('rooms', array('id', 'name'));
		$data = $db->fetchAll($dbSelect);
		foreach ($data as $row) {
			$ret[$row['id']] = $row['name'];
		}
		return $ret;
	}
}
