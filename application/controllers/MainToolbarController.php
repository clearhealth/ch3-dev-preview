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
		$this->view->xmlHeader = '<?xml version=\'1.0\' encoding=\'iso-8859-1\'?>' . "\n";
		$contentType = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/xml";
		header("Content-type: ". $contentType);
		$this->render();
	}

	public function _setActivePatient($personId,$visitId) {
		if (!$personId > 0) return;
                $patient = new Patient();
                $patient->personId = (int)$personId;
                $patient->populate();
		$patient->person->populate();
                $this->_patient = $patient;
		$this->view->patient = $this->_patient;

		if ($patient->teamId > 0) {
			$teamMember = new TeamMember();
			$teamMember->teamId = $patient->teamId;
			$teamMember->populate();

			$name = $teamMember->role;
			if (strlen($name) > 0) {
				$enumeration = new Enumeration();
				$enumeration->populateByEnumerationName($name);
				$enumerationId = $enumeration->enumerationId;
				$this->view->team = TeamMember::generateTeamTree($enumerationId);
			}
		}

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
