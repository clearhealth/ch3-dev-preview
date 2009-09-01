<?php
/*****************************************************************************
*       AclController.php
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


class AclController extends WebVista_Controller_Action {

	protected $_memcache = null;
	protected $_aclMemKey = 'aclList';
	protected $_chkLabelRead = 'chkRead';
	protected $_chkLabelWrite = 'chkWrite';
	protected $_chkLabelDelete = 'chkDelete';
	protected $_chkLabelOther = 'chkOther';

	public function init() {
		$this->_memcache = Zend_Registry::get('memcache');
	}

	public function indexAction() {
		$this->view->chkLabelRead = $this->_chkLabelRead;
		$this->view->chkLabelWrite = $this->_chkLabelWrite;
		$this->view->chkLabelDelete = $this->_chkLabelDelete;
		$this->view->chkLabelOther = $this->_chkLabelOther;
		$this->render();
	}

	public function listItemsAction() {
		$rows = array();
		$items = $this->_memcache->get($this->_aclMemKey); // get returns FALSE if error or key not found
		if ($items === false) {
			calcTS();
			trigger_error("before generating list: " . calcTS(),E_USER_NOTICE);
			$items = WebVista_Acl::getInstance()->getLists();
			$this->_memcache->set($this->_aclMemKey,$items);
			trigger_error("after generating list: " .calcTS(),E_USER_NOTICE);
		}
		foreach ($items as $moduleName=>$data) {
			foreach ($data as $id=>$resources) {
				$resourceName = $resources['name'];
				$tmp = array();
				$tmp['id'] = $id;
				// Resource
				$tmp['data'][] = $resources['prettyName'];
				// Read
				$tmp['data'][] = implode("<br />\n",$this->_generateCheckboxInputs($this->_chkLabelRead,$resources['read'],$resourceName));
				// Write
				$tmp['data'][] = implode("<br />\n",$this->_generateCheckboxInputs($this->_chkLabelWrite,$resources['write'],$resourceName));
				// Delete
				$tmp['data'][] = implode("<br />\n",$this->_generateCheckboxInputs($this->_chkLabelDelete,$resources['delete'],$resourceName));
				// Other
				$tmp['data'][] = implode("<br />\n",$this->_generateCheckboxInputs($this->_chkLabelOther,$resources['other'],$resourceName));
				$rows[] = $tmp;
			}
		}

		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function toolbarXmlAction() {
		$this->view->xmlHeader = '<?xml version=\'1.0\' encoding=\'iso-8859-1\'?>' . "\n";
		$contentType = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/xml";
		header("Content-type: ". $contentType);
		$this->render();
	}

	public function reloadPermissionsAction() {
		$acl = WebVista_Acl::getInstance();
		// populate acl from db
		$acl->populate();
		// save to memcache
		$this->_memcache->set('acl',$acl);
		Zend_Registry::set('acl',$acl);

		$items = $acl->getLists();
		$this->_memcache->set($this->_aclMemKey,$items);
		ACLAPI::saveACLItems($items);

		$data = array();
		$data['msg'] = __('Permissions reload successfully.');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function getMenuXmlAction() {
		$this->view->xmlHeader = '<?xml version=\'1.0\' encoding=\'iso-8859-1\'?>' . "\n";
		$contentType = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/xml";
		header("Content-type: ". $contentType);
		$this->render();
	}

	public function getRolesXmlAction() {
		$this->view->xmlHeader = '<?xml version=\'1.0\' encoding=\'iso-8859-1\'?>' . "\n";
		$contentType = (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) ? "application/xhtml+xml" : "text/xml";
		header("Content-type: ". $contentType);
		$this->render();
	}

	public function ajaxSavePermissionAction() {
		$chkName = $this->_getParam('name');
		$value = $this->_getParam('value');
		$template = $this->_getParam('template');
		$access = $this->_getParam('access');
		if ($access == 'all') {
			// save all permission
		}
		else {
			$eAccess = explode('_',$access);
			$resourceName = $eAccess[0]; // controller name
			$permissionName = $eAccess[1]; // action name
			// individual save
		}
		$data = array();
		$data['msg'] = __('Permissions save successfully.');
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	protected function _generateCheckboxInputs($name,Array $values,$resourceName) {
		$ret = array();
		foreach ($values as $value) {
			$ret[] = '<input type="checkbox" name="'.$name.'" value="'.$resourceName.'_'.$value['name'].'" onClick="toggleItem(this)" /> '.$value['prettyName'];
		}
		return $ret;
	}

}

