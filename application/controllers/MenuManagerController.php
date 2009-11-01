<?php
/*****************************************************************************
*       MenuManagerController.php
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
 * Menu Manager controller
 */
class MenuManagerController extends WebVista_Controller_Action {

    protected $_session;

    public function init() {
        $this->_session = new Zend_Session_Namespace(__CLASS__);
    }

	public function editAction() {
		if (isset($this->_session->messages)) {
			$this->view->messages = $this->_session->messages;
		}
		$menuId = (int)$this->_getParam('menuId');
		$enumerationId = (int)$this->_getParam('enumerationId');
		$objMenu = new MenuItem();
		if ($menuId !== 0) {
			$objMenu->menuId = $menuId;
			$objMenu->populate();
		}

		if (!strlen($objMenu->type) > 0) {
			$objMenu->type = 'freeform';
		}
		$mainTabs = array();
		foreach ($this->getMainTabs() as $tabName=>$url) {
			$mainTabs[$tabName] = $tabName;
		}
		$this->view->mainTabs = $mainTabs;
		$this->view->types = array('freeform', 'report', 'form', 'submenu');

		$data['report'] = array();
		$objReport = new Report();
		$data['report'] = $objReport->getReportList();

		$data['form'] = array();
		$objForm = new Form();
		$data['form'] = $objForm->getFormList();

		$data['typeValue'] = $objMenu->action;
		switch ($objMenu->type) {
			case 'report':
				$x = explode('?', $objMenu->action);
				$requestUri = explode('&', $x[1]);
				foreach ($requestUri as $uri) {
					$kvp = explode('=', $uri);
					if ($kvp[0] == 'templateId') {
						$data['typeValue'] = $kvp[1];
						break;
					}
				}
				break;
			case 'form':
				$x = explode('?', $objMenu->action);
				$requestUri = explode('&', $x[1]);
				foreach ($requestUri as $uri) {
					$kvp = explode('=', $uri);
					if ($kvp[0] == 'formId') {
						$data['typeValue'] = $kvp[1];
						break;
					}
				}
				break;
		}

		$this->view->data = $data;

		$objForm = new WebVista_Form(array('name' => 'menu-item'));
		$objForm->setAction(Zend_Registry::get('baseUrl') . "menu-manager.raw/edit-process");
		$objForm->loadORM($objMenu, "MenuItem");
		$objForm->setWindow('windowEditMenuId');
		$this->view->form = $objForm;
		$this->view->enumerationId = $enumerationId;

		$this->render();
	}

    protected function getMainTabs() {
        // temporarily placed it here, move this later to appropriate location
        // since the mainTabs are defined in MainController
        $mainTabs = array();
        $mainTabs['All'] =  '';
        $mainTabs['Calendar'] = $this->view->baseUrl.'/calendar.raw';
        $mainTabs['Problem']   = $this->view->baseUrl.'/problem-list.raw';
        $mainTabs['Medication']   = $this->view->baseUrl.'/medications.raw';
        $mainTabs['Order']   = $this->view->baseUrl.'/orders.raw';
        $mainTabs['ClinicalNote']   = $this->view->baseUrl.'/clinical-notes.raw';
        $mainTabs['LabResult']   = $this->view->baseUrl.'/lab-results.raw';
        $mainTabs['NsdrManager']   = $this->view->baseUrl.'/nsdr-manager.raw';

        $mainTabs['Patient'] = '/index.php/minimal/PatientFinder/find';
        $mainTabs['Billing'] = '/index.php/minimal/Claims/list';
        $mainTabs['Admin']   = '/index.php/minimal/Admin';
        return $mainTabs;
    }

    public function ajaxEditAction() {
        if (isset($this->_session->messages)) {
            $this->view->messages = $this->_session->messages;
        }
        $menuId = $this->_getParam('menuId');
        if ((int)$menuId === 0 && substr($menuId,0,11) != 'newMenuItem') {
            $msg = __("Root menu id cannot be modified.");
            throw new Exception($msg);
        }
        $objMenu = new MenuItem();
        $objMenu->menuId = $menuId;
        $objMenu->populate();
        $data = array();
        $data['menuId'] = $menuId;
        $data['type'] = $objMenu->type;
        $data['parentId'] = $objMenu->parentId;
        $data['active'] = $objMenu->active;
        $data['siteSection'] = $objMenu->siteSection;
        $objForm = new WebVista_Form(array('name' => 'menu-item'));
        $objForm->setAction(Zend_Registry::get('baseUrl') . "menu-manager.raw/edit-process");
        $objForm->loadORM($objMenu, "MenuItem");
        $objForm->setWindow('windowEditMenuId');
        $this->view->form = $objForm;

        if (!isset($data['siteSection'])) {
            $data['siteSection'] = 'default';
        }
        if (!isset($data['type'])) {
            $data['type'] = 'freeform';
        }
        $this->view->mainTabs = $this->getMainTabs();
        $this->view->types = array('freeform', 'report', 'form', 'submenu');

        $data['val'] = $objMenu->typeValue;
        $data['report'] = array();
        $objReport = new Report();
        $data['report'] = $objReport->getReportList();

        $data['form'] = array();
        $objForm = new Form();
        $data['form'] = $objForm->getFormList();

        $data['typeValue'] = $objMenu->action;
        switch ($objMenu->type) {
            case 'report':
                $x = explode('?', $objMenu->action);
                $requestUri = explode('&', $x[1]);
                foreach ($requestUri as $uri) {
                    $kvp = explode('=', $uri);
                    if ($kvp[0] == 'templateId') {
                        $data['typeValue'] = $kvp[1];
                        break;
                    }
                }
                break;
            case 'form':
                $x = explode('?', $objMenu->action);
                $requestUri = explode('&', $x[1]);
                foreach ($requestUri as $uri) {
                    $kvp = explode('=', $uri);
                    if ($kvp[0] == 'formId') {
                        $data['typeValue'] = $kvp[1];
                        break;
                    }
                }
                break;
        }

        $this->view->data = $data;
        $this->render('ajax-edit');
    }

    function editProcessAction() {
		$enumerationId = (int)$this->_getParam('enumerationId');
		$menuParams = $this->_getParam('menuItem');
		$menuId = (int)$menuParams['menuId'];
		$origMenuId = $menuId;
		$menuParams['menuId'] = $menuId;
		$objMenu = new MenuItem();
		if ($menuId !== 0) {
			$objMenu->menuId = $menuId;
			$objMenu->populate();
		}

		$menuParams['action'] = '';
		if (isset($menuParams['type'])) {
			switch ($menuParams['type']) {
				case 'freeform':
					if ($this->_getParam('typefreeform') !== NULL) {
						$menuParams['action'] = $this->_getParam('typefreeform');
					}
					break;
				case 'report':
					if ($this->_getParam('typereport') !== NULL) {
						$x = explode('-', $this->_getParam('typereport'));
						$x[0] = (int)$x[0];
						$x[1] = (int)$x[1];
						$menuParams['action'] = "Report/report?reportId={$x[0]}&templateId={$x[1]}";
					}
					break;
				case 'form':
					if ($this->_getParam('typeform') !== NULL) {
						$typeForm = (int)$this->_getParam('typeform');
						$menuParams['action'] = "Form/fillout?formId={$typeForm}";
					}
					break;
			}
		}

		$menuParams['active'] = (int)$this->_getParam('active');
		if ($this->_getParam('chSiteSection') !== NULL) {
			$menuParams['siteSection'] = $this->_getParam('chSiteSection');
		}
		$menuParams['parentId'] = (int)$menuParams['parentId'];

		$objMenu->populateWithArray($menuParams);

		if ($enumerationId !== 0) {
			// update its parent
			$enumerationsClosure = new EnumerationsClosure();
			$objMenu->parentId = $enumerationsClosure->getParentById($enumerationId);
		}

		$objMenu->persist();

		if ($menuId === 0 && $enumerationId !== 0) {
			$enumeration = new Enumeration();
			$enumeration->enumerationId = $enumerationId;
			$enumeration->populate();
			$enumeration->ormId = $objMenu->menuId;
			$enumeration->persist();
		}

		$msg = __("Record Saved for Menu: " . ucfirst($objMenu->title));
		$data = array();
		$data['msg'] = $msg;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

    /**
     * Default action to dispatch
     */
    public function indexAction() {
		$this->render();
    }

    /**
     * Move menu
     */
    public function ajaxMoveMenuItemAction() {
        $idFrom = (int) $this->_getParam('idFrom');
        $idTo = (int) $this->_getParam('idTo');
	$idBefore = (int) $this->_getParam('idBefore');

        if ($idTo == 'menu') {
            $idTo = 0;
        }

	$menuItem = new MenuItem();
	$menuItem->menuId = $idFrom;
	$menuItem->populate();

	if ($idBefore > 0) {
		//this is the sibling reorder case
		$beforeMenuItem = new MenuItem();
		$beforeMenuItem->menuId = $idBefore;
		$beforeMenuItem->populate();
		$menuItem->updateDisplayOrder($beforeMenuItem->displayOrder);
	}
	else {
		//this is the hierarchy level move case
		$menuItem->parentId = $idTo;
		$menuItem->persist();
	}
	$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;
        $json->direct(true);
    }

    /**
     * Menu Settings
     */
    public function ajaxMenuSettingsAction() {
        $this->render();
    }

    /**
     * Clear/Enable/Disable Cache
     */
    public function ajaxChangeCacheSettingsAction() {
        $cacheMode = $this->_getParam('cacheMode');
        $msg = '';
        switch (strtolower($cacheMode)) {
            case 'clear':
                $cache = Zend_Registry::get('cache');
                $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('tagMenu'));
                $msg = __("Successfully cleared cache menu");
                break;
            case 'enable':
            case 'disable':
                $objConfig = new ConfigItem();
                $objConfig->configId = 'enableCache';
                $objConfig->populate();
                $objConfig->value = ($cacheMode == 'enable')? 1 : 0;
                $objConfig->persist();
                $msg = __("Successfully {$cacheMode} cache menu");
                break;
            default:
                $msg = __("Invalid action");
        }
        $data = array('msg' => $msg);
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;
        $json->direct($data);
    }

    /**
     * Delete Menu Item
     */
    public function ajaxDeleteAction() {
        $menuId = (int)$this->_getParam('menuId');
        if ($menuId === 0) {
            $msg = __("Deleting root menu is not allowed!");
        }
        else {
            // TODO: execute deletion of menu id, temporarily placed it here 
            //       since ORM DELETE is not fully implemented
            $objMenu = new MenuItem();
            $objMenu->menuId = $menuId;
            if ($objMenu->delete()) {
                $msg = __("Successfully deleted Menu Item: $menuId");
            }
            else {
                $msg = __("Failed to deleted Menu Item: $menuId");
            }
        }
        $data = array('msg' => $msg);
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;
        $json->direct($data);
    }

    /**
     * Outputs xml left side toolbar menu
     */
    public function leftToolbarXmlAction() {
        if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        }
        else {
            header("Content-type: text/xml");
        }
        $strXml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
    <toolbar>
        <item id=\"add\" type=\"button\" title=\"Add\" img=\"newproblem.png\" imgdis=\"newproblem.png\"/>
        <item id=\"delete\" type=\"button\" title=\"Delete\" img=\"removeproblem.png\" imgdis=\"removeproblem.png\"/>
    </toolbar>";
        $this->view->toolbarXml = $strXml;
        $this->render('toolbar-xml');
    }

    /**
     * Retrieves menu from database and output its raw JSON format
     */
    public function menuJsonAction() {
        $this->view->toolbarXml = Menu::generateJsonMenu();
        $this->render('toolbar-xml');
    }

    /**
     * Retrieves menu from database and output its raw JSON format
     */
    public function exportXmlAction() {
        if (stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        }
        else {
            header("Content-type: text/xml");
        }
        $strXml = Menu::getXmlMenu();
        $this->view->menuXml = $strXml;
        $this->render();
    }

    /**
     * Synchronize menu using XML file
     */
    public function syncWithXmlAction() {
        $strXml = Menu::getXmlMenu();
        // what do next with the XML menu?
        return;
    }


}

