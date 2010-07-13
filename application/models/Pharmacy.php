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
		$serviceStr = "NewRX:" . $this->getNewRxSupport() . " ";
		$serviceStr .= "RefReq:" . $this->getRefReqSupport() . " ";
		$serviceStr .= "RxFill:" . $this->getRxFillSupport() . " ";
		$serviceStr .= "RxChg:" . $this->getRxChgSupport() . " ";
		$serviceStr .= "CanRx:" . $this->getCanRx()  . " ";
		$serviceStr .= "RxHis:" . $this->getRxHisSupport() . " ";
		$serviceStr .= "RxElig:" . $this->getRxEligSupport() ;
		return $serviceStr;
	}

	public function sendPharmacy() {
		$uuid = uuid_create();
		$messageId = str_replace('-','',$uuid);
		$messaging = new Messaging();
		$messaging->messagingId = $messageId;
		$messaging->messageType = 'AddPharmacy';
		$messaging->populate();
		$messaging->objectId = $this->pharmacyId;
		$messaging->objectClass = 'Pharmacy';
		$messaging->status = 'Sending';
		$messaging->note = 'Sending new pharmacy';
		$type = 'add';
		switch ($this->RecordChange) {
			case 'N': // New
				break;
			case 'D':
				break;
			default: // or case 'U': // Update
				$type = 'update';
				$messaging->messageType = 'UpdatePharmacy';
				$messaging->note = 'Sending update pharmacy';
				break;
		}
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->persist();
		$data = $this->toArray();
		$data['messageId'] = $messageId;

		$query = http_build_query(array('type'=>$type,'data'=>$data));
		$ch = curl_init();
		$ePrescribeURL = Zend_Registry::get('config')->healthcloud->URL;
		$ePrescribeURL .= 'ss-manager.raw/edit-pharmacy?apiKey='.Zend_Registry::get('config')->healthcloud->apiKey;
		curl_setopt($ch,CURLOPT_URL,$ePrescribeURL);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$query);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		$output = curl_exec($ch);
		$error = '';
		$messaging->status = 'Sent';
		$messaging->note = 'Add Pharmacy sent';
		if ($type == 'update') {
			$messaging->note = 'Update Pharmacy sent';
		}
		if (!curl_errno($ch)) {
			try {
				$responseXml = new SimpleXMLElement($output);
				if (isset($responseXml->error)) {
					$errorCode = (string)$responseXml->error->code;
					$errorMsg = (string)$responseXml->error->message;
					if (isset($responseXml->error->errorCode)) {
						$errorCode = (string)$responseXml->error->errorCode;
					}
					if (isset($responseXml->error->errorMsg)) {
						$errorMsg = (string)$responseXml->error->errorMsg;
					}
					$error = $errorMsg;
					trigger_error('There was an error prescribing new medication, Error code: '.$errorCode.' Error Message: '.$errorMsg,E_USER_NOTICE);
				}
				else if (isset($responseXml->status)) {
					if ((string)$responseXml->status->code == '010') { // value 000 is for free standing error?
						$messaging->status .= ' and Verified';
						$messaging->note .= ' and verified';
					}
				}
				if (isset($responseXml->rawMessage)) {
					$messaging->rawMessage = base64_decode((string)$responseXml->rawMessage);
				}
			}
			catch (Exception $e) {
				$error = __("There was an error connecting to HealthCloud to prescribe new medication. Please try again or contact the system administrator.");
				trigger_error("There was an error prescribing new medication, the response couldn't be parsed as XML: " . $output, E_USER_NOTICE);
			}
		}
		else {
			$error = __("There was an error connecting to HealthCloud to prescribe new medication. Please try again or contact the system administrator.");
			trigger_error("Curl error connecting to healthcare prescribed new medication: " . curl_error($ch),E_USER_NOTICE);
		}

		curl_close ($ch);
		$ret = true;
		if (strlen($error) > 0) {
			$messaging->status = 'Error';
			$messaging->note = $error;
			$ret = $error;
		}
		else {
			$this->RecordChange = 'U';
			$this->persist();
		}
		if ($messaging->resend) {
			$messaging->resend = 0;
		}
		$messaging->retries++;
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->persist();
		return $ret;
	}

	public function populatePharmacyIdWithNCPDPID($NCPDPID = null) {
		$db = Zend_Registry::get('dbAdapter');
		if ($NCPDPID === null) {
			$NCPDPID = $this->NCPDPID;
		}
		$sqlSelect = $db->select()
				->from($this->_table,'pharmacyId')
				->where('NCPDPID = ?',(int)$NCPDPID);
		if ($row = $db->fetchRow($sqlSelect)) {
			$this->pharmacyId = $row['pharmacyId'];
		}
	}

}
