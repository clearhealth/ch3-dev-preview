<?php
/*****************************************************************************
*       FilesController.php
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


class FilesController extends WebVista_Controller_Action {

	public function flatAction() {
		$this->view->data = $this->_getParam('data','');
		header('Content-Disposition: attachment; filename="file.txt"');
		//header('Content-type: application/vnd.ms-excel');
		header('Content-type: text/plain');
		$this->render('index');
	}

	public function xmlAction() {
		$this->view->data = $this->_getParam('data','');
		header('Content-Disposition: attachment; filename="file.xml"');
		header('Content-type: application/xml');
		$this->render('index');
	}

}
