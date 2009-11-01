<?php
/*****************************************************************************
*       GenericEditController.php
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
 * Generic Edit controller
 */
class GenericEditController extends WebVista_Controller_Action {

	protected $_ormObject = null;

	/**
	 * Default action to dispatch
	 */
	public function indexAction() {
		$enumerationId = (int)$this->_getParam("enumerationId");
		$enumeration = new Enumeration();
		$enumeration->enumerationId = $enumerationId;
		$enumeration->populate();
		$ormClass = $enumeration->ormClass;
		$ormId = $enumeration->ormId;
		$ormEditMethod = $enumeration->ormEditMethod;

		if (!class_exists($ormClass)) {
			throw new Exception("ORM Class {$ormClass} does not exists");
		}
		$ormObject = new $ormClass();
		if (!$ormObject instanceof ORM) {
			throw new Exception("ORM Class {$ormClass} is not an instance of an ORM");
		}
		if (strlen($ormEditMethod) > 0 && method_exists($ormObject,$ormEditMethod)) {
			$form = $ormObject->$ormEditMethod($ormId);
		}
		else {
			foreach ($ormObject->_primaryKeys as $key) {
				$ormObject->$key = $ormId;
			}
			$ormObject->populate();
			$form = new WebVista_Form(array('name' => 'edit-object'));
			$form->setAction(Zend_Registry::get('baseUrl') . "generic-edit.raw/process-edit?enumerationId={$enumerationId}");
			$form->loadORM($ormObject, "ormObject");
			$form->setWindow('windowEditORMObjectId');
		}
		$this->_ormObject = $ormObject;
		$this->view->ormObject = $this->_ormObject;
		$this->view->form = $form;
		$this->render('index');
	}

	public function processEditAction() {
		try {
			$this->indexAction();
		}
		catch (Exception $e) {
			throw $e;
		}
		$ormObject = $this->_getParam("ormObject");
		$this->_ormObject->populateWithArray($ormObject);
		$this->_ormObject->persist();
		$this->view->message = __('Object saved successfully');
		$this->render();
	}

	// a placeholder for generic edit popup, all manipulations are in javascripts
	public function codeEditorAction() {
		$jsVar = $this->_getParam('jsVar');
                $jsVar = preg_replace('/[^a-z_0-9- ]/i','',$jsVar);
                $jsVar = ltrim(preg_replace('/^(?P<digit>\d+)/','',$jsVar));
		$this->view->jsVar = $jsVar;
		$this->render();
	}

}

