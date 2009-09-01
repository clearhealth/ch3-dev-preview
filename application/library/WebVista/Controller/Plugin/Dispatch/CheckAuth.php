<?php
/*****************************************************************************
*       CheckAuth.php
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

class WebVista_Controller_Plugin_Dispatch_CheckAuth extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
		$auth = Zend_Auth::getInstance();
		$publicPages = array();
		$publicPages['controllers'] = array('login',);
		$publicPages['actions'] = array();
		$controllerName = $request->getControllerName();
		$actionName = $request->getActionName();
		if ($auth->hasIdentity() || in_array($controllerName,$publicPages['controllers'])) {
			return true;
		}
		if ($actionName != 'index' && $controllerName != 'index' && !isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="Unauthorize Access Prohibited"');
                	header('HTTP/1.0 401 Unauthorized');
		}
		if (false && isset($_SERVER['PHP_AUTH_USER'])) {
			$_POST['username'] = $_SERVER['PHP_AUTH_USER'];
			$_POST['password'] = $_SERVER['PHP_AUTH_PW'];
			$zvah = new Zend_View_Helper_Action();
			$zvah->action('process','login');
			if ($auth->hasIdentity() || in_array($controllerName,$publicPages['controllers'])) {
                        	return true;
                	}
		}

		throw new WebVista_App_AuthException('You must be authenticated to access the system.');

		$roleId = $auth->getIdentity()->roleId;
		$acl = WebVista_Acl::getInstance();
 
		if (!$acl->hasRole($roleId)) {
	  	    $error = "Sorry, the requested user role '".$roleId."' does not exist";									
	  	}
	  	if (!$acl->has($request->getModuleName().'_'.$request->getControllerName())) {
			$error = "Sorry, the requested controller '".$request->getControllerName()."' does not exist as an ACL resource";
 		}
		if (!$acl->isAllowed($roleId, $request->getModuleName().'_'.$request->getControllerName(), $request->getActionName())) {
			$error = "Sorry, the page you requested does not exist or you do not have access";
		}
 
		if (isset($error)) {
			throw new WebVista_App_AuthException('You must be authenticated to access the system.');
		}
    }
}
