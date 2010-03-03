<?php
/*****************************************************************************
*       FacilitiesController.php
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
 * Facilities controller
 */
class FacilitiesController extends WebVista_Controller_Action {

	public function editPracticeAction() {
		$id = (int)$this->_getParam('id');
		$enumerationId = (int)$this->_getParam('enumerationId');
		$orm = new Practice();
		if ($id > 0) {
			$orm->practiceId = $id;
			$orm->populate();
		}
		$form = new WebVista_Form(array('name'=>'edit-practice'));
		$form->setAction(Zend_Registry::get('baseUrl').'facilities.raw/process-edit-practice');
		$form->loadORM($orm,'Practice');
		$form->setWindow('windowEditPracticeId');
		$this->view->form = $form;
		$this->view->enumerationId = $enumerationId;
		$this->render('edit-practice');
	}

	function processEditPracticeAction() {
		$enumerationId = (int)$this->_getParam('enumerationId');
		$params = $this->_getParam('practice');
		$id = (int)$params['id'];
		$params['id'] = $id;

		$orm = $this->_populateAndPersist('Practice',$id,$params,$enumerationId);

		$msg = __('Record Saved for Practice: ') . ucfirst($params['name']);
		$data = array();
		$data['msg'] = $msg;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function editBuildingAction() {
		$id = (int)$this->_getParam('id');
		$enumerationId = (int)$this->_getParam('enumerationId');
		$enumerationsClosure = new EnumerationsClosure();
		$parentId = $enumerationsClosure->getParentById($enumerationId);
		$enumeration = new Enumeration();
		$enumeration->enumerationId = $parentId;
		$enumeration->populate();
		$orm = new Building();
		if ($id > 0) {
			$orm->buildingId = $id;
			$orm->populate();
		}
		$orm->practiceId = $enumeration->ormId;
		$form = new WebVista_Form(array('name'=>'edit-building'));
		$form->setAction(Zend_Registry::get('baseUrl').'facilities.raw/process-edit-building');
		$form->loadORM($orm,'Building');
		$form->setWindow('windowEditBuildingId');
		$this->view->form = $form;
		$this->view->enumerationId = $enumerationId;
		$this->render('edit-building');
	}

	function processEditBuildingAction() {
		$enumerationId = (int)$this->_getParam('enumerationId');
		$params = $this->_getParam('building');
		$id = (int)$params['id'];
		$params['id'] = $id;

		$orm = $this->_populateAndPersist('Building',$id,$params,$enumerationId);

		$msg = __('Record Saved for Building: ') . ucfirst($params['name']);
		$data = array();
		$data['msg'] = $msg;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function editRoomAction() {
		$id = (int)$this->_getParam('id');
		$enumerationId = (int)$this->_getParam('enumerationId');
		$enumerationsClosure = new EnumerationsClosure();
		$parentId = $enumerationsClosure->getParentById($enumerationId);
		$enumeration = new Enumeration();
		$enumeration->enumerationId = $parentId;
		$enumeration->populate();
		$orm = new Room();
		if ($id > 0) {
			$orm->roomId = $id;
			$orm->populate();
		}
		$orm->buildingId = $enumeration->ormId;
		$form = new WebVista_Form(array('name'=>'edit-room'));
		$form->setAction(Zend_Registry::get('baseUrl').'facilities.raw/process-edit-room');
		$form->loadORM($orm,'Room');
		$form->setWindow('windowEditRoomId');
		$this->view->form = $form;

		$routingStations = LegacyEnum::getEnumArray('routing_stations');
		$routingStations = array_merge(array('' => ''),$routingStations);
		$this->view->colors = Room::getColorList();
		$this->view->routingStations = $routingStations;
		$this->view->enumerationId = $enumerationId;
		$this->render('edit-room');
	}

	function processEditRoomAction() {
		$enumerationId = (int)$this->_getParam('enumerationId');
		$params = $this->_getParam('room');
		$id = (int)$params['id'];
		$params['id'] = $id;

		$orm = $this->_populateAndPersist('Room',$id,$params,$enumerationId);

		$msg = __('Record Saved for Room: ') . ucfirst($params['name']);
		$data = array();
		$data['msg'] = $msg;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	private function _populateAndPersist($class,$id,$data,$enumerationId) {
		// this method assumes that is being called in this controller only and that $class is valid and exists
		$orm = new $class();
		if ($id > 0) {
			$orm->id = $id;
			$orm->populate();
		}
		$orm->populateWithArray($data);
		$orm->persist();

		if (!$id > 0 && $enumerationId > 0) {
			$enumeration = new Enumeration();
			$enumeration->enumerationId = $enumerationId;
			$enumeration->populate();
			$enumeration->ormId = $orm->id;
			$enumeration->persist();
		}
		return $orm;
	}

}

