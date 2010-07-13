<?php
/*****************************************************************************
*       AdminPharmaciesController.php
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


class AdminPharmaciesController extends WebVista_Controller_Action {
	protected $_form;
	protected $_pharmacy;

	public function preDispatch() {
		parent::preDispatch();
		$this->view->addPharmacy = false;
	}

	public function indexAction() {
		$this->render();
	}

	public function addAction() {
		$this->getHelper('viewRenderer')->setNoRender();
		$this->editAction();
		$this->getResponse()->clearBody();
		$this->view->addPharmacy = true;
		$this->render('edit');
	}
	
	public function processEditAction() {
		$params = $this->_getParam('pharmacy');
		if (isset($params['preferred'])) {
			$params['preferred'] = 1;
		}
		$pharmacy = new Pharmacy();
		$pharmacy->populateWithArray($params);
		//$pharmacy->pharmacyId = Pharmacy::generateGUID();
		$pharmacy->LastModifierDate = date('Y-m-d H:i:s');
		$pharmacy->persist();
		$data['pharmacyId'] = $pharmacy->pharmacyId;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function editAction() {
		$pharmacyId = preg_replace('/[^a-zA-Z0-9-]+/','',$this->_getParam('pharmacyId'));
		if (isset($this->_session->messages)) {
			$this->view->messages = $this->_session->messages;
		}
		$this->_form = new WebVista_Form(array('name' => 'pharmacy-detail'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "admin-pharmacy.raw/process-edit");
		$this->_pharmacy = new Pharmacy();
		$this->_pharmacy->pharmacyId = $pharmacyId;
		if (!$this->_pharmacy->populate()) {
			$this->_pharmacy->RecordChange = 'N';
		}
		$this->_form->loadORM($this->_pharmacy, "Pharmacy");
		//var_dump($this->_form);
		$this->view->form = $this->_form;
		$this->view->pharmacy = $this->_pharmacy;
		$this->render('edit');
	}

	public function autoCompleteAction() {
		$match = $this->_getParam('name');
		$match = preg_replace('/[^a-zA-Z-0-9]/','',$match);
		$matches = array();
		if (!strlen($match) > 0) {
			$this->_helper->autoCompleteDojo($matches);
		}
		$db = Zend_Registry::get('dbAdapter');
		$patSelect = $db->select()
				->from('pharmacies')
				->where('pharmacies.StoreName like ' . $db->quote($match.'%'))
				->order('pharmacies.State DESC')
				->order('pharmacies.City DESC')
				->order('pharmacies.StoreName DESC')
				->limit(50);
		//echo $patSelect->__toString();exit;
		//var_dump($db->query($patSelect)->fetchAll());exit;
		foreach($db->query($patSelect)->fetchAll() as $row) {
			$matches[$row['pharmacyId']] = $row['StoreName'] . ' ' . $row['City'] . ' ' .  $row['State'];
		}
		//var_dump($matches);exit;
		//$matches = array("name1" => $match, "name2" =>"value3");
		$this->_helper->autoCompleteDojo($matches);
	}

	public function healthcloudSyncAction() {
		$this->render();
	}

	public function ajaxActivateDownloadUrlAction() {
		$data = array();
		$data['daily'] = (int)$this->_getParam('daily');
		$data['portalId'] = Zend_Registry::get('config')->sureScripts->portalId;
		$data['accountId'] = Zend_Registry::get('config')->sureScripts->accountId;
		//$data['clinicName'] = $practice->name;
		$type = 'full';
		if ($data['daily']) {
			$type = 'daily';
		}

		$messaging = new Messaging();
		//$messaging->messagingId = '';
		$messaging->messageType = 'DirectoryDownload';
		$messaging->populate();
		//$messaging->objectId = '';
		//$messaging->objectClass = '';
		$messaging->status = 'Downloading';
		$messaging->note = 'Downloading pharmacy ('.$type.')';
		$messaging->dateStatus = date('Y-m-d H:i:s');
		//$messaging->auditId = '';
		$messaging->persist();

		$ch = curl_init();
		$pharmacyActivateURL = Zend_Registry::get('config')->healthcloud->URL;
		$pharmacyActivateURL .= 'ss-manager.raw/activate-pharmacy-download?apiKey='.Zend_Registry::get('config')->healthcloud->apiKey;
		$cookieFile = tempnam(sys_get_temp_dir(),'ssddcookies_');
		curl_setopt($ch,CURLOPT_URL,$pharmacyActivateURL);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_COOKIEJAR,$cookieFile); 
		curl_setopt($ch,CURLOPT_USERPWD,'admin:ch3!');
		$output = curl_exec ($ch);
		$error = "";
		$downloadURL = "";
		$messaging->status = 'Downloaded';
		$messaging->note = 'Pharmacy downloaded ('.$type.')';
		if (!curl_errno($ch)) {
			try {
				$responseXml = simplexml_load_string($output);
				if (isset($responseXml->error)) {
					$error = (string)$responseXml->error->messageCode.': '.(string)$responseXml->error->message;
					trigger_error("There was an error activating synchronization of pharmacies, Error code: " . $responseXml->error->code . " Error Message: " . $responseXml->error->message,E_USER_NOTICE);
				}
				elseif (isset($responseXml->data->SSDirectoryDownloadUrl)) {
					$downloadURL = $responseXml->data->SSDirectoryDownloadUrl;
				}
				if (isset($responseXml->rawMessage)) {
					$messaging->rawMessage = base64_decode((string)$responseXml->rawMessage);
				}
			}
			catch (Exception $e) {
				$error = __("There was an error connecting to HealthCloud to activate synchronization of pharmacies. Please try again or contact the system administrator.");
				trigger_error("Curl error connecting to healthcloud to activate pharmacy sync: " . curl_error($ch),E_USER_NOTICE);
			}
		}
		else {
			$error = __("There was an error connecting to HealthCloud to activate synchronization of pharmacies. Please try again or contact the system administrator.");
			trigger_error("Curl error connecting to healthcloud to activate pharmacy sync: " . curl_error($ch),E_USER_NOTICE);
		}
		curl_close ($ch);
		if (strlen($error) > 0) {
			$messaging->status = 'Error';
			$messaging->note .= ' ERROR: '.$error;
			$ret = false;
		}
		if ($messaging->resend) {
			$messaging->resend = 0;
		}
		$messaging->retries++;
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->persist();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('downloadUrl'=>$downloadURL,'cookieFile'=>$cookieFile,'error'=>$error));
	}

	public function ajaxDownloadPharmaciesFileAction() {
		$filename = urldecode($this->_getParam('filename'));
		$cookieFile = urldecode($this->_getParam('cookieFile'));
		$pharmUpdateFileName = tempnam(sys_get_temp_dir(),'ssdd_');
		$pharmUpdateFile = fopen($pharmUpdateFileName,'w');
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_COOKIEFILE,$cookieFile);
		curl_setopt($ch,CURLOPT_URL,$filename);
		curl_setopt($ch,CURLOPT_POST,false);
		curl_setopt($ch,CURLOPT_HTTPGET,true);
		curl_setopt($ch,CURLOPT_FILE,$pharmUpdateFile);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_USERPWD,'admin:ch3!');
		$output = curl_exec($ch);
		$error = "";
		fclose($pharmUpdateFile);
		$pharmaciesData = "";
		if (!curl_errno($ch)) {
			try {
				$zip = zip_open($pharmUpdateFileName);
				if ($zip) {
					while ($zipEntry = zip_read($zip)) {
						$name = zip_entry_name($zipEntry);
						zip_entry_open($zip,$zipEntry,'r');
						$pharmaciesData = zip_entry_read($zipEntry,zip_entry_filesize($zipEntry));
						zip_entry_close($zipEntry);
					}
					zip_close($zip);
				}
				else {
					$error = __("There was an unpacking the pharmacy data returned from HealthCloud. Please try again or contact the system administrator.");
					trigger_error("Zip error unpacking pharmacy data: " . $zip,E_USER_NOTICE);

				}
			}
			catch (Exception $e) {
				//todo add exceptions in above try
			}
		}
		curl_close ($ch);
		$tmpFileName = tempnam(sys_get_temp_dir(),'ssdddata_');
		$pharmDataTmp = fopen($tmpFileName,'w');
		fwrite($pharmDataTmp,$pharmaciesData);
		fclose($pharmDataTmp);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('filename'=>$tmpFileName));
	}

	public function ajaxLoadPharmaciesDataAction() {
		trigger_error('before loading pharmacies: '.calcTS(),E_USER_NOTICE);

		set_time_limit(300); // 5 minutes
		$filename = urldecode($this->_getParam('filename'));
		$filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.preg_replace('/.*(\/|\\ee)/','',$filename);
		$pharmDataTmp = fopen($filename,'r');

		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('pharmacies',array('pharmacyId','NCPDPID','preferred'));
		$pharmacies = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$pharmacies[$row['NCPDPID']] = array('pharmacyId'=>$row['pharmacyId'],'preferred'=>$row['preferred']);
			}
		}

		fseek($pharmDataTmp,0);
		$counter = 0;
		while($line = fgets($pharmDataTmp)) {
			$pharmacy = array();
			$pharmacy['NCPDPID'] = substr($line,0,7); 
			$pharmacy['StoreNumber'] = substr($line,7,35);
			$pharmacy['ReferenceNumberAlt1'] = substr($line,42,35);
			$pharmacy['ReferenceNumberAlt1Qualifier'] = substr($line,77,3);
			$pharmacy['StoreName'] = substr($line,80,35);
			$pharmacy['AddressLine1'] = substr($line,115,35);
			$pharmacy['AddressLine2'] = substr($line,150,35);
			$pharmacy['City'] = substr($line,185,35);
			$pharmacy['State'] = substr($line,220,2);
			$pharmacy['Zip'] = substr($line,222,11);
			$pharmacy['PhonePrimary'] = substr($line,233,25);
			$pharmacy['Fax'] = substr($line,258,25);
			$pharmacy['Email'] = substr($line,283,80); 
			$pharmacy['PhoneAlt1'] = substr($line,363,25);
			$pharmacy['PhoneAlt1Qualifier'] = substr($line,388,3);
			$pharmacy['PhoneAlt2'] = substr($line,391,25);
			$pharmacy['PhoneAlt2Qualifier'] = substr($line,416,3);
			$pharmacy['PhoneAlt3'] = substr($line,419,25);
			$pharmacy['PhoneAlt3Qualifier'] = substr($line,444,3);
			$pharmacy['PhoneAlt4'] = substr($line,447,25);
			$pharmacy['PhoneAlt4Qualifier'] = substr($line,472,3);
			$pharmacy['PhoneAlt5'] = substr($line,475,25);
			$pharmacy['PhoneAlt5Qualifier'] = substr($line,500,3);
			$pharmacy['ActiveStartTime'] = substr($line,503,22);
			$pharmacy['ActiveEndTime'] = substr($line,525,22);
			$pharmacy['ServiceLevel'] = substr($line,547,5);
			$pharmacy['PartnerAccount'] = substr($line,552,35);
			$pharmacy['LastModifiedDate'] = substr($line,587,22);
			$pharmacy['TwentyFourHourFlag'] = substr($line,609,1);
			$pharmacy['Available CrossStreet'] = substr($line,610,35);
			$pharmacy['RecordChange'] = substr($line,645,1);
			$pharmacy['OldServiceLevel'] = substr($line,646,5); 
			$pharmacy['TextServiceLevel'] = substr($line,651,100);
			$pharmacy['TextServiceLevelChange'] = substr($line,751,100);
			$pharmacy['Version'] = substr($line,851,5);
			$pharmacy['NPI'] = substr($line,856,10);
			$data = array();
			foreach ($pharmacy as $key=>$value) {
				$data[$key] = trim($value);
			}
			$p = new Pharmacy();
			$p->_shouldAudit = false;
			$p->populateWithArray($data);
			if (isset($pharmacies[$p->NCPDPID])) {
				$p->pharmacyId = $pharmacies[$p->NCPDPID]['pharmacyId'];
				$p->preferred = $pharmacies[$p->NCPDPID]['preferred'];
			}
			//$p->populatePharmacyIdWithNCPDPID();
			$p->persist();
			$counter++;
		}
		fclose($pharmDataTmp);
		unlink($filename);

		trigger_error('after loading pharmacies: '.calcTS(),E_USER_NOTICE);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($counter);
	}
}
