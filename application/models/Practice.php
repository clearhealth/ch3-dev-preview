<?php
/*****************************************************************************
*	Practice.php
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


class Practice extends WebVista_Model_ORM {
    protected $id;
    protected $name;
    protected $website;
    protected $identifier;
    protected $primaryAddress;
    protected $secondaryAddress;
    protected $_table = "practices";
    protected $_primaryKeys = array("id");

    public function __construct() {
        parent::__construct();
	$this->primaryAddress = new Address();
	$this->secondaryAddress = new Address();

    }

    public function getPracticeId() {
        return $this->id;
    }

    public function setPracticeId($id) {
        $this->id = $id;
	if ($this->primaryAddress->addressId > 0) {
		$this->primaryAddress = $this->_loadAddress(4); //4 is primary address
		echo $this->primaryAddress->toString();
        }
	if ($this->secondaryAddress->addressId > 0) {
		$this->secondaryAddress = $this->_loadAddress(5); //5 is secondary address
        }
    }
	private function _loadAddress($addressType) {
		$addressIterator = $this->primaryAddress->getIterator();
                $addressIterator->setFilters(array('practiceId' => $this->id,'class'=>'practice','addressType' => (int)$addressType));
                foreach($addressIterator as $address) {
                        return $address;
                }
		return new Address();
	}
	function populate() {
		parent::populate();
		$this->primaryAddress = $this->_loadAddress(4); //4 is primary address
		$this->secondaryAddress = $this->_loadAddress(5); //4 is primary address
		return true;	
	}
}
