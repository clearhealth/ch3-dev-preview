<?php
/*****************************************************************************
*       Practice.php
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


class Practice extends WebVista_Model_ORM {
	protected $id;
	protected $name;
	protected $website;
	protected $identifier;
	protected $primaryAddress;
	protected $secondaryAddress;
	protected $_table = 'practices';
	protected $_primaryKeys = array('id');

	public function __construct() {
		parent::__construct();
		$this->primaryAddress = new Address();
		$this->secondaryAddress = new Address();
	}

	public function getPracticeId() {
		return $this->id;
	}

	public function setId($id) {
		$this->setPracticeId($id);
	}

	public function setPracticeId($id) {
		$this->id = $id;
		if ($this->primaryAddress->addressId > 0) {
			$this->primaryAddress = $this->_loadAddress(4); //4 is primary address
		}
		if ($this->secondaryAddress->addressId > 0) {
			$this->secondaryAddress = $this->_loadAddress(5); //5 is secondary address
		}
	}

	private function _loadAddress($type) {
		$address = new Address();
		$address->practiceId = (int)$this->id;
		$address->type = (int)$type;
		$address->populateWithPracticeIdType();
		return $address;
	}

	public function populate() {
		parent::populate();
		$this->primaryAddress = $this->_loadAddress(4); //4 is primary address
		$this->secondaryAddress = $this->_loadAddress(5); //4 is primary address
		return true;
	}

	public function ormEditMethod($ormId) {
		$controller = Zend_Controller_Front::getInstance();
		$request = $controller->getRequest();
		$enumerationId = (int)$request->getParam('enumerationId');

		$params = array();
		$params['enumerationId'] = $enumerationId;
		$params['id'] = $ormId;
		$view = Zend_Layout::getMvcInstance()->getView();
		return $view->action('edit-practice','facilities',null,$params);
	}

}
