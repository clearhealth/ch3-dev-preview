<?php
/*****************************************************************************
*       MedicationsController.php
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


/**
 * Medications controller
 */
class MedicationsController extends WebVista_Controller_Action {

	protected $_form;
	protected $_session;
	protected $_medication;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function ajaxDiscontinueMedicationAction() {
		$medicationId = $this->_getParam('medicationId');
		$medication = new Medication();
		$medication->medicationId = (int)$medicationId;
		$medication->populate();
		// set discontinue here...
		$medication->persist();

		$data = array();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listMedicationsAction() {
		$personId = $this->_getParam('personId');
		$rows = array();
		$filter = array('patientId'=>$personId);
		$medicationIterator = new MedicationIterator();
		$medicationIterator->setFilter($filter);
		foreach ($medicationIterator as $medication) {
			$tmp = array();
			$tmp['id'] = $medication->medicationId;
			$tmp['data'][] = '';
			$tmp['data'][] = $medication->description;
			$tmp['data'][] = $medication->transmit;
			$tmp['data'][] = $medication->displayStatus;
			$tmp['data'][] = '';
			$tmp['data'][] = '';
			$tmp['data'][] = $medication->refillsRemaining;
			$tmp['data'][] = $medication->comment;
			$rows[] = $tmp;
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function editMedicationAction() {
		$personId = (int)$this->_getParam('personId');
		$medicationId = (int)$this->_getParam('medicationId');
		$copy = $this->_getParam('copy');

		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();

		if (strlen($copy) > 0) {
			$this->view->copy = $copy;
		}

		$this->view->scheduleOptions = $this->getScheduleOptions();
		$this->view->chBaseMed24Url = Zend_Registry::get('config')->healthcloud->CHMED->chBaseMed24Url;
		$this->view->chBaseMed24DetailUrl = Zend_Registry::get('config')->healthcloud->CHMED->chBaseMed24DetailUrl;
		$this->_form = new WebVista_Form(array('name' => 'new-medication'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "medications.raw/process-add-medication");

		$this->_medication = new Medication();
		$this->_medication->personId = $personId;
		
		if ($medicationId > 0) {
			$this->_medication->medicationId = (int)$medicationId;
			$this->_medication->populate();
		}
		if (!strlen($this->_medication->pharmacyId) > 0) {
			$this->_medication->pharmacyId = $patient->defaultPharmacyId;
		}

		if (strlen($copy) > 0) {
			$this->_medication->medicationId = 0;
		}

		$this->_form->loadORM($this->_medication, "Medication");
		$this->_form->setWindow('windowNewMedication');
		$this->view->form = $this->_form;
		$this->view->medication = $this->_medication;
		$this->render('new-medication');
	}

	public function processAddMedicationAction() {
		$this->editMedicationAction();
		$med = $this->_getParam('medication');
		$this->_medication->populateWithArray($med);
		$this->_medication->persist();
		$this->view->message = __("Record Saved");
		$this->render('new-medication');
	}

	/**
	 * Default action to dispatch
	 */
	public function indexAction() {
		$this->render('index');
	}


	public function toolbarAction() {
		header("Cache-Control: public");
		header("Pragma: public");

		$cache = Zend_Registry::get('cache');
		$cacheKey = "toolbar-" . Menu::getCurrentlySelectedActivityGroup() . "-" . Menu::getCurrentUserRole();
		$cacheKey = str_replace('-', '_', $cacheKey);
		$cacheKey = str_replace('/', '_', $cacheKey);
		if ($cache->test($cacheKey."_hash")) {
			$hash = $cache->load($cacheKey."_hash");
			$lastModified = $cache->load($cacheKey."_lastModified");
			$headers = getallheaders();
			if (isset($headers['If-None-Match']) && ereg($hash, $headers['If-None-Match'])) {
				header("Last-Modified: " . $lastModified);
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		if ($cache->test($cacheKey)) {
			$items = $cache->load($cacheKey);
		}
		else {
			$items = $this->render('toolbar');
			$hash = md5($items);
			$lastModified = gmdate("D, d M Y H:i:s")." GMT";
			$objConfig = new ConfigItem();
			$objConfig->configId = 'enableCache';
			$objConfig->populate();
			if ($objConfig->value) {
				$cache->save($hash, $cacheKey."_hash", array('tagToolbar'));
				$cache->save($lastModified, $cacheKey."_lastModified", array('tagToolbar'));
				$cache->save($items, $cacheKey, array('tagToolbar'));
			}
			header("ETag: ". $hash);
			header("Last-Modified: ". $lastModified);
			header("Content-length: "  . mb_strlen($items));
		}
		if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
			header("Content-type: application/xhtml+xml");
		}
		else {
			header("Content-type: text/xml");
		}
		return $items;

	}

	public function selectPharmacyAction() {
		$personId = (int)$this->_getParam('personId');
		$selectedPharmacyId = (int)$this->_getParam('selectedPharmacyId');
		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();
		$this->view->patient = $patient;
		$defPharmacy = new Pharmacy();
		$defPharmacy->pharmacyId = $patient->defaultPharmacyId;
		if ($defPharmacy->populate()) {
			$this->view->defaultPharmacy=$defPharmacy;
		}
		$this->view->selectedPharmacyId = $selectedPharmacyId;
		$practice = new Practice();
                $practice->practiceId = MainController::getActivePractice();
                $practice->populate();
                $practice->primaryAddress->populate();
		$this->view->practice = $practice;
	}

	public function listPharmaciesAction() {
		$filters = (array)$this->_getParam('filters');
		if (count($filters) == 0) $filters['preferred'] =1;
		$pharmacy = new Pharmacy();
		$pharmacyIter = $pharmacy->getIterator();
		$pharmacyIter->setFilters($filters);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
		$pharmacyIter->getDbColumns();
                $json->direct(array("rows" => $pharmacyIter->toJsonArray('pharmacyId',array('pharmacyId','StoreName','AddressLine1','City','newRxSupport','refReqSupport','rxFillSupport','rxChgSupport','canRx','rxHisSupport','rxEligSupport'))),true);
	}
	protected function getScheduleOptions() {
		$scheduleOptions = array('BID','MO-WE-FR','NOW','ONCE','Q12H','Q24H','Q2H','Q3H','Q4H','Q5MIN PRN');
		return $scheduleOptions;
	}

	public function transmitEprescriptionAction() {
		$medicationId = (int)$this->_getParam('medicationId');
		$medication = new Medication();
		$medication->medicationId = $medicationId;
		$medication->populate();
		//echo $medication->toString();
		//echo $medicationId;
		$data = $medication->toArray();
		$practice = new Practice();
		$practice->practiceId = MainController::getActivePractice();
		$practice->populate();
		$data['practiceName'] = $practice->name;
		$pharmacy = new Pharmacy();
		$pharmacy->pharmacyId = $medication->pharmacyId;
		$pharmacy->populate();
		$data['pharmacy'] = $pharmacy->toArray();
		$prescriber = new Provider();
		$prescriber->personId = $medication->prescriberPersonId;
		$prescriber->populate();
		$prescriber->person->populate();
		$data['prescriber'] = $prescriber->toArray();
		$data['prescriber']['agentFirstName'] = '';
		$data['prescriber']['agentLastName'] = '';
		$data['prescriber']['agentSuffix'] = '';
		$addressIterator = new AddressIterator();
		$addressIterator->setFilters(array('class' => 'person','personId' => $prescriber->personId));
		$data['prescriber']['address'] = $addressIterator->first()->toArray();
		$phoneIterator = new PhoneNumberIterator();
		$phoneIterator->setFilters(array('class' => 'person','personId' => $prescriber->personId));
		$data['prescriber']['phone'] = $phoneIterator->first()->toArray();
		$patient = new Patient();
		$patient->personId = $medication->personId;
		$patient->populate();
		$data['patient'] = $patient->toArray();
		$phoneIterator->setFilters(array('class' => 'person','personId' => $patient->personId));
		$data['patient']['phone'] = $phoneIterator->first()->toArray();
		//var_dump($data);exit;
		$data = $this->makePostArray($data);
		//var_dump($this->makePostArray($data));exit;
		//var_dump($data);exit;
		$transmitEPrescribeURL = Zend_Registry::get('config')->healthcloud->URL;
                $transmitEPrescribeURL .= "SSRX/NewRx?apiKey=" . Zend_Registry::get('config')->healthcloud->apiKey;
                $cookieFile = tempnam(sys_get_temp_dir(),"ssddcookies_");
		$ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$transmitEPrescribeURL);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                $output = curl_exec ($ch);
		echo $output;
		exit;
	}
	private function makePostArray($data,$leading = '') {
		$pData = array();
		foreach($data as $key => $value) {
			if (is_array($value)) {
				$pData = array_merge($pData,$this->makePostArray($value,$leading . '[' . $key . ']'));
			}
			else {
				$pData ['medication' . $leading . '[' . $key . ']'] = $value;
			}
		}
		return $pData;
	}

	public function getPrescriptionPdfAction() {
		$medicationId = (int)$this->_getParam('medicationId');
		$medication = new Medication();
		$medication->medicationId = $medicationId;
		$medication->populate();
		$xmlData =  PdfController::toXML($medication,'Medication',null);
		//ff560b50-75d0-11de-8a39-0800200c9a66 is uuid for prescription PDF
		$this->_forward('pdf-merge-attachment','pdf', null, array('attachmentReferenceId' => 'ff560b50-75d0-11de-8a39-0800200c9a66','xmlData'=>$xmlData));
	}

	public function medicationsContextMenuAction() {
		//placeholder function, template is xml and autorenders when called as medications-context-menu.raw
	}

	public function processPrintedRxAction() {
		// transmit
		// dateTransmitted
		$medicationId = $this->_getParam('medicationId');
		$medication = new Medication();
		$medication->medicationId = $medicationId;
		$medication->populate();
		$medication->transmit = 'print';
		$medication->dateTransmitted = date('Y-m-d H:i:s');
		$medication->persist();

		$rows = array();
		$rows['msg'] = __('Saved successfully.');
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public static function buildJSJumpLink($objectId,$signingUserId,$objectClass) {
		$objectClass = 'Medications'; // temporarily hard code objectClass based on MainController::getMainTabs() definitions
		$medication = new Medication();
		$medication->medicationId = $objectId;
		$medication->populate();
		$patientId = $medication->personId;

		$js = parent::buildJSJumpLink($objectId,$patientId,$objectClass);
		$js .= <<<EOL

mainTabbar.setOnTabContentLoaded(function(tabId){
	loadMedication(objectId);
	openNewMedicationWindow(objectId);
});

EOL;
		return $js;
	}

}
