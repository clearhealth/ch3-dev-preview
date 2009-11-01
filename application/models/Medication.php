<?php
/*****************************************************************************
*       Medication.php
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

 
class Medication extends WebVista_Model_ORM implements Document {
 
  protected $medicationId;
  protected $hipaaNDC;
  protected $type = 'OPM';
  protected $personId;
  protected $patientReported;
  protected $substitution;
  protected $dateBegan;
  protected $datePrescribed;
  protected $description;
  protected $comment;
  protected $directions;
  protected $prescriberPersonId;
  protected $quantity;
  protected $dose;
  protected $route;
  protected $priority;
  protected $schedule;
  protected $prn;
  protected $transmit;
  protected $dateTransmitted;
  protected $pharmacyId;
  protected $daysSupply;
  protected $strength;
  protected $unit;
  protected $refills;
  protected $rxnorm;
  protected $eSignatureId;
  protected $_table = "medications";
  protected $_primaryKeys = array("medicationId");

	public function getDisplayStatus() {
		$status = __("Unsigned");
		if ($this->eSignatureId > 0) {
			$status = __("Signed");
			switch ($this->transmit) {
				case 'ePrescribe':
					if ($this->dateTransmitted == '0000-00-00 00:00:00') { $status =  __("Pending ePrescription") ; }
					else { $status = __("ePrescription Sent " . date('Y-m-d',$this->dateTransmitted)); }
					break;
				case 'print':
					if ($this->dateTransmitted == '0000-00-00 00:00:00') { $status =  __("Not Yet Printed") ; }
					else { $status = __("Printed " . date('Y-m-d',$this->dateTransmitted)); }
					break;
				case 'fax':
					if ($this->dateTransmitted == '0000-00-00 00:00:00') { $status = __("Not Yet Faxed"); }
					else { $status = __("Faxed " . date('Y-m-d',$this->dateTransmitted)); }
					break;
				default:
					break;
			}
		}
		return $status;
	}

	function getRefillsRemaining() {
		return $this->refills;
	}
	function getSummary() {
                return $this->description;
        }

        function getDocumentId() {
                return $this->medicationId;
        }
        function setDocumentId($id) {
                $this->medicationId = (int)$id;
        }

        function getContent() {
                return "";
        }

        static function getPrettyName() {
                return "Medications";
        }

        function setSigned($eSignatureId) {
                $this->eSignatureId = (int)$eSignatureId;
                $this->persist();
        }

	public static function getControllerName() {
		return "MedicationsController";
	}
 
}
