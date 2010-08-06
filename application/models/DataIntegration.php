<?php
/*****************************************************************************
*       DataIntegration.php
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


class DataIntegration extends WebVista_Model_ORM {

	protected $handlerType;

	public function __construct($handlerType = 0) {
		parent::__construct();
		$this->handlerType = (int)$handlerType;
	}

	public function getIterator($dbSelect = null) {
		if ($dbSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$dbSelect = $db->select()
					->from($this->_table)
					->where('handlerType = ?',$this->handlerType)
					->order('name');
		}
		return parent::getIterator($dbSelect);
	}

	public static function handlerSSSourceData(Audit $audit) {
		$data = array();
		if ($audit->objectClass != 'ESignature') {
			return $data;
		}
		$eSignature = new ESignature();
		$eSignature->eSignatureId = $audit->objectId;
		$eSignature->populate();
		if ($eSignature->objectClass != 'Medication' || !strlen($eSignature->signature) > 0) {
			return $data;
		}

		// MEDICATION DATA
		$medication = new Medication();
		$medication->medicationId = (int)$eSignature->objectId;
		$medication->populate();
		if ($medication->transmit != 'ePrescribe' || $medication->isScheduled()) {
			return $data;
		}

		$data['_audit'] = $audit;
		$uuid = uuid_create();
		$data['messageId'] = str_replace('-','',$uuid);
		$data['prescriberOrderNumber'] = $medication->medicationId.'_'.$audit->auditId;
		$data['rxReferenceNumber'] = $medication->rxReferenceNumber;

		$medData = array();
		$medData['description'] = $medication->description;
		//$medData['strength'] = $medication->strength;
		//$dose = '';
		//if (preg_match('/^[0-9]*/',trim($medication->dose),$matches)) {
		//	$dose = $matches[0];
		//}
		//$medData['strength'] = $dose;
		$medData['strength'] = $medication->dose;
		$qualifiers = Medication::listQuantityQualifiersMapping();
		$medData['strengthUnits'] = $qualifiers[$medication->quantityQualifier];// temporarily set to the same with quantity
		//$qty = '';
		//if (preg_match('/^[0-9]*/',trim($medication->quantity),$matches)) {
		//	$qty = $matches[0];
		//}
		//$medData['quantity'] = $qty;
		$medData['quantity'] = $medication->quantity;
		$medData['quantityUnits'] = $qualifiers[$medication->quantityQualifier];
		$medData['daysSupply'] = $medication->daysSupply;
		$medData['directions'] = $medication->directions;
		$qualifier = 'R';
		if ($medication->prn) {
			$qualifier = 'PRN';
		}
		$medData['refills'] = $medication->refills;
		$medData['refillsUnits'] = $qualifier;
		$medData['substitutions'] = ($medication->substitution)?'0':'1';
		$writtenDate = date('Ymd',strtotime($medication->datePrescribed));
		if ($medication->datePrescribed == '0000-00-00 00:00:00') {
			$writtenDate = '';
		}
		$medData['writtenDate'] = $writtenDate;
		$medData['productCode'] = $medication->hipaaNDC;
		$medData['productQualifier'] = 'ND';
		$medData['dosageForm'] = DataTables::getDosageForm($medication->chmedDose);
		$medData['drugDBCode'] = $medication->pkey;
		$medData['drugDBQualifier'] = ''; //'pkey'; valid options: "E|G|FG|FS|MC|MD|MG|MM"
		$medData['note'] = $medication->comment;
		$data['Medication'] = $medData;

		// PHARMACY DATA
		$pharmacy = new Pharmacy();
		$pharmacy->pharmacyId = $medication->pharmacyId;
		$pharmacy->populate();

		$pharmacyData = array();
		$pharmacyData['NCPDPID'] = $pharmacy->NCPDPID;
		$pharmacyData['fileId'] = $pharmacy->pharmacyId;
		$pharmacyData['NPI'] = $pharmacy->NPI;
		$pharmacyData['storeName'] = $pharmacy->StoreName;
		$pharmacyData['storeNumber'] = $pharmacy->StoreNumber;
		$pharmacyData['email'] = $pharmacy->Email;
		$pharmacyData['twentyFourHourFlag'] = $pharmacy->TwentyFourHourFlag;
		$pharmacyData['crossStreet'] = $pharmacy->CrossStreet;
		$pharmacyData['addressLine1'] = $pharmacy->AddressLine1;
		$pharmacyData['addressLine2'] = $pharmacy->AddressLine2;
		$pharmacyData['city'] = $pharmacy->City;
		$pharmacyData['state'] = $pharmacy->State;
		$pharmacyData['zip'] = $pharmacy->Zip;
		$phones = array();
		$phones[] = array('number'=>$pharmacy->PhonePrimary,'type'=>'TE');
		$phones[] = array('number'=>$pharmacy->Fax,'type'=>'FX');
		$phones[] = array('number'=>$pharmacy->PhoneAlt1,'type'=>$pharmacy->PhoneAlt1Qualifier);
		$phones[] = array('number'=>$pharmacy->PhoneAlt2,'type'=>$pharmacy->PhoneAlt2Qualifier);
		$phones[] = array('number'=>$pharmacy->PhoneAlt3,'type'=>$pharmacy->PhoneAlt3Qualifier);
		$phones[] = array('number'=>$pharmacy->PhoneAlt4,'type'=>$pharmacy->PhoneAlt4Qualifier);
		$phones[] = array('number'=>$pharmacy->PhoneAlt5,'type'=>$pharmacy->PhoneAlt5Qualifier);
		$pharmacyData['phones'] = $phones;
		$data['Pharmacy'] = $pharmacyData;

		// PRESCRIBER DATA
		$provider = new Provider();
		$provider->personId = $medication->prescriberPersonId;
		$provider->populate();
		$prescriberData = array();
		$prescriberData['DEANumber'] = $provider->deaNumber;
		$prescriberData['SPI'] = $provider->sureScriptsSPI;
		// it has conflicts with DEANumber
		//$prescriberData['stateLicenseNumber'] = $provider->stateLicenseNumber;
		$prescriberData['fileId'] = $provider->personId;
		$prescriberData['clinicName'] = '';

		$identifierType = $provider->identifierType;
		if (strlen($identifierType) > 0) {
		//	$prescriberData[$identifierType] = $provider->identifier;
		}
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $provider->personId;
		$prescriberData['phones'] = $phoneNumber->phoneNumbers;

		$prescriberData['lastName'] = $provider->person->lastName;
		$prescriberData['firstName'] = $provider->person->firstName;
		$prescriberData['middleName'] = $provider->person->middleName;
		$prescriberData['suffix'] = $provider->person->suffix;
		$prescriberData['prefix'] = '';
		$prescriberData['email'] = $provider->person->email;
		$prescriberData['specialtyCode'] = $provider->specialty;
		$specialtyQualifier = '';
		if (strlen($provider->specialty) > 0) {
			$specialtyQualifier = 'AM';
		}
		$prescriberData['specialtyQualifier'] = $specialtyQualifier;
		$address = new Address();
		$address->personId = $provider->personId;
		$address->populateWithType('MAIN');
		$prescriberData['addressLine1'] = $address->line1;
		$prescriberData['addressLine2'] = $address->line2;
		$prescriberData['city'] = $address->city;
		$prescriberData['state'] = $address->state;
		$prescriberData['zip'] = $address->zipCode;
		$data['Prescriber'] = $prescriberData;

		// PATIENT DATA
		$patient = new Patient();
		$patient->personId = $medication->personId;
		$patient->populate();
		$patientData = array();
		$patientData['lastName'] = $patient->person->lastName;
		$patientData['firstName'] = $patient->person->firstName;
		$patientData['middleName'] = $patient->person->middleName;
		$patientData['suffix'] = $patient->person->suffix;
		$patientData['prefix'] = '';
		$patientData['email'] = $patient->person->email;
		$patientData['fileId'] = $patient->recordNumber;
		$patientData['medicareNumber'] = ''; // TODO: to be implemented

		$identifierType = $patient->identifierType;
		if (strlen($identifierType) > 0) {
			$patientData[$identifierType] = $patient->identifier;
		}

		$enumeration = new Enumeration();
		$enumeration->enumerationId = $patient->person->gender;
		$enumeration->populate();
		$gender = $enumeration->key;

		$patientData['gender'] = $gender;
		$dateOfBirth = date('Ymd',strtotime($patient->person->dateOfBirth));
		if ($patient->person->dateOfBirth == '0000-00-00') {
			$dateOfBirth = '';
		}
		$patientData['dateOfBirth'] = $dateOfBirth;
		$address = new Address();
		$address->personId = $patient->personId;
		$address->populateWithType('MAIN');
		$patientData['addressLine1'] = $address->line1;
		$patientData['addressLine2'] = $address->line2;
		$patientData['city'] = $address->city;
		$patientData['state'] = $address->state;
		$patientData['zip'] = $address->zipCode;
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $patient->personId;
		$patientData['phones'] = $phoneNumber->phoneNumbers;
		$data['Patient'] = $patientData;

		// CHECK for attending/supervisor
		$attendingId = TeamMember::getAttending($patient->teamId);
		if ($attendingId > 0) {
			// SUPERVISOR
			$provider = new Provider();
			$provider->personId = $attendingId;
			$provider->populate();
			$supervisorData = array();
			$supervisorData['DEANumber'] = $provider->deaNumber;
			$supervisorData['SPI'] = $provider->sureScriptsSPI;
			// it has conflicts with DEANumber
			//$supervisorData['stateLicenseNumber'] = $provider->stateLicenseNumber;
			$supervisorData['fileId'] = $provider->personId;
			$supervisorData['clinicName'] = '';

			$identifierType = $provider->identifierType;
			if (strlen($identifierType) > 0) {
			//	$prescriberData[$identifierType] = $provider->identifier;
			}
			$phoneNumber = new PhoneNumber();
			$phoneNumber->personId = $provider->personId;
			$supervisorData['phones'] = $phoneNumber->phoneNumbers;
	
			$supervisorData['lastName'] = $provider->person->lastName;
			$supervisorData['firstName'] = $provider->person->firstName;
			$supervisorData['middleName'] = $provider->person->middleName;
			$supervisorData['suffix'] = $provider->person->suffix;
			$supervisorData['prefix'] = '';
			$supervisorData['email'] = $provider->person->email;
			$supervisorData['specialtyCode'] = $provider->specialty;
			$specialtyQualifier = '';
			if (strlen($provider->specialty) > 0) {
				$specialtyQualifier = 'AM';
			}
			$supervisorData['specialtyQualifier'] = $specialtyQualifier;
			$address = new Address();
			$address->personId = $provider->personId;
			$address->populateWithType('MAIN');
			$supervisorData['addressLine1'] = $address->line1;
			$supervisorData['addressLine2'] = $address->line2;
			$supervisorData['city'] = $address->city;
			$supervisorData['state'] = $address->state;
			$supervisorData['zip'] = $address->zipCode;
			$data['Supervisor'] = $supervisorData;
		}

		return $data;
	}

	public static function handlerSSAct(Audit $audit,Array $sourceData) {
		if (!isset($sourceData['_audit']) || $audit->objectClass != 'ESignature') {
			return false;
		}
		$eSignature = new ESignature();
		$eSignature->eSignatureId = $audit->objectId;
		$eSignature->populate();
		if ($eSignature->objectClass != 'Medication' || !strlen($eSignature->signature) > 0) {
			return false;
		}

		$medication = new Medication();
		$medication->medicationId = (int)$eSignature->objectId;
		$medication->populate();
		$medication->dateTransmitted = date('Y-m-d H:i:s');
		$medication->persist();

		$patientInfo = $sourceData['Patient']['lastName'].', '.$sourceData['Patient']['firstName'].' '.$sourceData['Patient']['middleName'].' MRN#'.$sourceData['Patient']['fileId'];
		$patientInfo .= ' - '.$sourceData['Medication']['description'].' #'.date('m/d/Y',strtotime($sourceData['Medication']['writtenDate']));

		$audit = $sourceData['_audit'];
		unset($sourceData['_audit']);
		$messaging = new Messaging();
		$messaging->messagingId = $sourceData['messageId'];
		$messaging->messageType = 'NewRx';
		$messaging->populate();
		$messaging->objectId = (int)$eSignature->objectId;
		$messaging->objectClass = $audit->objectClass;
		$messaging->status = 'Sending';
		$messaging->note = 'Sending newRx ('.$patientInfo.')';
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->auditId = $audit->auditId; // this must be required for retransmission in case of error
		$messaging->persist();

		if ($messaging->resend && $messaging->pharmacyId  > 0) { // supersedes pharmacy from messaging
			$pharmacy = new Pharmacy();
			$pharmacy->pharmacyId = $messaging->pharmacyId;
			$pharmacy->populate();

			$pharmacyData = array();
			$pharmacyData['NCPDPID'] = $pharmacy->NCPDPID;
			$pharmacyData['StoreName'] = $pharmacy->StoreName;
			$pharmacyData['addressLine1'] = $pharmacy->AddressLine1;
			$pharmacyData['addressLine2'] = $pharmacy->AddressLine2;
			$pharmacyData['city'] = $pharmacy->City;
			$pharmacyData['state'] = $pharmacy->State;
			$pharmacyData['zip'] = $pharmacy->Zip;
			$pharmacyData['phone'] = $pharmacy->PhonePrimary;
			$pharmacyData['fax'] = '';
			$sourceData['Pharmacy'] = $pharmacyData;
		}

		$query = http_build_query(array('data'=>$sourceData));
		$ch = curl_init();
		$ePrescribeURL = Zend_Registry::get('config')->healthcloud->URL;
		$ePrescribeURL .= 'ss-manager.raw/new-rx?apiKey='.Zend_Registry::get('config')->healthcloud->apiKey;
		curl_setopt($ch,CURLOPT_URL,$ePrescribeURL);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$query);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_USERPWD,'admin:ch3!');
		$output = curl_exec($ch);
		$error = '';
		$messaging->status = 'Sent';
		$messaging->note = 'newRx sent';
		trigger_error('RESPONSE:'.$output,E_USER_NOTICE);
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

		$messaging->note .= ' ('.$patientInfo.')';
		curl_close ($ch);
		$ret = true;
		if (strlen($error) > 0) {
			$messaging->status = 'Error';
			$messaging->note = $error;
			$ret = false;
		}
		if ($messaging->resend) {
			$messaging->resend = 0;
		}
		$messaging->retries++;
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->persist();
		return $ret;
	}

	public function getNormalizedName() {
		return Handler::normalizeHandlerName($this->name);
	}

}
