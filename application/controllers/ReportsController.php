<?php
/*****************************************************************************
*       ReportsController.php
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


class ReportsController extends WebVista_Controller_Action {

	public function init() {
        	$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		$this->render('index');
		return;
		$report = new Report();
		$report->id = 1;
		$report->populate();
		//echo $report->toString();
		foreach($report->reportQueries as $query) {
			echo $query->execute()->toXml();
		}
		exit;
	}

	public function listReportsAction() {
		$xml = new SimpleXMLElement('<rows/>');
		ReportBaseClosure::generateXMLTree($xml);
                header('content-type: text/xml');
		$this->view->content = $xml->asXml();
		$this->render('list');
	}

	public function pdfTemplateAction() {
		$reportTemplateId = (int)$this->_getParam('reportTemplateId');
		setlocale(LC_CTYPE, 'en_US');
		$xmlData =  PdfController::toXML(array(),'FlowSheet',null);
                $this->_forward('pdf-merge-attachment','pdf', null, array('attachmentReferenceId' => $reportTemplateId,'xmlData'=>$xmlData));
	}
	
	public function flowSheetTemplateAction() {
		$personId = (int)$this->_getParam('personId');
		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();
		$vitalSignIter = new VitalSignGroupsIterator();
		$vitalSignIter->setFilter(array("personId" => $personId));
		$xmlData =  PdfController::toXML($patient,'Patient',null);
		$xmlData .= "<VitalSignGroups>";
		$loop = 0;
		foreach($vitalSignIter as $vitalGroup) {
			$xmlData .=  PdfController::toXML($vitalGroup,'VitalSignGroup',null);
			if ($loop > 5) exit;
			$loop++;
		}
		$xmlData .= "</VitalSignGroups>";
		//header('Content-type: text/xml;');
		//echo $xmlData;exit;
		$this->_forward('pdf-merge-attachment','pdf', null, array('attachmentReferenceId' => '5','xmlData'=>$xmlData));
	}

	function binaryTemplateAction() {
		$templateId = (int) $this->_getParam('templateId');
		$reportTemplate = new ReportTemplate();
		$reportTemplate->reportTemplateId = $templateId;
		$reportTemplate->populate();
		$this->getResponse()->setHeader('Content-Type', 'application/pdf');
		$this->view->content = $reportTemplate->template;
		$this->render();
	} 

	public function getReportAction() {
		$baseId = (int)$this->_getParam('baseId');
		$data = array(
			'filters'=>array(),
			'views'=>array(),
		);
		$reportBase = new ReportBase();
		$reportBase->reportBaseId = $baseId;
		$reportBase->populate();
		foreach ($reportBase->reportFilters as $reportFilter) {
			$filter = array();
			$filter['id'] = $reportFilter->id;
			$filter['name'] = $reportFilter->name;
			$filter['defaultValue'] = $reportFilter->defaultValue;
			$filter['type'] = $reportFilter->type;
			$filter['options'] = $reportFilter->options;
			$list = null;
			if ($reportFilter->type == ReportBase::FILTER_TYPE_ENUM) {
				$enumerationClosure = new EnumerationClosure();
				$filter['enums'] = array();
				$paths = $enumerationClosure->generatePaths($reportFilter->enumName['id']);
				foreach ($paths as $id=>$name) {
					$filter['enums'][] = array('id'=>$id,'name'=>$name);
				}
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_QUERY) {
				$reportQuery = new ReportQuery();
				$reportQuery->reportQueryId = (int)$reportFilter->query;
				$reportQuery->populate();
				$filter['queries'] = $reportQuery->executeQuery();
				if ($reportFilter->includeBlank) {
					array_unshift($filter['queries'],array('id'=>'','name'=>'&amp;nbsp;'));
				}
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_BUILDING) {
				$orm = new Building();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'buildingId',
					'name'=>'displayName',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_PRACTICE) {
				$orm = new Practice();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'practiceId',
					'name'=>'name',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_PROVIDER) {
				$orm = new Provider();
				$list = array(
					'ormIterator'=>$orm->getIter(),
					'id'=>'personId',
					'name'=>'displayName',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_ROOM) {
				$orm = new Room();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'roomId',
					'name'=>'displayName',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_BUILDING_PREF
				|| $reportFilter->type == ReportBase::FILTER_TYPE_LIST_ROOM_PREF
				|| $reportFilter->type == ReportBase::FILTER_TYPE_LIST_PROVIDER_PREF) {
				$room = User::myPreferencesLocation();
				$practiceId = (int)$room->building->practiceId;
				$buildingId = (int)$room->buildingId;
				if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_BUILDING_PREF) {
					$orm = new Building();
					$orm->practiceId = $practiceId;
					$list = array(
						'ormIterator'=>$orm->getIteratorByPracticeId(),
						'id'=>'buildingId',
						'name'=>'displayName',
					);
				}
				else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_ROOM_PREF) {
					$orm = new Room();
					$orm->buildingId = $buildingId;
					$list = array(
						'ormIterator'=>$orm->getIteratorByBuildingId(),
						'id'=>'roomId',
						'name'=>'displayName',
					);
				}
				else {
					$orm = new Provider();
					$list = array(
						'ormIterator'=>$orm->getIteratorByPracticeId($practiceId),
						'id'=>'personId',
						'name'=>'displayName',
					);
				}
			}
			if ($list !== null) {
				$filter['lists'] = array();
				foreach ($list['ormIterator'] as $row) {
					$filter['lists'][] = array('id'=>$row->{$list['id']},'name'=>htmlspecialchars($row->{$list['name']}));
				}
			}
			$data['filters'][] = $filter;
		}
		$reportView = new ReportView();
		$filters = array(
			'reportBaseId'=>$reportBase->reportBaseId,
			'active'=>1,
		);
		$reportViewIterator = $reportView->getIteratorByFilters($filters);
		foreach ($reportViewIterator as $view) {
			$row = array();
			$row['id'] = $view->reportViewId;
			$row['data'] = array();
			$row['data'][] = $view->displayName;
			$row['data'][] = $view->runQueriesImmediately;
			$row['data'][] = (strlen($view->showResultsIn) > 0)?$view->showResultsIn:'grid';
			$data['views'][] = $row;
		}
		$this->view->filterTypes = ReportBase::getFilterTypes();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function getResultsAction() {
		$viewId = (int)$this->_getParam('viewId');
		$params = $this->_getAllParams();
		$filterParams = array();
		foreach ($params as $key=>$value) {
			if (substr($key,0,7) != 'filter_') continue;
			$index = substr($key,7);
			$x = explode('_',$index);
			if (!isset($x[1])) continue;
			$index = str_replace('_','.',$index);
			$filterParams[$index] = $value;
		}

		$result = ReportBase::generateResults($viewId,$filterParams);
		$data = $result['data'];
		if (isset($result['value'])) {
			$value = $result['value'];
			switch ($result['type']) {
				case 'file':
					return $this->_forward('flat','files',null,array('data'=>$value));
				case 'xml':
					return $this->_forward('xml','files',null,array('data'=>$value));
				case 'pdf':
					return $this->_forward('pdf-merge-attachment','pdf',null,array('attachmentReferenceId'=>$value['attachmentReferenceId'],'xmlData'=>$value['xmlData']));
				case 'graph': // to be implemented
					break;
				case 'pdr':
					return $this->_forward('flat','files',null,array('data'=>$value));
				case 'pqri':
					return $this->_forward('zip','files',null,array('data'=>$value));
			}
		}
		//trigger_error(print_r($data,true),E_USER_NOTICE);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	protected function _addChild(SimpleXMLElement $xml,$key,$value) {
		if (is_object($value)) trigger_error($key.'='.get_class($value));
		if ($key && $value) $xml->addChild($key,htmlentities($value));
	}

	public function patientIntakeAction() {
		$personId = (int)$this->_getParam('personId');
		// bd3a6ca7-63e4-432c-a0f1-623c7f5bb839
		$referenceId = $this->_getParam('referenceId');
		$data = $this->_getAttachmentData($referenceId);

		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();
		$person = $patient->person;
		$picture = '';
		if ($person->activePhoto > 0) {
			$attachment = new Attachment();
			$attachment->attachmentId = (int)$person->activePhoto;
			$attachment->populate();
			$picture = base64_encode($attachment->rawData);
		}

		$xml = new SimpleXMLElement('<intakeSubform/>');
		$xmlPatient = $xml->addChild('patient');
		$this->_addChild($xmlPatient,'picture',$picture);
		$this->_addChild($xmlPatient,'lastName',$person->lastName);
		$this->_addChild($xmlPatient,'firstName',$person->firstName);
		$this->_addChild($xmlPatient,'middleName',$person->middleName);
		$identifier = '';
		if ($person->identifierType == 'SSN') $identifier = $person->identifier;
		$this->_addChild($xmlPatient,'identifier',$identifier);
		$this->_addChild($xmlPatient,'gender',$person->gender);
		$dateOfBirth = explode(' ',date('m d Y',strtotime($person->dateOfBirth)));
		$this->_addChild($xmlPatient,'dobMonth',$dateOfBirth[0]);
		$this->_addChild($xmlPatient,'dobDay',$dateOfBirth[1]);
		$this->_addChild($xmlPatient,'dobYear',$dateOfBirth[2]);
		$statistics = PatientStatisticsDefinition::getPatientStatistics($personId);
		$race = '';
		if (isset($statistics['Race'])) $race = $statistics['Race'];
		else if (isset($statistics['race'])) $race = $statistics['race'];
		$this->_addChild($xmlPatient,'race',$race);
		$maritalStatus = ($person->maritalStatus)?$person->maritalStatus:'Other';
		$this->_addChild($xmlPatient,'maritalStatus',$maritalStatus);
		$addresses = Address::listAddresses($personId);
		$phoneNumbers = PhoneNumber::listPhoneNumbers($personId);

		if (isset($addresses[Address::TYPE_BILLING])) {
			$address = $addresses[Address::TYPE_BILLING];
			$phone = isset($phoneNumbers[PhoneNumber::TYPE_BILLING])?$phoneNumbers[PhoneNumber::TYPE_BILLING]->number:'';
			$this->_addChild($xmlPatient,'billingLine1',$address->line1);
			$this->_addChild($xmlPatient,'billingCity',$address->city);
			$this->_addChild($xmlPatient,'billingState',$address->state);
			$this->_addChild($xmlPatient,'billingZip',$address->postalCode);
			$this->_addChild($xmlPatient,'homeNumber',$phone);
		}

		$address = null;
		if (isset($addresses[Address::TYPE_HOME])) $address = $addresses[Address::TYPE_HOME];
		else if (isset($addresses[Address::TYPE_MAIN])) $address = $addresses[Address::TYPE_MAIN];
		else if (isset($addresses[Address::TYPE_SEC])) $address = $addresses[Address::TYPE_SEC];
		else if (isset($addresses[Address::TYPE_OTHER])) $address = $addresses[Address::TYPE_OTHER];
		if ($address !== null) {
			$phone = '';
			if (isset($phoneNumbers[PhoneNumber::TYPE_HOME])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_WORK])) $phone = $phoneNumbers[PhoneNumber::TYPE_WORK]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_HOME_DAY])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME_DAY]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_HOME_EVE])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME_EVE]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_MOBILE])) $phone = $phoneNumbers[PhoneNumber::TYPE_MOBILE]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_BEEPER])) $phone = $phoneNumbers[PhoneNumber::TYPE_BEEPER]->number;
			$this->_addChild($xml,'addressStreet',$address->line1);
			$this->_addChild($xml,'addressCity',$address->city);
			$this->_addChild($xml,'addressState',$address->state);
			$this->_addChild($xml,'addressZip',$address->postalCode);
			$this->_addChild($xml,'patientPhone',$phone);
		}

		if (isset($addresses[Address::TYPE_EMPLOYER])) {
			$address = $addresses[Address::TYPE_EMPLOYER];
			$phone = isset($phoneNumbers[PhoneNumber::TYPE_EMPLOYER])?$phoneNumbers[PhoneNumber::TYPE_EMPLOYER]->number:'';
			$this->_addChild($xmlPatient,'employerLine1',$address->line1);
			$this->_addChild($xmlPatient,'employerCity',$address->city);
			$this->_addChild($xmlPatient,'employerState',$address->state);
			$this->_addChild($xmlPatient,'employerZip',$address->postalCode);
			$this->_addChild($xmlPatient,'employerNumber',$phone);
		}

		$phone = isset($phoneNumbers[PhoneNumber::TYPE_EMERGENCY])?$phoneNumbers[PhoneNumber::TYPE_EMERGENCY]->number:'';
		$this->_addChild($xmlPatient,'emergencyName','');
		$this->_addChild($xmlPatient,'emergencyNumber',$phone);

		$insuredRelationship = new InsuredRelationship();
		$insuredRelationshipIterator = $insuredRelationship->getIteratorByPersonId($personId);
		$primary = null;
		$secondary = null;
		foreach ($insuredRelationshipIterator as $item) {
			if (!$item->active) continue;
			if ($primary === null) $primary = $item;
			else if ($secondary === null) $secondary = $item;
			else break;
		}

		if ($primary !== null) $this->_addChild($xmlPatient,'medicareNumber',$primary->insuranceProgram->payerIdentifier);
		if ($secondary !== null) $this->_addChild($xmlPatient,'medicaidNumber',$secondary->insuranceProgram->payerIdentifier);

		try {
			$content = ReportBase::mergepdfset($xml,$data);
			$this->getResponse()->setHeader('Content-Type','application/pdf');
		}
		catch (Exception $e) {
			$content = '<script>alert("'.$e->getMessage().'")</script>';
		}
		$this->view->content = $content;
		$this->render('binary-template');
	}

	public function defaultPatientHeaderAction() {
		$personId = (int)$this->_getParam('personId');
		// e76f18cd-d388-4c53-b940-53cb81b80c5e
		$referenceId = $this->_getParam('referenceId');
		$data = $this->_getAttachmentData($referenceId);

		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();
		$person = $patient->person;
		$picture = '';
		if ($person->activePhoto > 0) {
			$attachment = new Attachment();
			$attachment->attachmentId = (int)$person->activePhoto;
			$attachment->populate();
			$picture = base64_encode($attachment->rawData);
		}

		$xml = new SimpleXMLElement('<patientHeader/>');
		$xmlPatient = $xml->addChild('patient');
		$this->_addChild($xmlPatient,'picture',$picture);
		$this->_addChild($xmlPatient,'lastName',$person->lastName);
		$this->_addChild($xmlPatient,'firstName',$person->firstName);
		$this->_addChild($xmlPatient,'dateOfBirth',$person->dateOfBirth);
		$this->_addChild($xmlPatient,'gender',$person->gender);

		$statistics = PatientStatisticsDefinition::getPatientStatistics($personId);
		$race = '';
		if (isset($statistics['Race'])) $race = $statistics['Race'];
		else if (isset($statistics['race'])) $race = $statistics['race'];
		$this->_addChild($xmlPatient,'race',$race);
		$this->_addChild($xmlPatient,'maritalStatus',$person->maritalStatus);
		$addresses = Address::listAddresses($personId);
		$phoneNumbers = PhoneNumber::listPhoneNumbers($personId);

		$address = null;
		if (isset($addresses[Address::TYPE_BILLING])) $address = $addresses[Address::TYPE_BILLING];
		else if (isset($addresses[Address::TYPE_HOME])) $address = $addresses[Address::TYPE_HOME];
		else if (isset($addresses[Address::TYPE_MAIN])) $address = $addresses[Address::TYPE_MAIN];
		else if (isset($addresses[Address::TYPE_SEC])) $address = $addresses[Address::TYPE_SEC];
		else if (isset($addresses[Address::TYPE_OTHER])) $address = $addresses[Address::TYPE_OTHER];
		if ($address !== null) {
			$phone = '';
			if (isset($phoneNumbers[PhoneNumber::TYPE_BILLING])) $phone = $phoneNumbers[PhoneNumber::TYPE_BILLING]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_HOME])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_WORK])) $phone = $phoneNumbers[PhoneNumber::TYPE_WORK]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_HOME_DAY])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME_DAY]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_HOME_EVE])) $phone = $phoneNumbers[PhoneNumber::TYPE_HOME_EVE]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_MOBILE])) $phone = $phoneNumbers[PhoneNumber::TYPE_MOBILE]->number;
			else if (isset($phoneNumbers[PhoneNumber::TYPE_BEEPER])) $phone = $phoneNumbers[PhoneNumber::TYPE_BEEPER]->number;
			$this->_addChild($xmlPatient,'billingLine1',$address->line1);
			$this->_addChild($xmlPatient,'billingCity',$address->city);
			$this->_addChild($xmlPatient,'billingState',$address->state);
			$this->_addChild($xmlPatient,'billingZip',$address->postalCode);
			$this->_addChild($xmlPatient,'phoneNumber',$phone);
		}

		if ($person->primaryPracticeId > 0) {
			$practice = new Practice();
			$practice->practiceId = (int)$person->primaryPracticeId;
			$practice->populate();
			$address = $practice->primaryAddress;
			$xmlPractice = $xml->addChild('practice');
			$this->_addChild($xmlPractice,'name',$practice->name);
			$this->_addChild($xmlPractice,'primaryLine1',$address->line1);
			$this->_addChild($xmlPractice,'primaryCity',$address->city);
			$this->_addChild($xmlPractice,'primaryState',$address->state);
			$this->_addChild($xmlPractice,'primaryZip',$address->postalCode);
			$this->_addChild($xmlPractice,'mainPhone',$practice->mainPhone->number);
			$this->_addChild($xmlPractice,'faxNumber',$practice->fax->number);
		}

		$insuredRelationship = new InsuredRelationship();
		$insuredRelationshipIterator = $insuredRelationship->getIteratorByPersonId($personId);
		$primary = null;
		$secondary = null;
		foreach ($insuredRelationshipIterator as $item) {
			if (!$item->active) continue;
			if ($primary === null) $primary = $item;
			else if ($secondary === null) $secondary = $item;
			else break;
		}

		$xmlPayer = $xml->addChild('payer');
		if ($primary !== null) $this->_addChild($xmlPayer,'primary',$primary->insuranceProgram->payerIdentifier);
		if ($secondary !== null) $this->_addChild($xmlPayer,'secondary',$secondary->insuranceProgram->payerIdentifier);

		$xmlGuarantor = $xml->addChild('guarantor');
		$this->_addChild($xmlGuarantor,'lastName','');
		$this->_addChild($xmlGuarantor,'firstName','');
		$this->_addChild($xmlGuarantor,'dateOfBirth','');
		$this->_addChild($xmlGuarantor,'phone','');

		try {
			$content = ReportBase::mergepdfset($xml,$data);
			$this->getResponse()->setHeader('Content-Type','application/pdf');
		}
		catch (Exception $e) {
			$content = '<script>alert("'.$e->getMessage().'")</script>';
		}
		$this->view->content = $content;
		$this->render('binary-template');
	}

	public function defaultPatientMostRecentNoteAction() {
		$personId = (int)$this->_getParam('personId');
		$clinicalNoteDefinitionId = (int)$this->_getParam('clinicalNoteDefinitionId');
		// ab3d26a7-49c5-4091-b496-23169f5ed41a
		$referenceId = $this->_getParam('referenceId');
		$data = $this->_getAttachmentData($referenceId);

		$clinicalNote = ClinicalNote::mostRecent($personId,$clinicalNoteDefinitionId);
		try {
			if (!$clinicalNote->clinicalNoteId > 0) throw new Exception('No most recent note');
			$xml = $clinicalNote->populateXML();
			$patient = new Patient();
			$patient->personId = (int)$clinicalNote->personId;
			$patient->populate();
			$xml = $patient->populateXML($xml);
			$content = ReportBase::mergepdfset($xml,$data);
			$this->getResponse()->setHeader('Content-Type','application/pdf');
		}
		catch (Exception $e) {
			$content = '<script>alert("'.$e->getMessage().'")</script>';
		}
		$this->view->content = $content;
		$this->render('binary-template');
	}
  
	public function defaultPatientSelectedNoteAction() {
		// 66ea047a-33fc-470f-844e-ed053bf3b4bf
		$referenceId = $this->_getParam('referenceId');
		$data = $this->_getAttachmentData($referenceId);

		$clinicalNoteId = (int)$this->_getParam('clinicalNoteId');
		$revisionId = (int)$this->_getParam('revisionId');
		$clinicalNote = new ClinicalNote();
		$clinicalNote->clinicalNoteId = $clinicalNoteId;
		$clinicalNote->populate();
		$xml = $clinicalNote->populateXML(null,$revisionId);
		$patient = new Patient();
		$patient->personId = (int)$clinicalNote->personId;
		$patient->populate();
		$xml = $patient->populateXML($xml);
		try {
			$content = ReportBase::mergepdfset($xml,$data);
			$this->getResponse()->setHeader('Content-Type','application/pdf');
		}
		catch (Exception $e) {
			$content = '<script>alert("'.$e->getMessage().'")</script>';
		}
		$this->view->content = $content;
		$this->render('binary-template');
	}
  
	public function defaultPatientNoteAction() {
		// bf9f3f39-dbc2-493f-9ca7-dde335f54de3
		$referenceId = $this->_getParam('referenceId');
		$data = $this->_getAttachmentData($referenceId);

		$clinicalNoteId = (int)$this->_getParam('clinicalNoteId');
		$revisionId = (int)$this->_getParam('revisionId');
		$clinicalNote = new ClinicalNote();
		$clinicalNote->clinicalNoteId = $clinicalNoteId;
		$clinicalNote->populate();
		$xml = new SimpleXMLElement('<data/>');
		$xml->addChild('clinicalNoteContents',$clinicalNote->toASCII($revisionId));
		$patient = new Patient();
		$patient->personId = (int)$clinicalNote->personId;
		$patient->populate();
		$xml = $patient->populateXML($xml);
		try {
			$content = ReportBase::mergepdfset($xml,$data);
			$this->getResponse()->setHeader('Content-Type','application/pdf');
		}
		catch (Exception $e) {
			$content = '<script>alert("'.$e->getMessage().'")</script>';
		}
		$this->view->content = $content;
		$this->render('binary-template');
	}

	protected function _getAttachmentData($referenceId) {
		$attachment = new Attachment();
		$attachment->attachmentReferenceId = $referenceId;
		$attachment->populateWithAttachmentReferenceId();
		$data = '';
		if (!$attachment->attachmentId > 0) {
			trigger_error('Invalid attachment reference id '.$referenceId);
		}
		else {
			$data = $attachment->rawData;
		}
		return $data;
	}

}
