<?php
/*****************************************************************************
*       CCD.php
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


class CCD {

	protected $_xml = null;
	protected $_patientId = 0;
	public $patient = null;
	protected $_userId = 0;
	public $user = null;
	public $building = null;
	public $visit = null;
	protected $_title = '';
	public $problemLists = array();
	public $performers = array();
	public $labResults = array();

	public function __construct($withXSLT=false) {
		$xmlStr = '<?xml version="1.0" encoding="UTF-8"?>';
		if ($withXSLT) {
			$baseUrl = Zend_Registry::get('baseUrl');
			$xmlStr .= '<?xml-stylesheet type="text/xsl" href="'.$baseUrl.'ccd.raw/xsl"?>';
		}
		$xmlStr .= '<ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:sdtc="urn:hl7-org:sdtc" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/cdar2c32/infrastructure/cda/C32_CDA.xsd"/>';
		$this->_xml = new SimpleXMLElement($xmlStr);
	}

	public function getVisit() {
		return $this->visit;
	}

	public function setFiltersDateRange(&$filters) {
		if ($this->visit !== null) {
			$dateOfTreatment = strtotime($this->visit->dateOfTreatment);
			$dateRange = date('Y-m-d',strtotime('-30 days',$dateOfTreatment));
			$dateRange .= ';'.date('Y-m-d',$dateOfTreatment);
			$filters['dateRange'] = $dateRange;
		}
	}

	public static function formatDate($date = null) {
		if ($date === null) {
			$date = date('Y-m-d H:i:s');
		}
		$time = strtotime($date);
		$timezone = date('Z',$time);
		$hour = 60 * 60;
		$tz = $timezone / $hour;
		$time = date('YmdHis',$time).sprintf('%03d00',$tz);
		return $time;
	}

	public function populate($patientId,$userId,$visitId) {
		$this->_patientId = (int)$patientId;
		$patient = new Patient();
		$patient->personId = $this->_patientId;
		$patient->populate();
		$this->_title = $patient->displayName.' Healthcare Record';
		$this->patient = $patient;
		$this->_userId = (int)$userId;
		$user = new User();
		$user->personId = $this->_userId;
		$user->populate();
		$this->user = $user;
		$visit = new Visit();
		$visit->visitId = (int)$visitId;
		if ($visit->visitId > 0 && $visit->populate()) $this->visit = $visit;
		$this->building = Building::getBuildingDefaultLocation($this->user->personId);

		$performers = array();
		$problemList = new ProblemList();
		$filters = array();
		$filters['personId'] = $this->_patientId;
		$this->setFiltersDateRange($filters);
		$problems = array();
		$problemListIterator = new ProblemListIterator();
		$problemListIterator->setFilters($filters);
		foreach ($problemListIterator as $problem) {
			$problems[] = $problem;
			$providerId = (int)$problem->providerId;
			if (!isset($performers[$providerId])) {
				$provider = new Provider();
				$provider->personId = $providerId;
				$provider->populate();
				$performers[$providerId] = $provider;
			}
		}
		$this->problemLists = $problems;

		unset($filters['personId']);
		$filters['patientId'] = $this->_patientId;

		$labResults = array();
		$labTests = array();
		$labOrderTests = array();
		$labsIterator = new LabsIterator();
		$labsIterator->setFilters($filters);
		foreach ($labsIterator as $lab) {
			// get the lab order
			$labTestId = (int)$lab->labTestId;
			if (!isset($labTests[$labTestId])) {
				$labTest = new LabTest();
				$labTest->labTestId = (int)$lab->labTestId;
				$labTest->populate();
				$labTests[$labTestId] = $labTest;
			}
			$labTest = $labTests[$labTestId];
			$orderId = (int)$labTest->labOrderId;
			if (!isset($labOrderTests[$orderId])) {
				$orderLabTest = new OrderLabTest();
				$orderLabTest->orderId = $orderId;
				$orderLabTest->populate();
				$labOrderTests[$orderId] = $orderLabTest;
			}
			$orderLabTest = $labOrderTests[$orderId];
			$providerId = (int)$orderLabTest->order->providerId;
			if (!isset($performers[$providerId])) {
				$provider = new Provider();
				$provider->personId = $providerId;
				$provider->populate();
				$performers[$providerId] = $provider;
			}
			if (!isset($labResults[$orderId])) {
				$labResults[$orderId] = array();
				$labResults[$orderId]['results'] = array();
				$labResults[$orderId]['labTest'] = $labTest;
				$labResults[$orderId]['orderLabTest'] = $orderLabTest;
			}
			$labResults[$orderId]['results'][] = $lab;
		}
		$this->labResults = $labResults;

		$this->performers = $performers;
		$this->populateHeader($this->_xml);
		$this->populateBody($this->_xml);
		$xml = $this->_xml->asXML();
		$xml = str_replace('&lt;','<',$xml);
		$xml = str_replace('&gt;','>',$xml);
		return $xml;
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->loadXML($xml);
		return $doc->saveXML();
		//return $xml;
	}

	public function populateHeader(SimpleXMLElement $xml) {
		$patientName = array();
		$patientName['given'] = $this->patient->person->firstName;
		$patientName['family'] = $this->patient->person->lastName;
		$patientName['suffix'] = $this->patient->person->suffix;

		$providerName = array();
		$providerName['prefix'] = $this->user->person->prefix;
		$providerName['given'] = $this->user->person->firstName;
		$providerName['family'] = $this->user->person->lastName;
		$building = $this->building;
		$buildingName = $building->displayName;

		$realmCode = $xml->addChild('realmCode');
		$realmCode->addAttribute('code','US');
		$typeId = $xml->addChild('typeId');
		$typeId->addAttribute('root','2.16.840.1.113883.1.3');
		$typeId->addAttribute('extension','POCD_HD000040');

		$templateId = $xml->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.27.1776');
		$templateId->addAttribute('assigningAuthorityName','CDA/R2');
		$templateId = $xml->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.3');
		$templateId->addAttribute('assigningAuthorityName','HL7/CDT Header');
		$templateId = $xml->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.1.1');
		$templateId->addAttribute('assigningAuthorityName','IHE/PCC');
		$templateId = $xml->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.32.1');
		$templateId->addAttribute('assigningAuthorityName','HITSP/C32');
		$id = $xml->addChild('id');
		$id->addAttribute('root','2.16.840.1.113883.3.72');
		$id->addAttribute('extension','HITSP_C32v2.5');
		$id->addAttribute('assigningAuthorityName','ClearHealth');
		$code = $xml->addChild('code');
		$code->addAttribute('code','34133-9');
		$displayName = 'Summarization of episode note';
		$code->addAttribute('displayName',$displayName);
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$code->addAttribute('codeSystemName','LOINC');
		$xml->addChild('title',$this->_title);
		$effectiveTime = $xml->addChild('effectiveTime');
		$dateEffective = self::formatDate();
		$effectiveTime->addAttribute('value',$dateEffective);
		$confidentialityCode = $xml->addChild('confidentialityCode');
		$confidentialityCode->addAttribute('code','N');
		//$confidentialityCode->addAttribute('codeSystem','2.16.840.1.113883.5.25');
		$languageCode = $xml->addChild('languageCode');
		$languageCode->addAttribute('code','en-US');

		// RECORD TARGET
		$recordTarget = $xml->addChild('recordTarget');
		$patientRole = $recordTarget->addChild('patientRole');
		$id = $patientRole->addChild('id');
		//$id->addAttribute('root','CLINICID');
		$id->addAttribute('root','MRN');
		//$id->addAttribute('extension','PatientID');
		$id->addAttribute('extension',$this->patient->recordNumber);
		// Address
		$address = new Address();
		$address->personId = $this->_patientId;
		$addressIterator = $address->getIteratorByPersonId();
		foreach ($addressIterator as $address) {
			break; // retrieves the top address
		}
		$addr = $patientRole->addChild('addr');
		if ($address->addressId > 0) {
			$addr->addAttribute('use','HP');
			$addr->addChild('streetAddressLine',(strlen($address->line2) > 0)?$address->line1.' '.$address->line2:$address->line1);
			$addr->addChild('city',$address->city);
			$addr->addChild('state',$address->state);
			$addr->addChild('postalCode',$address->zipCode);
		}
		// Telecom
		$phone = null;
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $this->_patientId;
		foreach ($phoneNumber->getPhoneNumbers(false) as $phone) {
			break; // retrieves the top phone
		}
		$telecom = $patientRole->addChild('telecom');
		if ($phone && strlen($phone['number']) > 0) {
			$telecom->addAttribute('use','HP');
			$telecom->addAttribute('value','tel:'.$phone['number']);
		}
		// Patient
		$patient = $patientRole->addChild('patient');
		$name = $patient->addChild('name');
		$name->addChild('given',$patientName['given']);
		$name->addChild('family',$patientName['family']);
		$name->addChild('suffix',$patientName['suffix']);

		$genderCode = $patient->addChild('administrativeGenderCode');
		$genderCode->addAttribute('code',$this->patient->person->gender);
		$genderCode->addAttribute('displayName',$this->patient->person->displayGender);
		$genderCode->addAttribute('codeSystem','2.16.840.1.113883.5.1');
		$genderCode->addAttribute('codeSystemName','HL7 AdministrativeGender');
		$birthTime = $patient->addChild('birthTime');
		$birthTime->addAttribute('value',date('Ymd',strtotime($this->patient->person->dateOfBirth)));
		/*$maritalStatusCode = $patient->addChild('maritalStatusCode');
		$maritalStatusCode->addAttribute('code','');
		$maritalStatusCode->addAttribute('displayName','');
		$maritalStatusCode->addAttribute('codeSystem','2.16.840.1.113883.5.2');
		$maritalStatusCode->addAttribute('codeSystemName','HL7 Marital status');*/

		/*$languageCommunication = $patient->addChild('languageCommunication');
		$templateId = $languageCommunication->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.2');
		$templateId->addAttribute('assigningAuthorityName','HITSP/C83');
		$templateId = $languageCommunication->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.2.1');
		$templateId->addAttribute('assigningAuthorityName','IHE/PCC');
		$languageCode = $languageCommunication->addChild('languageCode');
		$languageCode->addAttribute('code','en-US');*/

		// AUTHOR
		$author = $xml->addChild('author');
		$time = $author->addChild('time');
		$timeValue = self::formatDate();
		$time->addAttribute('value',$timeValue);
		$assignedAuthor = $author->addChild('assignedAuthor');
		$id = $assignedAuthor->addChild('id');
		$id->addAttribute('root','20cf14fb-b65c-4c8c-a54d-b0cca834c18c');
		$addr = $assignedAuthor->addChild('addr');
		$addr->addAttribute('use','HP');
		$addr->addChild('streetAddressLine',(strlen($building->line2) > 0)?$building->line1.' '.$building->line2:$building->line1);
		$addr->addChild('city',$building->city);
		$addr->addChild('state',$building->state);
		$addr->addChild('postalCode',$building->zipCode);
		$telecom = $assignedAuthor->addChild('telecom');
		if (strlen($building->phoneNumber) > 0) {
			//$telecom->addAttribute('use','HP');
			$telecom->addAttribute('value','tel:'.$building->phoneNumber);
		}
		$assignedPerson = $assignedAuthor->addChild('assignedPerson');
		$name = $assignedPerson->addChild('name');
		$name->addChild('prefix',$providerName['prefix']);
		$name->addChild('given',$providerName['given']);
		$name->addChild('family',$providerName['family']);
		$representedOrg = $assignedAuthor->addChild('representedOrganization');
		$id = $representedOrg->addChild('id');
		$id->addAttribute('root','2.16.840.1.113883.19.5');
		$representedOrg->addChild('name',$buildingName);
		$address = $building->practice->primaryAddress;
		$telecom = $representedOrg->addChild('telecom');
		if (strlen($building->practice->mainPhone->number) > 0) {
			//$telecom->addAttribute('use','HP');
			$telecom->addAttribute('value','tel:'.$building->practice->mainPhone->number);
		}
		$addr = $representedOrg->addChild('addr');
		if ($address->addressId > 0) {
			$addr->addAttribute('use','HP');
			$addr->addChild('streetAddressLine',(strlen($address->line2) > 0)?$address->line1.' '.$address->line2:$address->line1);
			$addr->addChild('city',$address->city);
			$addr->addChild('state',$address->state);
			$addr->addChild('postalCode',$address->zipCode);
		}

		// CUSTODIAN
		$custodian = $xml->addChild('custodian');
		$assignedCustodian = $custodian->addChild('assignedCustodian');
		$representedOrg = $assignedCustodian->addChild('representedCustodianOrganization');
		$id = $representedOrg->addChild('id');
		$id->addAttribute('root','2.16.840.1.113883.19.5');
		$representedOrg->addChild('name','NIST Registry');
		$telecom = $representedOrg->addChild('telecom');
		$telecom->addAttribute('value','tel:+1-301-975-3251');
		$addr = $representedOrg->addChild('addr');
		$addr->addChild('streetAddressLine','100 Bureau Drive');
		$addr->addChild('city','Gaithersburg');
		$addr->addChild('state','MD');
		$addr->addChild('postalCode','20899');

		// PARTICIPANT
		$participant = $xml->addChild('participant');
		$participant->addAttribute('typeCode','IND');
		$associatedEntity = $participant->addChild('associatedEntity');
		$associatedEntity->addAttribute('classCode','GUAR');
		$id = $associatedEntity->addChild('id');
		$id->addAttribute('root','4ff51570-83a9-47b7-91f2-93ba30373141');
		$addr = $associatedEntity->addChild('addr');
		//$addr->addChild('streetAddressLine','17 Daws Rd.');
		//$addr->addChild('city','Blue Bell');
		//$addr->addChild('state','MA');
		//$addr->addChild('postalCode','02368');
		$telecom = $associatedEntity->addChild('telecom');
		//$telecom->addAttribute('value','tel:(888)555-1212');
		$associatedPerson = $associatedEntity->addChild('associatedPerson');
		$name = $associatedPerson->addChild('name');
		//$name->addChild('given','Kenneth');
		//$name->addChild('family','Ross');

		// DOCUMENTATION OF
		$documentationOf = $xml->addChild('documentationOf');
		$serviceEvent = $documentationOf->addChild('serviceEvent');
		$serviceEvent->addAttribute('classCode','PCPR');
		$effectiveTime = $serviceEvent->addChild('effectiveTime');
		$low = $effectiveTime->addChild('low');
		$lowValue = date('Ymd');
		$low->addAttribute('value',$lowValue);
		$high = $effectiveTime->addChild('high');
		$highValue = date('Ymd',strtotime('+1 month'));
		$high->addAttribute('value',$highValue);

		// Performer
		foreach ($this->performers as $provider) {
			$performer = $serviceEvent->addChild('performer');
			$performer->addAttribute('typeCode','PRF');
			$templateId = $performer->addChild('templateId');
			$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.4');
			$templateId->addAttribute('assigningAuthorityName','HITSP C83');
			$templateId = $performer->addChild('templateId');
			$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.2.3');
			$templateId->addAttribute('assigningAuthorityName','IHE PCC');
			$functionCode = $performer->addChild('functionCode');
			$functionCode->addAttribute('code','PP');
			$functionCode->addAttribute('displayName','Primary Care Provider');
			$functionCode->addAttribute('codeSystem','2.16.840.1.113883.12.443');
			$functionCode->addAttribute('codeSystemName','Provider Role');
			$functionCode->addChild('originalText','Primary Care Provider');
			$time = $performer->addChild('time');
			$low = $time->addChild('low');
			$lowValue = date('Y');
			$low->addAttribute('value',$lowValue);
			$high = $time->addChild('high');
			$highValue = date('Ymd',strtotime('+1 month'));
			$high->addAttribute('value',$highValue);

			$assignedEntity = $performer->addChild('assignedEntity');
			$id = $assignedEntity->addChild('id');
			$id->addAttribute('extension','PseudoMD-'.$provider->personId);
			$id->addAttribute('root','2.16.840.1.113883.3.72.5.2');
			$id = $assignedEntity->addChild('id');
			$id->addAttribute('extension','999999999');
			$id->addAttribute('root','2.16.840.1.113883.4.6');
			// <code code="200000000X" displayName="Allopathic and Osteopathic Physicians" codeSystemName="Provider Codes" codeSystem="2.16.840.1.113883.6.101"/>
			$addr = $assignedEntity->addChild('addr');
			$address = new Address();
			$address->personId = $provider->personId;
			$addressIterator = $address->getIteratorByPersonId();
			foreach ($addressIterator as $address) {
				break; // retrieves the top address
			}
			if ($address->addressId > 0) {
				$addr->addAttribute('use','HP');
				$addr->addChild('streetAddressLine',(strlen($address->line2) > 0)?$address->line1.' '.$address->line2:$address->line1);
				$addr->addChild('city',$address->city);
				$addr->addChild('state',$address->state);
				$addr->addChild('postalCode',$address->zipCode);
			}
			$telecom = $assignedEntity->addChild('telecom');
			$phoneNumber = new PhoneNumber();
			$phoneNumber->personId = $provider->personId;
			foreach ($phoneNumber->getPhoneNumbers(false) as $phone) {
				break; // retrieves the top phone
			}
			if (strlen($phone['number']) > 0) {
				$telecom->addAttribute('use','HP');
				$telecom->addAttribute('value','tel:'.$phone['number']);
			}

			$assignedPerson = $assignedEntity->addChild('assignedPerson');
			$name = $assignedPerson->addChild('name');

			$name->addChild('prefix',$provider->person->prefix);
			$name->addChild('given',$provider->person->firstName);
			$name->addChild('family',$provider->person->lastName);
			$representedOrg = $assignedEntity->addChild('representedOrganization');
			$id = $representedOrg->addChild('id');
			$id->addAttribute('root','2.16.840.1.113883.3.72.5');
			$representedOrg->addChild('name');
			$telecom = $representedOrg->addChild('telecom');
			$addr = $representedOrg->addChild('addr');
			/*$representedOrg->addChild('name',$buildingName);
			$telecom = $representedOrg->addChild('telecom');
			if (strlen($building->practice->mainPhone->number) > 0) {
				$telecom->addAttribute('use','HP');
				$telecom->addAttribute('value','tel:'.$building->practice->mainPhone->number);
			}
			$addr = $representedOrg->addChild('addr');
			if ($address->addressId > 0) {
				$addr->addAttribute('use','HP');
				$addr->addChild('streetAddressLine',(strlen($address->line2) > 0)?$address->line1.' '.$address->line2:$address->line1);
				$addr->addChild('city',$address->city);
				$addr->addChild('state',$address->state);
				$addr->addChild('postalCode',$address->zipCode);
			}*/
		}
	}

	public function populateBody(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$structuredBody = $component->addChild('structuredBody');

		$this->populatePurpose($structuredBody);
		$this->populatePayers($structuredBody);
		$this->populateAdvanceDirectives($structuredBody);
		$this->populateFunctionalStatus($structuredBody);
		CCDProblems::populate($this,$structuredBody);
		$this->populateFamilyHistory($structuredBody);
		$this->populateSocialHistory($structuredBody);
		CCDAllergies::populate($this,$structuredBody);
		CCDMedications::populate($this,$structuredBody);
		$this->populateMedicalEquipment($structuredBody);
		$this->populateImmunizations($structuredBody);
		$this->populateVitalSigns($structuredBody);
		CCDResults::populate($this,$structuredBody);
		$this->populateProcedures($structuredBody);
		$this->populateEncounters($structuredBody);
		$this->populateCarePlan($structuredBody);
	}

	public function populatePurpose(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.13');
		$code = $section->addChild('code');
		$code->addAttribute('code','48764-5');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Summary Purpose');
		$section->addChild('text','Transfer of care');
	}

	public function populatePayers(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.9');
		// <!-- Payers section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','48768-6');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Payers');

		$rows = array();
		$insurancePrograms = InsuranceProgram::getInsurancePrograms();
		$insuredRelationship = new InsuredRelationship();
		$insuredRelationshipIterator = $insuredRelationship->getIteratorByPersonId($this->_patientId);
		foreach ($insuredRelationshipIterator as $item) {
			$company = '';
			$program = '';
			if (isset($insurancePrograms[$item->insuranceProgramId])) {
				$exp = explode('->',$insurancePrograms[$item->insuranceProgramId]);
				$company = $exp[0];
				$program = $exp[1];
			}
			$rows[] = '<tr>
					<td>'.$company.'</td>
					<td>'.$program.'</td>
					<td>'.$item->groupNumber.'</td>
					<td></td>
				</tr>';
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
					<thead>
						<tr>
							<th>Payer name</th>
							<th>Policy type / Coverage type</th>
							<th>Covered party ID</th>
							<th>Authorization(s)</th>
						</tr>
					</thead>
					<tbody>'.implode("\n",$rows).'</tbody>
				</table>';
		$section->addChild('text',$text);
		return;
		$entry = $section->addChild('entry');
		$entry->addAttribute('typeCode','DRIV');
		$act = $entry->addChild('act');
		$act->addAttribute('classCode','ACT');
		$act->addAttribute('moodCode','DEF');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.20');
	}

	public function populateAdvanceDirectives(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.1');
		// <!-- Advance directives section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','42348-3');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Advance Directives');
		$section->addChild('text','<table border="1" width="100%">
							<thead>
								<tr>
									<th>Directive</th>
									<th>Description</th>
									<th>Verification</th>
									<th>Supporting Document(s)</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Resuscitation status</td>
									<td>
										<content ID="AD1">Do not resuscitate</content>
									</td>
									<td>Dr. Robert Dolin, Nov 07, 1999</td>
									<td>
										<linkHtml href="AdvanceDirective.b50b7910-7ffb-4f4c-bbe4-177ed68cbbf3.pdf">Advance directive</linkHtml>
									</td>
								</tr>
							</tbody>
						</table>');
	}

	public function populateFunctionalStatus(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.5');
		// <!-- Functional status section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','47420-5');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Functional Status');
		$section->addChild('text','<table border="1" width="100%">
							<thead>
								<tr>
									<th>Functional Condition</th>
									<th>Effective Dates</th>
									<th>Condition Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Dependence on cane</td>
									<td>1998</td>
									<td>Active</td>
								</tr>
								<tr>
									<td>Memory impairment</td>
									<td>1999</td>
									<td>Active</td>
								</tr>
							</tbody>
						</table>');
	}

	public function populateFamilyHistory(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.4');
		// <!-- Family history section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','10157-6');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Family history');
		$section->addChild('text','<paragraph>Father (deceased)</paragraph>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>Diagnosis</th>
									<th>Age At Onset</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Myocardial Infarction (cause of death)</td>
									<td>57</td>
								</tr>
								<tr>
									<td>Hypertension</td>
									<td>40</td>
								</tr>
							</tbody>
						</table>
						<paragraph>Mother (alive)</paragraph>
						<table border="1" width="100%">
							<thead>
								<tr>
									<th>Diagnosis</th>
									<th>Age At Onset</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Asthma</td>
									<td>30</td>
								</tr>
							</tbody>
						</table>');
	}

	public function populateSocialHistory(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.15');
		// <!-- Social history section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','29762-2');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Social History');
		$section->addChild('text','<table border="1" width="100%">
							<thead>
								<tr>
									<th>Social History Element</th>
									<th>Description</th>
									<th>Effective Dates</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Cigarette smoking</td>
									<td>1 pack per day</td>
									<td>1947 - 1972</td>
								</tr>
								<tr>
									<td>"</td>
									<td>None</td>
									<td>1973 - </td>
								</tr>
								<tr>
									<td>Alcohol consumption</td>
									<td>None</td>
									<td>1973 - </td>
								</tr>
							</tbody>
						</table>');
	}

	public function populateMedicalEquipment(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.7');
		// <!-- Medical equipment section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','46264-8');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Medical Equipment');
		$section->addChild('text','<table border="1" width="100%">
							<thead>
								<tr>
									<th>Supply/Device</th>
									<th>Date Supplied</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Automatic implantable cardioverter/defibrillator</td>
									<td>Nov 1999</td>
								</tr>
								<tr>
									<td>Total hip replacement prosthesis</td>
									<td>1998</td>
								</tr>
								<tr>
									<td>Wheelchair</td>
									<td>1999</td>
								</tr>
							</tbody>
						</table>');
	}

	public function populateImmunizations(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.6');
		// <!-- Immunizations section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','11369-6');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Immunizations');
		$rows = array();
		$iterator = new PatientImmunizationIterator();
		$this->setFiltersDateRange($filters);
		$iterator->setFilter(array('patientId'=>$this->_patientId));
		foreach ($iterator as $immunization) {
			$status = 'Completed'; // TODO: where to get the status?
			$tr = '<tr>
					<td>'.$immunization->immunization.'</td>
					<td>'.date('M d, Y',strtotime($immunization->dateAdministered)).'</td>
					<td>'.$status.'</td>
				</tr>';
			$rows[] = $tr;
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
							<thead>
								<tr>
									<th>Vaccine</th>
									<th>Date</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>'.implode("\n",$rows).'</tbody>
						</table>';
		$section->addChild('text',$text);
	}

	public function populateVitalSigns(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.16');
		// <!-- Vital signs section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','8716-3');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Vital Signs');

		$filters = array('personId'=>$this->_patientId);
		$this->setFiltersDateRange($filters);
		$iterator = new VitalSignGroupsIterator();
		$iterator->setFilter($filters);
		$headers = array('<th align="right">Date / Time: </th>');
		$vitals = array();
		foreach ($iterator as $vsGroup) {
			$headers[$vsGroup->dateTime] = '<th>'.date('M d, Y',strtotime($vsGroup->dateTime)).'</th>';
			foreach ($vsGroup->vitalSignValues as $vital) {
				$vitals[$vital->vital][$vsGroup->dateTime] = $vital;
			}
		}
		$rows = array();
		$labelKeyValues = VitalSignTemplate::generateVitalSignsTemplateKeyValue();
		foreach ($labelKeyValues as $key=>$value) {
			if (!isset($vitals[$key])) continue;
			$tr ='<tr>
				<th align="left">'.$value.'</th>';
			foreach ($vitals[$key] as $dateTime=>$vital) {
				$tr .='<td>'.$vital->value.' '.$vital->units.'</td>';
			}
			$tr .= '</tr>';
			$rows[] = $tr;
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
					<thead>
						<tr>'.implode("\n",$headers).'</tr>
					</thead>
					<tbody>'.implode("\n",$rows).'</tbody>
				</table>';
		$section->addChild('text',$text);
	}

	public function populateProcedures(SimpleXMLELement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.12');
		// <!-- Procedures section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','47519-4');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Procedures');
		$rows = array();
		$filters = array('patientId'=>$this->_patientId);
		$this->setFiltersDateRange($filters);
		$iterator = new PatientProcedureIterator(null,false);
		$iterator->setFilters($filters);
		$ctr = 1;
		foreach ($iterator as $procedure) {
			$rows[] = '<tr>
					<td>
						<content ID="Proc'.$ctr++.'">'.$procedure->procedure.'</content>
					</td>
					<td>'.date('M d, Y',strtotime($procedure->dateTime)).'</td>
				</tr>';
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
					<thead>
						<tr>
							<th>Procedure</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>'.implode("\n",$rows).'</tbody>
				</table>';
		$section->addChild('text',$text);
	}

	public function populateEncounters(SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.3');
		// <!-- Encounters section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','46240-8');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Encounters');

		if ($this->visit !== null) {
			$visitIterator = array($this->visit);
		}
		else {
			$visitIterator = new VisitIterator();
			$visitIterator->setFilters(array('patientId'=>$this->_patientId));
		}
		$rows = array();
		foreach ($visitIterator as $visit) {
			$building = new Building();
			$building->buildingId = $visit->buildingId;
			$building->populate();
			$appointment = new Appointment();
			$appointment->appointmentId = $visit->appointmentId;
			$appointment->populate();
			$tr ='<tr>
					<td>'.$appointment->title.'</td>
					<td>'.$building->displayName.'</td>
					<td>'.date('M d, Y',strtotime($visit->dateOfTreatment)).'</td>
				</tr>';
			$rows[] = $tr;
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
					<thead>
						<tr>
							<th>Encounter</th>
							<th>Location</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>'.implode("\n",$rows).'</tbody>
				</table>';
		$section->addChild('text',$text);
	}

	public function populateCarePlan(SimpleXMLElement $xml) {
		// TODO: to be implemented
		return;
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.10');
		// <!-- Plan of Care section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','18776-5');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$section->addChild('title','Plan');
		$section->addChild('text','<table border="1" width="100%">
							<thead>
								<tr>
									<th>Planned Activity</th>
									<th>Planned Date</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Pulmonary function test</td>
									<td>April 21, 2000</td>
								</tr>
							</tbody>
						</table>');
	}

}
