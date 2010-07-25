<?php
/*****************************************************************************
*       PatientPicturesController.php
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


class PatientPicturesController extends WebVista_Controller_Action {

	public function indexAction() {
		$personId = (int)$this->_getParam('personId');
		$this->view->personId = $personId;
		$this->render();
	}

	public function listPicturesAction() {
		$personId = (int)$this->_getParam('personId');
		$person = new Person();
		$person->personId = $personId;
		$person->populate();
		$attachment = new Attachment();
		$attachmentIterator = $attachment->getIteratorByAttachmentReferenceId($personId);
		$xml = '<data>';
		foreach($attachmentIterator as $file) {
			$description = '';
			$active = 0;
			if ($person->activePhoto == $file->attachmentId) {
				$active = 1;
				$description = '(Active)';
			}
			$xml .= '<item id="'.$file->attachmentId.'" active="'.$active.'">';
			$xml .= '<title>'.$file->name.'</title>';
			$xml .= '<description>'.$description.'</description>';
			$xml .= '</item>';
		}
		$xml .= '</data>';
		$this->view->xml = $xml;
		header('Content-type: text/xml');
		$this->render();
	}

	public function processSetActivePictureAction() {
		$personId = (int)$this->_getParam('personId');
		$attachmentId = (int)$this->_getParam('id');
		$data = false;
		if ($personId > 0 && $attachmentId > 0) {
			$person = new Person();
			$person->personId = $personId;
			$person->populate();
			$person->activePhoto = $attachmentId;
			$person->persist();
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processDeletePictureAction() {
		$personId = (int)$this->_getParam('personId');
		$attachmentId = (int)$this->_getParam('id');
		$data = false;
		if ($attachmentId > 0) {
			$attachment = new Attachment();
			$attachment->attachmentId = $attachmentId;
			$attachment->setPersistMode(WebVista_Model_ORM::DELETE);
			$attachment->persist();
			$person = new Person();
			$person->personId = $personId;
			$person->populate();
			if ($person->activePhoto == $attachmentId) {
				$person->activePhoto = 0;
				$person->persist();
			}
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
