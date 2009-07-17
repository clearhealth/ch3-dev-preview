<?php
/*****************************************************************************
*	Action.php
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


class WebVista_Controller_Action extends Zend_Controller_Action {
    public function preDispatch() {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        $auth = Zend_Auth::getInstance();
        $currentUser = "Anonymous";
	if ($auth->hasIdentity()) {
		$currentUser = $auth->getIdentity()->username;
	}
	/*
	else {
		$user = new User();
                $user->username = $_SESSION['frame']['me']->_objects['user']->username;
                $user->userId = $_SESSION['frame']['me']->_objects['user']->id;
                Zend_Auth::getInstance()
                 ->getStorage()
                 ->write($user);
	}
	*/

        $view->authenticated = $auth->hasIdentity();

        $view->user = new WebVista_Model_User($auth->getIdentity());

	$request = Zend_Controller_Front::getInstance();

        $view->baseUrl = $request->getBaseUrl();
        $view->doctype('XHTML1_STRICT');
        $view->headTitle()->setSeparator(' / ');
        $view->headScript()->setAllowArbitraryAttributes(true);

        $view->headTitle(ucwords($request->getRequest()->getControllerName()));
        $view->headTitle(ucwords($request->getRequest()->getActionName()));
        //$currentUser = "Anonymous";
        $view->headTitle("Connected as " . $currentUser);

        $view->timerTimeout = Zend_Registry::get('sessionTimeout');
	if ($this->getRequest()->getControllerName() == 'login' &&
	    $this->getRequest()->getActionName() != 'complete') {
		return;
	}
	$cssUrl = $view->baseUrl . '/cache-file.raw/css?files=dojocss,dhtmlxcss';
	$view->headLink()->appendStylesheet($cssUrl);

	$view->headScript()->appendScript("function getBaseUrl() { return '{$view->baseUrl}'; }");

	$jsUrl = $view->baseUrl . '/cache-file.raw/js?files=dojojs,dhtmlxjs';
	$view->headScript()->appendFile($jsUrl);

	$this->view->baseUrl = $view->baseUrl;
    }

	public static function buildJSJumpLink($objectId,$patientId,$objectClass) {
		$js = <<<EOL
// check if mainTabbar object exists
if (typeof mainTabbar != "undefined") {
	// check if tabId exists
	var tabId = 'tab_{$objectClass}';
	var tab = mainTabbar._getTabById(tabId);
	if (tab) {
		// check if mainController object exists
		if (typeof mainController != "undefined") {
			// set active patientId
			mainController.setActivePatient('{$patientId}');
		}
		mainTabbar.setTabActive(tabId); // tabName should be dynamic
	}
}

EOL;
		return $js;
	}

    public function __call($name, $args)
    {
        throw new Exception('Sorry, the requested action does not exist');
    }
}
