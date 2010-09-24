<?php
/*****************************************************************************
*       UsersController.php
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


class UsersController extends WebVista_Controller_Action {

	protected $user;
	protected $xmlPreferences = null;

	public function init() {
		$auth = Zend_Auth::getInstance();
		$user = new User();
		$user->personId = (int)$auth->getIdentity()->personId;
		$user->populateWithPersonId();
		if (strlen($user->preferences) > 0) {
			$this->xmlPreferences = new SimpleXMLElement($user->preferences);
		}
		$this->user = $user;
	}

	public function preferencesAction() {
		$mainTabs = Menu::getMainTabs($this->view->baseUrl);
		$data = array();
		$tabs = array();
		foreach ($mainTabs as $key=>$value) {
			$tabs[$key] = $key;
		}
		$this->view->tabs = $tabs;
		$visibleTabs = array();
		$defaultTab = '';
		$currentLocation = '';
		if ($this->xmlPreferences !== null) {
			foreach ($this->xmlPreferences->tabs as $tab) {
				$tab = (string)$tab;
				$visibleTabs[$tab] = $tab;
			}
			$defaultTab = (string)$this->xmlPreferences->defaultTab;
			$currentLocation = (string)$this->xmlPreferences->currentLocation;
		}
		$data['tabs'] = $visibleTabs;
		$data['defaultTab'] = $defaultTab;
		$data['currentLocation'] = $currentLocation;
		$this->view->data = $data;

		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice','Building', 'Room'));
		$facilities = array();
		foreach($facilityIterator as $facility) {
			$name = $facility['Practice']->name.'->'.$facility['Building']->name.'->'.$facility['Room']->name;
			$facilities[$facility['Room']->roomId] = $name;
		}
		$this->view->facilities = $facilities;
		$this->render();
	}

	public function processPreferencesAction() {
		$params = $this->_getParam('usersPreferences');

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><preferences/>');
		if (isset($params['tabs'])) {
			$tabs = $params['tabs'];
			if (!is_array($tabs)) {
				$tabs = array($tabs);
			}
			foreach ($tabs as $tab) {
				$xml->addChild('tabs',$tab);
			}
		}
		if (isset($params['defaultTab'])) {
			$xml->addChild('defaultTab',$params['defaultTab']);
		}
		if (isset($params['currentLocation'])) {
			$xml->addChild('currentLocation',$params['currentLocation']);
		}
		$this->user->preferences = $xml->asXML();
		$this->user->persist();

		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
