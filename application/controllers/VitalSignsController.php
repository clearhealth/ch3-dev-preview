<?php
/*****************************************************************************
*       VitalSignsController.php
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


class VitalSignsController extends WebVista_Controller_Action {

	protected $_visit;
	protected $_patient;
	protected $_form;

        public function init() {
                $this->_session = new Zend_Session_Namespace(__CLASS__);
                $cprss = new Zend_Session_Namespace('CprsController');
		if (!isset($this->_session->patientId)) {
			$this->_session->patientId = $this->_getParam('patientId');
		}
		$patient = new Patient();
		$patient->setPersonId($this->_session->patientId);
		$patient->populate();
                $this->_patient = $patient;
                $this->_visit = $cprss->visit;
        }

	public function indexAction() {
		exit;
		$pat = new Patient();
		$pat->personId = 1983;
		$pat->populate();
		echo $pat->bmi;
		//var_dump(VitalSignGroup::getBMIVitalsForPatientId(1983));
		exit;
		$vitals = new VitalSignGroup();
		$vitalsIter = $vitals->getIterator();
		foreach ($vitalsIter as $vitals) {
			print_r($vitals->toString());
		}
		$this->render();
	}
	
	public function addAction() {
		$this->_form = new WebVista_Form(array('name' => 'vs-add-form'));
		$this->_form->setWindow('windowAddVitalSignsId');
		$this->_form->setWindowAction(WebVista_Form::WINDOW_CLOSE);
		$vitalSignTemplate = new VitalSignTemplate();
		$vitalSignTemplate->vitalSignTemplateId = 1;
		$vitalSignTemplate->populate();
		$template = simplexml_load_string($vitalSignTemplate->template);
                $this->_form->setAction(Zend_Registry::get('baseUrl') . "vital-signs.raw/process-add");
		$this->_buildForm($template);
		$element = $this->_form->createElement("hidden",'vitalSignTemplateId',array('value' => $vitalSignTemplate->vitalSignTemplateId));
                $element->setBelongsTo('vitalSignGroup');
                $this->_form->addElement($element);
		$this->view->form = $this->_form;
		$this->view->jsCallback = $this->_getParam('jsCallback','');
		$this->render();
	}
	
	function processAddAction() {
		$vitalSignGroup = new VitalSignGroup();
		$vitalSignGroup->populateWithArray($this->_getParam('vitalSignGroup'));
		$vitalSignGroup->dateTime = date('Y-m-d H:i:s');
		//$vitalSignGroup->visitId = $this->_visit->visitId;
		$vitalSignGroup->personId = $this->_patient->personId;
		$vitalSignGroup->enteringUserId = (int)Zend_Auth::getInstance()->getIdentity()->userId;
		$vitalSignGroup->persist();
		$this->view->action('set-active-patient','cprs',null,array('personId' => $this->_patient->personId));
	}

	function _buildForm($template) {
		foreach ($template as $vital) {
                        $elements = array();

                        $element = $this->_form->createElement("checkbox","unavailable");
			$element->setBelongsTo('vitalSignGroup[vitalSignValues]['. (string)$vital->attributes()->label.']');
                        $this->_form->addElement($element);
                        $elements[] = "unavailable";

                        $element = $this->_form->createElement("checkbox","refused");
			$element->setBelongsTo('vitalSignGroup[vitalSignValues]['. (string)$vital->attributes()->label.']');
                        $this->_form->addElement($element);
                        $elements[] = "refused";

                        $elementName = preg_replace('/\./','_',(string)$vital->attributes()->title);
                        $element = $this->_form->createElement('hidden','vital', array('value' => $elementName));
			$element->setBelongsTo('vitalSignGroup[vitalSignValues]['. (string)$vital->attributes()->label.']');
                        $this->_form->addElement($element);
                        $elements[] = 'vital';

                        $element = $this->_form->createElement((string)$vital->attributes()->type,'value', array('label' => (string)$vital->attributes()->label));
			$element->setBelongsTo('vitalSignGroup[vitalSignValues]['. (string)$vital->attributes()->label.']');
                        $this->_form->addElement($element);
                        $elements[] = 'value';

			if ($vital->attributes()->units) {
                        	$element = $this->_form->createElement("select","units");
				$element->addMultiOptions(Enumeration::getEnumArray($vital->attributes()->units,"key","name"));
				$element->setBelongsTo('vitalSignGroup[vitalSignValues]['. (string)$vital->attributes()->label.']');
                        	$this->_form->addElement($element);
                        	$elements[] = "units";
			}

                        $this->_form->addDisplayGroup($elements,(string)$vital->attributes()->label);
                } 
	}
	function listMostRecentAction() {
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;
		if (!is_object($this->_patient)) $json->direct(array());
                $personId = $this->_patient->personId;
                if (!$personId > 0) $json->direct(array());
                $db = Zend_Registry::get('dbAdapter');
                $vsSelect = $db->select()
                                        ->from('vitalSignGroups')
                                        ->joinUsing('vitalSignValues','vitalSignGroupId')
                                        ->where('vitalSignGroups.personId = ' . (int)$personId)
                                        ->where("vitalSignGroups.vitalSignGroupId = (select vitalSignGroups.vitalSignGroupId from vitalSignGroups where personId = " . (int)$personId . " order by vitalSignGroups.dateTime DESC limit 1)");


		//echo $vsSelect->__toString();exit;
                $vitalSigns = array();
		//var_dump($db->query($vsSelect)->fetchAll());exit;
                foreach($db->query($vsSelect)->fetchAll() as $row) {
                        $vitalSigns[] = array("id" => $row['vitalSignValueId'],"data" => array($row['vital'], '', $row['value'], $row['dateTime']));
                }

                $json->direct(array("rows" => $vitalSigns));

        }
}
