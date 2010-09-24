<?php
/*****************************************************************************
*       Order.php
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


class Order extends WebVista_Model_ORM {
	protected $orderId;
	protected $providerId;
	protected $provider;
	protected $dateStart;
	protected $dateStop;
	protected $orderText;
	protected $status;
	protected $service;
	protected $eSignatureId;
	protected $_table = "orders";
	protected $_primaryKeys = array("orderId");
	protected $_cascadePersist = false;

	function __construct() {
		$this->provider = new Provider();
		$this->provider->_cascadePersist = false;
                parent::__construct();
        }

	function __get($key) {
		if (in_array($key,$this->ORMFields())) {
			return $this->$key;
		}
		elseif (in_array($key,$this->provider->ORMFields())) {
			return $this->provider->__get($key);
		}
		elseif (!is_null(parent::__get($key))) {
			return parent::__get($key);
		}
		elseif (!is_null($this->provider->__get($key))) {
			return $this->provider->__get($key);
		}
		return parent::__get($key);
	}

}
