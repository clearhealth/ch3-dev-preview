<?php
/*****************************************************************************
*       MainToolbarController.php
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
 * MainToolbar controller
 */
class MainToolbarController extends WebVista_Controller_Action {

	public $_patient;
	public $_visit;

	/**
	 * Default action to dispatch
	 */
	public function indexAction() {
                $personId = (int)$this->_getParam('personId', 0);
                $visitId = (int)$this->_getParam('visitId', 0);
		$this->_setActivePatient($personId,$visitId);

		// ALERTS
		$personId = Zend_Auth::getInstance()->getIdentity()->personId;
		$team = new TeamMember();
		$teamId = $team->getTeamByPersonId($personId);
		$ctr = 0;
		if (strlen($teamId) > 0) {
			$alert = new GeneralAlert();
			$alertIterator = $alert->getIteratorByTeam($teamId);
			foreach ($alertIterator as $item) {
				$ctr++;
			}
		}
		if ($ctr > 0) {
			$this->view->alerts = $ctr;
		}

		$this->view->xmlHeader = '<?xml version=\'1.0\' encoding=\'iso-8859-1\'?>' . "\n";
		$contentType = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/xml";
		header("Content-type: ". $contentType);
		$this->render();
	}

	public function _setActivePatient($personId,$visitId) {
		if (!$personId > 0) return;
		$memcache = Zend_Registry::get('memcache');
                $patient = new Patient();
                $patient->personId = (int)$personId;
                $patient->populate();
		$patient->person->populate();
                $this->_patient = $patient;
		$this->view->patient = $this->_patient;

		$mostRecentRaw = $memcache->get('mostRecent');
		$currentUserId = (int)Zend_Auth::getInstance()->getIdentity()->userId;
		$personId = $patient->personId;
		$teamId = $patient->teamId;
		if ($mostRecentRaw === false) {
			$mostRecent = array();
		}
		else {
			$mostRecent = unserialize($mostRecentRaw);
		}
		if (!array_key_exists($currentUserId,$mostRecent)) {
			$mostRecent[$currentUserId] = array();
		}
		if (array_key_exists($personId,$mostRecent[$currentUserId])) {
			unset($mostRecent[$currentUserId][$personId]);
		}
		$name = $patient->person->last_name . ', ' . $patient->person->first_name . ' ' . substr($patient->person->middle_name,0,1) . ' #' . $patient->record_number;
		$mostRecent[$currentUserId][$patient->personId] = array('name'=>$name,'teamId'=>$teamId);
		$memcache->set('mostRecent',serialize($mostRecent));

		if (strlen($patient->teamId) > 0) {

			$name = TeamMember::ENUM_PARENT_NAME;
			$enumeration = new Enumeration();
			$enumeration->populateByEnumerationName($name);

			$enumerationsClosure = new EnumerationsClosure();
			$rowset = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);

			$patientEnumerationId = 0;
			foreach ($rowset as $row) {
				if ($patient->teamId == $row->key) {
					$patientEnumerationId = $row->enumerationId;
					break;
				}
			}

			if ($patientEnumerationId !== 0) {
				$this->view->team = TeamMember::generateTeamTree($patientEnumerationId);
			}

		}

		// POSTINGS
		$postings = array();
		$patientAllergy = new PatientAllergy();
		$patientAllergyIterator = $patientAllergy->getIteratorByPatient($personId);
		foreach ($patientAllergyIterator as $allergy) {
			$postings[] = $allergy->toArray();
		}
		$this->view->postings = $postings;

		//REMINDERS
		$ctr = 0;
		$hsa = new HealthStatusAlert();
                $hsaIterator = $hsa->getIteratorByStatusWithPatientId('active',$personId);
		foreach ($hsaIterator as $row) {
			$ctr++;
		}
		if ($ctr > 0) {
			$this->view->reminders = $ctr;
		}

		// VISITS
                //$this->_visit = null;
		if (!$visitId > 0) {
			return;
		}
		$visit = new Visit();
		$visit->encounter_id = (int)$visitId;
		$visit->populate();
		$this->_visit = $visit;
		$this->view->visit = $this->_visit;
        }

}
