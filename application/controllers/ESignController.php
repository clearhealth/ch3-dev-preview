<?php
/*****************************************************************************
*       ESignController.php
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


class ESignController extends WebVista_Controller_Action {

	protected $_form;

        public function init() {
                $this->_session = new Zend_Session_Namespace(__CLASS__);
        }

	public function indexAction() {
		$this->_form = new WebVista_Form(array('name' => 'es-batch-sign-form'));
                $this->_form->setAction(Zend_Registry::get('baseUrl') . "esign.raw/process-batch");
		$element = $this->_form->createElement("text","signature", array('label' => "Signature"));
                $this->_form->addElement($element);
		$this->view->form = $this->_form;
		$multipleSign = 'false';
		$config = Zend_Registry::get('config');
		if (isset($config->esign->multiple) && $config->esign->multiple == 'true') {
			$multipleSign = 'true';
		}
		$this->view->multipleSign = $multipleSign;
		$this->view->objectId = (int)$this->_getParam('objectId');
		$this->render();
	}

	function countUnsignedAction() {
		$eSignIterator = new ESignatureIterator();
		$eSignIterator->setFilter((int)Zend_Auth::getInstance()->getIdentity()->personId,'signList');
		$counter = 0;
                foreach($eSignIterator as $row) {
			$counter++;
                }
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('counter'=>$counter));
	}

	function listItemsAction() {
		$eSignIterator = new ESignatureIterator();
		$objectId = (int)$this->_getParam('objectId');
		if ($objectId > 0) {
			$eSignIterator->setFilter($objectId,'objectId');
		}
		else {
			$eSignIterator->setFilter((int)Zend_Auth::getInstance()->getIdentity()->personId,'signList');
		}
                //var_dump($db->query($cnSelect)->fetchAll());exit;
		$baseStr = "<?xml version='1.0' standalone='yes'?><rows></rows>";
		$xml = new SimpleXMLElement($baseStr);
		$currentCat = null;
		$category = null;
		// override the include_path to include controllers path
		set_include_path(realpath(Zend_Registry::get('basePath').'/application/controllers')
				. PATH_SEPARATOR . get_include_path());
                foreach($eSignIterator as $row) {
			$row = $row->toArray();
			if ($currentCat != $row['objectClass']) {
				$currentCat = $row['objectClass'];
				$category = $xml->addChild("row");
				$category->addAttribute("id",$row['objectClass']);
				$cell = $category->addChild("cell",call_user_func($currentCat ."::" . "getPrettyName",array()));
				$cell = $category->addChild("cell",'');
				$controllerName = call_user_func($currentCat . "::" . "getControllerName");
				$jumpLink = call_user_func_array($controllerName . "::" . "buildJSJumpLink",array($row['objectId'],$row['signingUserId'],$row['objectClass']));
				//$jumpLink = $this->buildJSJumpLink($row['objectId'],$row['signingUserId'],$row['objectClass']);
				$js = "function jumpLink{$row['objectClass']}(objectId,patientId) {\n{$jumpLink}\n}";
				$cell = $category->addChild('cell',$js);
			}
			
			$leaf = $category->addChild("row");
			$leaf->addAttribute('id',$row['eSignatureId']);
			$leaf->addChild('cell',$row['dateTime'] . " " . $row['summary']);
			$leaf->addChild('cell','');
			// hidden column that will load the correct tab
			$leaf->addChild('cell',$row['objectId']); // temporary set to objectId
			//$leaf->addChild('cell',$this->buildJSJumpLink($row['objectId'],$row['signingUserId']));
			// for patientId hidden column, not sure if this is the correct field.
			//$leaf->addChild('cell',$row['signingUserId']);
			$obj = new $row['objectClass']();
			foreach ($obj->_primaryKeys as $key) {
				$obj->$key = $row['objectId'];
			}
			$obj->populate();
			$patientId = $obj->personId;
			$leaf->addChild('cell',$patientId);
                }

                header('content-type: text/xml');
		$this->view->content = $xml->asXml();
                $this->render();
	}

	function editSignItemsAction() {
		$eSigIds = Zend_Json::decode(($this->_getParam('electronicSignatureIds')));
		if (strlen($eSigIds) <= 0) {
			$msg = __('No selected signature.');
			throw new Exception($msg);
		}
		$eSigIds = explode(',',$eSigIds);
		$signature = $this->_getParam('signature');
		foreach ($eSigIds as $eSigId) {
			if (strlen($eSigId) <= 0) {
				continue;
			}
			$esig = new ESignature();
			$esig->eSignatureId = (int)$eSigId;
			$esig->populate();
			$signedDate =  date('Y-m-d H:i:s');
			$esig->signedDateTime = $signedDate;
			$obj = new $esig->objectClass();
			$obj->documentId = $esig->objectId;
			$obj->eSignatureId = $esig->eSignatureId;
			$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
               		$json->suppressExit = true;
			try {
				$esig->sign($obj, $signature);
			}
			catch (Exception $e) {
				$this->getResponse()->setHttpResponseCode(500);
                		$json->direct(array('error' => $e->getMessage()));
				return;
			}
			$esig->persist();
			$obj->populate();
			$obj->eSignatureId = $esig->eSignatureId;
			$obj->persist();
		}
	}

/*	function testKeysAction () {
		$passphrase = $this->_getParam('passphrase');
		echo $passphrase;
		flush();	
		$uK = new UserKey();
		$uK->userId = 1;
		$uK->generateKeys($passphrase);
		//echo $uK->toString();
		$uK->persist();
		$nk = new UserKey();
		$nk->userId = 1;
		$nk->populate();
		echo $nk->getDecryptedPrivateKey($passphrase);

		exit;
	}*/
	
	/*function testToDocumentAction() {
		$clinicalNote = new ClinicalNote();
		$clinicalNote->clinicalNoteId = 459;
		$clinicalNote->populate();
		echo $clinicalNote->toDocument();
		exit;
	}*/
}
