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
		if ($eSignature->objectClass != 'Medication') {
			return $data;
		}
		$data['_audit'] = $audit;
		$medication = new Medication();
		$medication->medicationId = (int)$eSignature->objectId;
		$medication->populate();
		$data['PrescriberOrderNumber'] = $medication->medicationId;

		$medData = array();
		$medData['DrugDescription'] = $medication->description;
		$medData['Strength'] = $medication->strength;
		$medData['StrengthUnits'] = $medication->unit;
		$medData['Quantity'] = $medication->quantity;
		$medData['Directions'] = $medication->directions;
		$medData['Refills'] = $medication->refills;
		$medData['Substitutions'] = $medication->substitution;
		$medData['WrittenDate'] = date('Ymd',strtotime($medication->datePrescribed));
		$data['medication'] = $medData;

		$pharmacy = new Pharmacy();
		$pharmacy->pharmacyId = $medication->pharmacyId;
		$pharmacy->populate();

		$pharmacyData = array();
		$pharmacyData['NCPDPID'] = $pharmacy->NCPDPID;
		$pharmacyData['StoreName'] = $pharmacy->StoreName;
		$pharmacyData['AddressLine1'] = $pharmacy->AddressLine1.' '.$pharmacy->AddressLine2;
		$pharmacyData['City'] = $pharmacy->City;
		$pharmacyData['State'] = $pharmacy->State;
		$pharmacyData['ZipCode'] = $pharmacy->Zip;
		$pharmacyData['PhoneNumber'] = $pharmacy->PhonePrimary;
		$data['pharmacy'] = $pharmacyData;
		$provider = new Provider();
		$provider->personId = $medication->prescriberPersonId;
		$provider->populate();
		$prescriberData = array();
		$prescriberData['DEANumber'] = $provider->deaNumber;
		$prescriberData['SPI'] = $provider->sureScriptsSPI;
		$prescriberData['ClinicName'] = '';
		$prescriberData['LastName'] = $provider->person->lastName;
		$prescriberData['FirstName'] = $provider->person->firstName;
		$prescriberData['Suffix'] = '';
		$address = new Address();
		$address->personId = $provider->personId;
		$address->populateWithPersonId();
		$prescriberData['AddressLine1'] = $address->line1.' '.$address->line2;
		$prescriberData['City'] = $address->city;
		$prescriberData['State'] = 'AZ'; //$address->state;
		$prescriberData['ZipCode'] = $address->postalCode;
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $provider->personId;
		$phoneNumber->populateWithPersonId();
		$prescriberData['PhoneNumber'] = $phoneNumber->number;
		$data['prescriber'] = $prescriberData;

		$patient = new Patient();
		$patient->personId = $medication->personId;
		$patient->populate();
		$patientData = array();
		$patientData['LastName'] = $patient->person->lastName;
		$patientData['FirstName'] = $patient->person->firstName;

		$enumeration = new Enumeration();
		$enumeration->enumerationId = $patient->person->gender;
		$enumeration->populate();
		$gender = $enumeration->key;

		$patientData['Gender'] = $gender;
		$patientData['DateOfBirth'] = date('Ymd',strtotime($patient->person->dateOfBirth));
		$address = new Address();
		$address->personId = $patient->personId;
		$address->populateWithPersonId();
		$patientData['AddressLine1'] = $address->line1.' '.$address->line2;
		$patientData['City'] = $address->city;
		$patientData['State'] = 'AZ'; //$address->state;
		$patientData['ZipCode'] = $address->postalCode;
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $patient->personId;
		$phoneNumber->populateWithPersonId();
		$patientData['PhoneNumber'] = $phoneNumber->number;
		$data['patient'] = $patientData;
		return $data;

		$ret = array();
		foreach ($data as $type=>$row) {
			if (is_array($row)) {
				foreach ($row as $field=>$value) {
					$key = $type.'['.$field.']';
					$ret[$key] = $value;
				}
			}
			else {
				$ret[$type] = $row;
			}
		}
		return $ret;
	}

	public static function handlerSSAct(Audit $audit,Array $sourceData) {
		if ($audit->objectClass != 'ESignature') {
			return false;
		}
		$eSignature = new ESignature();
		$eSignature->eSignatureId = $audit->objectId;
		$eSignature->populate();
		if ($eSignature->objectClass != 'Medication') {
			return false;
		}
		$audit = $sourceData['_audit'];
		$messaging = new Messaging();
		$messaging->messagingId = (int)$eSignature->objectId;
		$messaging->populate();
		$messaging->objectId = $messaging->messagingId;
		$messaging->objectClass = $audit->objectClass;
		$messaging->status = 'Prescribed';
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
			$pharmacyData['AddressLine1'] = $pharmacy->AddressLine1.' '.$pharmacy->AddressLine2;
			$pharmacyData['City'] = $pharmacy->City;
			$pharmacyData['State'] = $pharmacy->State;
			$pharmacyData['ZipCode'] = $pharmacy->Zip;
			$pharmacyData['PhoneNumber'] = $pharmacy->PhonePrimary;
			$sourceData['pharmacy'] = $pharmacyData;
		}

		$query = http_build_query($sourceData);
		$ch = curl_init();
		$ePrescribeURL = Zend_Registry::get('config')->healthcloud->URL;
		$ePrescribeURL .= 'sure-scripts-manager.raw/new-rx?apiKey='.Zend_Registry::get('config')->healthcloud->apiKey;
		curl_setopt($ch,CURLOPT_URL,$ePrescribeURL);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$query);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		$output = curl_exec($ch);
		$error = '';
		if (!curl_errno($ch)) {
			try {
				$responseXml = simplexml_load_string($output);
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
			}
			catch (Exception $e) {
				$error = __("There was an error connecting to HealthCloud to prescribe new medication. Please try again or contact the system administrator.");
				trigger_error("There was an error prescribeing new medication, the response couldn't be parsed as XML: " . $output, E_USER_NOTICE);
			}
		}
		else {
			$error = __("There was an error connecting to HealthCloud to prescribe new medication. Please try again or contact the system administrator.");
			trigger_error("Curl error connecting to healthcare prescribed new medication: " . curl_error($ch),E_USER_NOTICE);
		}
		curl_close ($ch);
		$messaging->status = 'Prescribe Sent';
		$ret = true;
		if (strlen($error) > 0) {
			$messaging->status = 'Prescribe Error';
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

	public static function handlereFaxSourceData(Audit $audit) {
		$data = array();
		if ($audit->objectClass != 'ESignature') {
			return $data;
		}
		$eSignature = new ESignature();
		$eSignature->eSignatureId = $audit->objectId;
		$eSignature->populate();
		if ($eSignature->objectClass != 'Medication') {
			return $data;
		}

		$data['_audit'] = $audit;
		$medication = new Medication();
		$medication->medicationId = $eSignature->objectId;
		$medication->populate();

		$data['transmissionId'] = (int)$medication->medicationId;
		$data['recipients'] = array();
		$patient = new Patient();
		$patient->personId = $medication->personId;
		$patient->populate();
		$pharmacyId = $patient->defaultPharmacyId;

		$provider = new Provider();
		$provider->personId = $medication->prescriberPersonId;
		$provider->populate();

		// recipients MUST be a pharmacy?
		$pharmacy = new Pharmacy();
		$pharmacy->pharmacyId = $pharmacyId;
		$pharmacy->populate();
		//$data['recipients'][] = array('fax'=>$pharmacy->Fax,'name'=>$pharmacy->StoreName,'company'=>$pharmacy->StoreName);
		// temporarily comment out the above recipient and use the hardcoded recipient
		$data['recipients'][] = array('fax'=>'6022976632','name'=>'Jay Walker','company'=>'ClearHealth Inc.');

		$prescription = new Prescription();
		$prescription->prescriberName = $provider->firstName.' '.$provider->lastName.' '.$provider->title;
		$prescription->prescriberStateLicenseNumber = $provider->stateLicenseNumber;
		$prescription->prescriberDeaNumber = $provider->deaNumber;

		// Practice Info
		$primaryPracticeId = $provider->primaryPracticeId;
		$practice = new Practice();
		$practice->id = $primaryPracticeId;
		$practice->populate();
		$address = $practice->primaryAddress;
		$prescription->practiceName = $practice->name;
		$prescription->practiceAddress = $address->line1.' '.$address->line2;
		$prescription->practiceCity = $address->city;
		$prescription->practiceState = $address->state;
		$prescription->practicePostalCode = $address->postalCode;

		$attachment = new Attachment();
		$attachment->attachmentReferenceId = $provider->personId;
		$attachment->populateWithAttachmentReferenceId();
		if ($attachment->attachmentId > 0) {
			$db = Zend_Registry::get('dbAdapter');
			$sqlSelect = $db->select()
					->from('attachmentBlobs')
					->where('attachmentId = ?',(int)$attachment->attachmentId);
			if ($row = $db->fetchRow($sqlSelect)) {
				$tmpFile = tempnam('/tmp','ch30_sig_');
				file_put_contents($tmpFile,$row['data']);
				$signatureFile = $tmpFile;
				$prescription->prescriberSignature = $signatureFile;
			}
		}

		$prescription->patientName = $patient->lastName.', '.$patient->firstName;
		$address = $patient->homeAddress;
		$prescription->patientAddress = $address->line1.' '.$address->line2;
		$prescription->patientCity = $address->city;
		$prescription->patientState = $address->state;
		$prescription->patientPostalCode = $address->postalCode;
		$prescription->patientDateOfBirth = date('m/d/Y',strtotime($patient->dateOfBirth));
		$prescription->medicationDatePrescribed = date('m/d/Y',strtotime($medication->datePrescribed));
		$prescription->medicationDescription = $medication->description;
		$prescription->medicationComment = $medication->comment;
		$prescription->medicationQuantity = $medication->quantity;
		$prescription->medicationRefills = $medication->refills;
		$prescription->medicationDirections = $medication->directions;
		$prescription->medicationSubstitution = $medication->substitution;
		$prescription->create();

		$filename = $prescription->imageFile;
		$fileType = pathinfo($filename,PATHINFO_EXTENSION);
		$data['files'] = array();
		$contents = file_get_contents($filename);
		unlink($filename);
		$data['files'][] = array('contents'=>base64_encode($contents),'type'=>$fileType);
		return $data;
	}

	public static function handlereFaxAct(Audit $audit,Array $sourceData) {
		if ($audit->objectClass != 'ESignature') {
			return false;
		}
		$eSignature = new ESignature();
		$eSignature->eSignatureId = $audit->objectId;
		$eSignature->populate();
		if ($eSignature->objectClass != 'Medication') {
			return false;
		}

		$medication = new Medication();
		$medication->medicationId = $eSignature->objectId;
		$medication->populate();

		$audit = $sourceData['_audit'];
		$messaging = new Messaging(Messaging::TYPE_OUTBOUND_FAX);
		$messaging->messagingId = (int)$sourceData['transmissionId'];
		$messaging->transmissionId = $messaging->messagingId;
		$messaging->populate();
		$messaging->objectId = $messaging->messagingId;
		$messaging->objectClass = $audit->objectClass;
		$messaging->status = 'Faxed';
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->auditId = $audit->auditId; // this must be required for retransmission in case of error
		$messaging->persist();

		$efax = new eFaxOutbound();
		$url = Zend_Registry::get('config')->healthcloud->eFax->outboundUrl;
		$url .= '?apiKey='.Zend_Registry::get('config')->healthcloud->apiKey;
		$efax->setUrl($url);

		$efax->setTransmissionId($sourceData['transmissionId']);
		$efax->setNoDuplicate(eFaxOutbound::NO_DUPLICATE_ENABLE);
		$efax->setDispositionMethod('POST');
		// use the default disposition URL
		$dispositionUrl = Zend_Registry::get('config')->healthcloud->eFax->dispositionUrl;
		$efax->setDispositionUrl($dispositionUrl);

		//$efax->setDispositionMethod('EMAIL');
		//$efax->addDispositionEmail('Arthur Layese','arthur@layese.com');
		foreach ($sourceData['recipients'] as $recipient) {
			if ($messaging->resend && strlen($messaging->faxNumber) > 9) { // supersedes fax number from messaging
				$recipient['fax'] = $messaging->faxNumber;
			}
			$efax->addRecipient($recipient['fax'],$recipient['name'],$recipient['company']);
		}
		foreach ($sourceData['files'] as $file) {
			$efax->addFile($file['contents'],$file['type']);
		}

		$ret = $efax->send();
		if (!$ret) {
			$messaging->status = 'Fax Error';
			$messaging->note = implode(PHP_EOL,$efax->getErrors());
		}
		else {
			$messaging->docid = $efax->getDocId();
			$messaging->status = 'Fax Sent';
			$messaging->note = '';
		}
		if ($messaging->resend) {
			$messaging->resend = 0;
		}
		$messaging->retries++;
		$messaging->dateStatus = date('Y-m-d H:i:s');
		$messaging->persist();
		return true;
	}

}
