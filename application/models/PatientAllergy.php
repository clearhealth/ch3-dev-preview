<?php
/*****************************************************************************
*       PatientAllergy.php
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


class PatientAllergy extends WebVista_Model_ORM {

	protected $patientAllergyId;
	protected $causativeAgent;
	protected $patientId;
	protected $observerId;
	protected $reactionType;
	protected $observed;
	protected $severity; // Severe, Moderate, Mild
	protected $dateTimeReaction;
	protected $dateTimeCreated;
	protected $symptoms;
	protected $comments;
	protected $noKnownAllergies;
	protected $enteredInError;
	protected $drugAllergy;

	protected $_primaryKeys = array('patientAllergyId');
	protected $_table = 'patientAllergies';

	const ENUM_REACTION_TYPE_PARENT_NAME = 'Reaction Type Preferences';
	const ENUM_SEVERITY_PARENT_NAME = 'Severity Preferences';
	const ENUM_SYMPTOM_PARENT_NAME = 'Symptom Preferences';

	public function getIteratorByPatient($patientId = null,$enteredInError = 0,$noKnownAllergies = null) {
		if ($patientId === null) {
			$patientId = $this->patientId;
		}
		$filters = array();
		$filters['patientId'] = (int)$patientId;
		$filters['enteredInError'] = (int)$enteredInError;
		if ($noKnownAllergies !== null) {
			$filters['patientId'] = (int)$noKnownAllergies;
		}
		$iterator = new PatientAllergyIterator();
		$iterator->setFilters($filters);
		return $iterator;
	}

}
