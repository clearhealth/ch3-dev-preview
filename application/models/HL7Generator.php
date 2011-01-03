<?php
/*****************************************************************************
*       HL7Generator.php
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


class HL7Generator {

	protected static function _generateHL7XML($data) {
		$hl7 = new HL7XML($data);
		$hl7->parse();
		return $hl7->xml->asXML();
	}

	public static function generatePatient($patientId) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('audits','COUNT(*) AS ctr')
				->where("objectClass = 'Patient' AND objectId = ?",(int)$patientId);
		$new = true;
		if ($row = $db->fetchRow($sqlSelect)) {
			if ($row['ctr'] > 1) {
				$new = false;
			}
		}
		if ($new) {
			$ret = self::generatePatientNew($patientId);
		}
		else {
			$ret = self::generatePatientUpdate($patientId);
		}
		file_put_contents('/tmp/patient.xml',$ret);
		return $ret;
	}

	public static function generatePatientNew($patientId) {
		$data = 'MSH|^~\&|||||ADT^A04'."\n".self::generatePatientSegments($patientId);
		file_put_contents('/tmp/patient-new.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generatePatientUpdate($patientId) {
		$data = 'MSH|^~\&|||||ADT^A08'."\n".self::generatePatientSegments($patientId);
		file_put_contents('/tmp/patient-update.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generatePatientSegments($patientId) {
		return self::generatePID($patientId); // temporarily set as alias of generatePID()
	}

	public static function generatePID($patientId) {
		$patient = new Patient();
		$patient->personId = (int)$patientId;
		$patient->populate();

		$phoneHome = '';
		$phoneBusiness = '';
		$phoneNumber = new PhoneNumber();
		$phoneNumber->personId = $patient->personId;
		$phones = $phoneNumber->getPhoneNumbers(false);
		foreach ($phones as $phone) {
			if ($phoneHome == '' && $phone['type'] == 'HP') {
				$phoneHome = $phone['number'];
			}
			if ($phoneBusiness == '' && $phone['type'] == 'TE') {
				$phoneBusiness = $phone['number'];
			}
		}

		/* most efficient way to create PID?
		$patientName = $patient->person->lastName.'^'.$patient->person->firstName.'^'.strtoupper(substr($patient->person->middleName,0,1));
		$addr = $patient->homeAddress;
		$address = $addr->line1.'^'.$addr->line2.'^'.$addr->city.'^'.$addr->state.'^'.$addr->zipCode;
		// reference: http://www.med.mun.ca/tedhoekman/medinfo/hl7/ch300056.htm
		$data = array();
		$data[] = 'PID';
		$data[] = ''; // 1: Set ID
		$data[] = ''; // 2: Patient ID (External)
		$data[] = $patient->recordNumber; // 3: Patient ID (Internal)
		$data[] = ''; // 4: Alternate Patient ID
		$data[] = $patientName; // 5: Patient Name
		$data[] = ''; // 6: Mother's Maiden Name
		$data[] = date('Ymd',strtotime($patient->person->dateOfBirth)); // 7: Data/Time of Birth
		$data[] = $patient->person->gender; // 8: Sex
		$data[] = ''; // 9: Patient Alias
		$data[] = ''; // 10: Race
		$data[] = $address; // 11: Patient Address
		$data[] = ''; // 12: Country Code
		$data[] = $phoneHome; // 13: Phone Number (Home)
		$data[] = $phoneBusiness; // 14: Phone Number (Business)
		$data[] = ''; // 15: Primary Language
		$data[] = $patient->person->maritalStatus; // 16: Marital Status
		$data[] = ''; // 17: Religion
		$data[] = ''; // 18: Patient Account Number
		$data[] = $patient->person->identifier; // 19: Patient SSS Number
		*/

		$data = array();
		$data['mrn'] = $patient->recordNumber;
		$data['lastName'] = $patient->person->lastName;
		$data['firstName'] = $patient->person->firstName;
		$data['middleInitial'] = strtoupper(substr($patient->person->middleName,0,1));
		$data['dateOfBirth'] = date('Ymd',strtotime($patient->person->dateOfBirth));
		$data['gender'] = $patient->person->gender;
		$address = $patient->homeAddress; // 2.x
		// fall back for 3.x
		if (!$address->addressId > 0) {
			$address = new Address();
			$address->personId = $patient->personId;
			$addressIterator = $address->getIteratorByPersonId();
			foreach ($addressIterator as $address) {
				break; // retrieves the top address
			}
		}
		$data['addressLine1'] = $address->line1;
		$data['addressLine2'] = $address->line2;
		$data['addressCity'] = $address->city;
		$data['addressState'] = $address->state;
		$data['addressZip'] = $address->zipCode;
		$data['phoneHome'] = $phoneHome;
		$data['phoneBusiness'] = $phoneBusiness;
		$data['ssn'] = $patient->person->identifier;

		return 'PID|||'.$data['mrn'].'||'.$data['lastName'].'^'.$data['firstName'].'^'.$data['middleInitial'].'||'.$data['dateOfBirth'].'|'.$data['gender'].'||'.$data['addressLine1'].'^'.$data['addressLine2'].'^'.$data['addressCity'].'^'.$data['addressState'].'^'.$data['addressZip'].'||'.$data['phoneHome'].'|'.$data['phoneBusiness'].'|||||'.$data['ssn'];
	}

	public static function generatePV1($appointment) {
		if (!$appointment instanceOf Appointment) {
			$appointmentId = (int)$appointment;
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();
		}
		$providerId = (int)$appointment->providerId;
		$provider = new Provider();
		$provider->personId = $providerId;
		$provider->populate();
		$data = array();
		$data['drId'] = $provider->deaNumber;
		$data['drLastName'] = $provider->person->lastName;
		$data['drFirstName'] = $provider->person->firstName;
		$data['drMiddleInitial'] = strtoupper(substr($provider->person->middleName,0,1));
		return 'PV1|1|O|||||'.$data['drId'].'^'.$data['drLastName'].'^'.$data['drFirstName'].'^'.$data['drMiddleInitial'];
	}

	public static function generateAppointment($appointmentId) {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('audits','COUNT(*) AS ctr')
				->where("objectClass = 'Appointment' AND objectId = ?",(int)$appointmentId);
		$new = true;
		if ($row = $db->fetchRow($sqlSelect)) {
			if ($row['ctr'] > 1) {
				$new = false;
			}
		}
		if ($new) {
			$ret = self::generateAppointmentNew($appointmentId);
		}
		else {
			// check appointment if cancel or no show
			$appointment = new Appointment();
			$appointment->appointmentId = (int)$appointmentId;
			$appointment->populate();
			if ($appointment->appointmentCode == 'CAN') {
				$ret = self::generateAppointmentCancel($appointment);
			}
			else if ($appointment->appointmentCode == 'NS') {
				$ret = self::generateAppointmentNoShow($appointment);
			}
			else {
				$ret = self::generateAppointmentUpdate($appointment);
			}
		}
		file_put_contents('/tmp/appointment.xml',$ret);
		return $ret;
	}

	public static function generateAppointmentNew($appointment) {
		$data = 'MSH|^~\&|||||SIU^S12'."\n".self::generateAppointmentSegments($appointment);
		file_put_contents('/tmp/appointment-new.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generateAppointmentUpdate($appointment) {
		$data = 'MSH|^~\&|||||SIU^S14'."\n".self::generateAppointmentSegments($appointment);
		file_put_contents('/tmp/appointment-update.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generateAppointmentCancel($appointment) {
		$ret = array('MSH|^~\&|||||SIU^S15');
		$ret[] = self::generateSCH($appointment);
		$data = implode("\n",$ret);
		file_put_contents('/tmp/appointment-cancel.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generateAppointmentNoShow($appointment) {
		$ret = array('MSH|^~\&|||||SIU^S26');
		$ret[] = self::generateSCH($appointment);
		$data = implode("\n",$ret);
		file_put_contents('/tmp/appointment-no-show.hl7',$data);
		return self::_generateHL7XML($data);
	}

	public static function generateAppointmentSegments($appointment) {
		if (!$appointment instanceOf Appointment) {
			$appointmentId = (int)$appointment;
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();
		}
		$ret = array();
		$ret[] = self::generateSCH($appointment->appointmentId);
		$ret[] = self::generatePV1($appointment);
		$ret[] = self::generatePID($appointment->patientId);
		$ret[] = self::generateAIL($appointment);
		return implode("\n",$ret);
	}

	public static function generateSCH($appointment) {
		$fillerStatusCodes = array(
			'1'=>'Booked',
			'2'=>'Cancelled',
			'3'=>'No Show',
			'4'=>'Complete',
			'5'=>'Overbook',
			'6'=>'Blocked',
			'7'=>'Deleted',
			'8'=>'Started',
			'9'=>'Pending',
			'10'=>'Waitlist',
			'11'=>'DC',
		);
		if (!$appointment instanceOf Appointment) {
			$appointmentId = (int)$appointment;
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();
		}

		$statusCode = 1; // Default: Booked
		// check appointment if cancel or no show
		if ($appointment->appointmentCode == 'CAN') {
			$statusCode = 2; // Cancelled
		}
		else if ($appointment->appointmentCode == 'NS') {
			$statusCode = 3; // No Show
		}

		// reference: http://www.med.mun.ca/tedhoekman/medinfo/hl7/ch100060.htm
		$data = array();
		$data['appointmentId'] = $appointment->appointmentId;
		$data['appointmentReasonIdentifier'] = $appointment->reason;
		$data['appointmentReasonText'] = $appointment->title;
		$data['quantity'] = '';
		$data['interval'] = '';
		$data['duration'] = '';
		$data['start'] = date('YmdHi',strtotime($appointment->start));
		$data['end'] = date('YmdHi',strtotime($appointment->end));
		$data['statusCode'] = $statusCode;
		return 'SCH|'.$data['appointmentId'].'||||||'.$data['appointmentReasonIdentifier'].'^'.$data['appointmentReasonText'].'||||'.$data['quantity'].'^'.$data['interval'].'^'.$data['duration'].'^'.$data['start'].'^'.$data['end'].'||||||||||||||'.$data['statusCode'].'^'.$fillerStatusCodes[$data['statusCode']];
	}

	public static function generateAIL($appointment) {
		if (!$appointment instanceOf Appointment) {
			$appointmentId = (int)$appointment;
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();
		}
		$room = new Room();
		$room->roomId = $appointment->roomId;
		$room->populate();
		return 'AIL|'.$appointment->appointmentId.'||'.$room->name;
	}

}
