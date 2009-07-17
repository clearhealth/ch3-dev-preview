<?php
/*****************************************************************************
*	AdminPersonsController.php
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


class AdminPersonsController extends WebVista_Controller_Action
{
	protected $_form;
	protected $_person;
	
    public function indexAction() {
        $this->render();
    }

	public function autoCompleteAction() {
        	$match = $this->_getParam('name');
		$match = preg_replace('/[^a-zA-Z-0-9]/','',$match);
		$matches = array();
		if (!strlen($match) > 0) $this->_helper->autoCompleteDojo($matches);
		$db = Zend_Registry::get('dbAdapter');
		$patSelect = $db->select()
					->from('person')
					->joinLeftUsing('provider','person_id')
					->joinLeftUsing('user','person_id')
					->where('person.last_name like ' . $db->quote($match.'%'))
					->orWhere('person.first_name like ' . $db->quote($match.'%'))
					->orWhere('user.username like ' . $db->quote($match.'%'))
					->order('person.last_name DESC')
					->order('person.first_name DESC')
					->limit(50);
		//echo $patSelect->__toString();exit;
		//var_dump($db->query($patSelect)->fetchAll());exit;
		foreach($db->query($patSelect)->fetchAll() as $row) {
			$matches[$row['person_id']] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . substr($row['middle_name'],0,1) . ' (' . $row['username'] .")"; 
		}
		//var_dump($matches);exit;
		//$matches = array("name1" => $match, "name2" =>"value3");
        	$this->_helper->autoCompleteDojo($matches);
	}

	public function editAction() {
		$personId = (int)$this->_getParam('personId');
        	if (isset($this->_session->messages)) {
        	    $this->view->messages = $this->_session->messages;
        	}
		$this->_form = new WebVista_Form(array('name' => 'person-detail'));
		$this->_form->setAction(Zend_Registry::get('baseUrl') . "admin-persons.raw/edit-process");
		$this->_person = new Person();
		$this->_person->person_id = $personId;
		$this->_person->populate();
		$this->_form->loadORM($this->_person, "Person");
		//var_dump($this->_form);
		$this->view->form = $this->_form;
		$this->view->person = $this->_person;
        	$this->render('edit-person');
	}

	function editProcessAction() {
		$this->indexAction();
		//$this->_form->isValid($_POST);
		$this->_person->populateWithArray($_POST['person']);
		$this->_person->persist();
		$this->view->message = "Record Saved for Person: " . ucfirst($this->_person->first_name) . " " . ucfirst($this->_person->last_name);
        	$this->render('index');
	}
}
