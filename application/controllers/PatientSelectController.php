<?php
/*****************************************************************************
*	PatientSelectController.php
*
*	Author:  ClearHealth Inc. (www.clear-health.com)	2009
*	
*	ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*	respective logos, icons, and terms are registered trademarks 
*	of ClearHealth Inc.
*
*	Though this software is open source you MAY NOT use our 
*	trademarks, graphics, logos and icons without explicit permission. 
*	Derivitive works MUST NOT be primarily identified using our 
*	trademarks, though statements such as "Based on ClearHealth(TM) 
*	Technology" or "incoporating ClearHealth(TM) source code" 
*	are permissible.
*
*	This file is licensed under the GPL V3, you can find
*	a copy of that license by visiting:
*	http://www.fsf.org/licensing/licenses/gpl.html
*	
*****************************************************************************/


class PatientSelectController extends WebVista_Controller_Action
{
    protected $_session;

    public function init()
    {
        $this->_session = new Zend_Session_Namespace(__CLASS__);
    }

    public function indexAction()  {

        $this->render();
    }
	public function autoCompleteAction() {
        	$match = trim($this->getRequest()->getQuery('patientSelect', ''));
		$match = preg_replace('/[^a-zA-Z-0-9]/','',$match);
		$matches = array();
		if (!strlen($match) > 0) $this->_helper->autoCompleteDojo($matches);
		$db = Zend_Registry::get('dbAdapter');
		$patSelect = $db->select()
					->from('patient')
					->joinUsing('person','person_id')
					->where('person.inactive = 0 and last_name like ' . $db->quote($match.'%'))
					->orWhere('first_name like ' . $db->quote($match.'%'))
					->orWhere('record_number = ' . $db->quote($match))
					->orWhere('identifier = ' . $db->quote($match))
					->order('person.last_name ASC')
					->limit(50);
		//trigger_error($patSelect->__toString(),E_USER_NOTICE);
		//var_dump($db->query($patSelect)->fetchAll());exit;
		foreach($db->query($patSelect)->fetchAll() as $row) {
			$matches[$row['person_id']] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . substr($row['middle_name'],0,1) . ' #' . $row['record_number']; 
		}
		//var_dump($matches);exit;
		//$matches = array("name1" => $match, "name2" =>"value3");
        	$this->_helper->autoCompleteDojo($matches);
	}

	public function detailAction() {
        	$personId = (int)$this->_getParam('personId');
		if (!$personId > 0) $this->_helper->autoCompleteDojo($personId);
		$db = Zend_Registry::get('dbAdapter');
		$patient = new Patient();
		$patient->personId = (int) $personId;
		$patient->populate();
		$patient->person->populate();
		$outputArray = $patient->toArray();
		$outputArray['displayGender'] = $patient->displayGender;
		$outputArray['age'] = $patient->age;
		$acj = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $acj->suppressExit = true;
                $acj->direct($outputArray);
	}
}
