<?php
/*****************************************************************************
*       Pharmacy.php
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


class Pharmacy extends WebVista_Model_ORM {

	protected $pharmacyId;
	protected $NCPDPID;
	protected $StoreNumber;
	protected $ReferenceNumberAlt1;
	protected $ReferenceNumberAlt1Qualifier;
	protected $StoreName;
	protected $AddressLine1;
	protected $AddressLine2;
	protected $City;
	protected $State;
	protected $Zip;
	protected $PhonePrimary;
	protected $Fax;
	protected $Email;
	protected $PhoneAlt1;
	protected $PhoneAlt1Qualifier;
	protected $PhoneAlt2;
	protected $PhoneAlt2Qualifier;
	protected $PhoneAlt3;
	protected $PhoneAlt3Qualifier;
	protected $PhoneAlt4;
	protected $PhoneAlt4Qualifier;
	protected $PhoneAlt5;
	protected $PhoneAlt5Qualifier;
	protected $ActiveStartTime;
	protected $ActiveEndTime;
	protected $ServiceLevel;
	protected $PartnerAccount;
	protected $LastModifierDate;
	protected $TwentyFourHourFlag;
	protected $CrossStreet;
	protected $RecordChange;
	protected $OldServiceLevel;
	protected $TextServiceLevel;
	protected $TextServiceLevelChange;
	protected $Version;
	protected $NPI;
	protected $preferred;

	protected $_table = "pharmacies";
	protected $_primaryKeys = array("pharmacyId");

	function __construct() {
                parent::__construct();
        }
	function setServiceLevel($val) {
		$this->ServiceLevel = (int)$val;
	}
	function getNewRxSupport() {
		if (($this->ServiceLevel & 1) == 1) {
			return 'Y';
		}
		return 'F';
	}

	function getRefReqSupport() {
		if (($this->ServiceLevel &  2) == 2) {
			return 'Y';
		}
		return 'F';
	}

	function getRxFillSupport() {
		if (($this->ServiceLevel & 4) == 4) {
			return 'Y';
		}
		return 'F';
	}

	function getRxChgSupport() {
		if (($this->ServiceLevel & 8) == 8) {
			return 'Y';
		}
		return 'F';
	}

	function getCanRx() {
		if (($this->ServiceLevel & 16) == 16) {
			return 'Y';
		}
		return 'F';
	}

	function getRxHisSupport() {
		if (($this->ServiceLevel & 32) == 32) {
			return 'Y';
		}
		return 'F';
	}

	function getRxEligSupport() {
		if (($this->ServiceLevel & 64) == 64) {
			return 'Y';
		}
		return 'F';
	}
	function getServiceLineDisplay() {
		$serviceStr = "";
		$serviceStr = "NewRX:" . getNewRxSupport() . " ";
		$serviceStr .= "RefReq:" . getRefReqSupport() . " ";
		$serviceStr .= "RxFill:" . getRxFillSupport() . " ";
		$serviceStr .= "RxChg:" . getRxChgSupport() . " ";
		$serviceStr .= "CanRx:" . getCanRx()  . " ";
		$serviceStr .= "RxHis:" . getRxHisSupport() . " ";
		$serviceStr .= "RxElig:" . getRxEligSupport() ;
		return $serviceStr;
	}
}
