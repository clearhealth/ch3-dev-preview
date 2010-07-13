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
		$element = $this->_form->createElement("password","signature", array('label' => "Signature"));
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
				$category->addAttribute("style",'height:20px;');
				$category->addAttribute("id",$row['objectClass']);
				$cell = $category->addChild("cell",call_user_func($currentCat ."::" . "getPrettyName",array()));
				$cell = $category->addChild("cell",'');
				$controllerName = call_user_func($currentCat . "::" . "getControllerName");
				$jumpLink = call_user_func_array($controllerName . "::" . "buildJSJumpLink",array($row['objectId'],$row['signingUserId'],$row['objectClass']));
				//$jumpLink = $this->buildJSJumpLink($row['objectId'],$row['signingUserId'],$row['objectClass']);
				$js = "function jumpLink{$row['objectClass']}(objectId,patientId) {\n{$jumpLink}\n}";
//				$cell = $category->addChild('cell',$js);
				$userdata = $category->addChild('userdata',$js);
				$userdata->addAttribute('name','js');
			}
			
			$leaf = $category->addChild("row");
			$leaf->addAttribute('id',$row['eSignatureId']);
			$leaf->addChild('cell',$row['dateTime'] . " " . preg_replace('/[<>]/','',$row['summary']));
			$leaf->addChild('cell','');
			$userdata = $leaf->addChild('userdata',$row['objectId']);
			$userdata->addAttribute('name','objectId');
			// hidden column that will load the correct tab
//			$leaf->addChild('cell',$row['objectId']); // temporary set to objectId
			//$leaf->addChild('cell',$this->buildJSJumpLink($row['objectId'],$row['signingUserId']));
			// for patientId hidden column, not sure if this is the correct field.
			//$leaf->addChild('cell',$row['signingUserId']);
			$obj = new $row['objectClass']();
			foreach ($obj->_primaryKeys as $key) {
				$obj->$key = $row['objectId'];
			}
			$obj->populate();
			$patientId = $obj->personId;
			$userdata = $leaf->addChild('userdata',$patientId);
			$userdata->addAttribute('name','patientId');
			//$leaf->addChild('cell',$patientId);

			// add a subrow for other info
			if ($row['objectClass'] == 'Medication') {
				$pharmacyInfo = array();
				// $obj refers to Medication ORM
				if ($obj->isScheduled()) {
					$pharmacyInfo[] = 'Medication is a controlled substance, it cannot be sent electronically. The Rx will be printed and needs a wet signature before it can be faxed to the pharmacy or handed to the patient.';
				}
				else {
					$pharmacy = $obj->pharmacy;
					if (strlen($pharmacy->StoreName) > 0) {
						$pharmacyInfo[] = $pharmacy->StoreName;
						$address = $pharmacy->AddressLine1;
						if (strlen($pharmacy->AddressLine2) > 0) {
							$address .= ' '.$pharmacy->AddressLine2;
						}
						$address .= ', '.$pharmacy->City;
						$address .= ', '.$pharmacy->State;
						$address .= ', '.$pharmacy->Zip;
						$pharmacyInfo[] = $address;
						$phones = array();
						$phones[] = $pharmacy->PhonePrimary;
						if (strlen($pharmacy->Fax) > 0) {
							$phones[] = $pharmacy->Fax;
						}
						if (strlen($pharmacy->PhoneAlt1) > 0) {
							$phones[] = $pharmacy->PhoneAlt1;
						}
						if (strlen($pharmacy->PhoneAlt2) > 0) {
							$phones[] = $pharmacy->PhoneAlt2;
						}
						if (strlen($pharmacy->PhoneAlt3) > 0) {
							$phones[] = $pharmacy->PhoneAlt3;
						}
						if (strlen($pharmacy->PhoneAlt4) > 0) {
							$phones[] = $pharmacy->PhoneAlt4;
						}
						if (strlen($pharmacy->PhoneAlt5) > 0) {
							$phones[] = $pharmacy->PhoneAlt5;
						}
						$pharmacyInfo[] = implode(', ',$phones);
					}
					else {
						$pharmacyInfo[] = 'No pharmacy selected';
					}
				}

				$pharmacyInfo = implode(" <br /> ",$pharmacyInfo);
				$patient = new Patient();
				$patient->personId = $obj->personId;
				$patient->populate();
				$patientInfo = $patient->lastName . ", " . $patient->firstName . " " . strtoupper(substr($patient->middleName,0,1)) . " #" . $patient->recordNumber;

				$qualifiers = Medication::listQuantityQualifiersMapping();
				$medicationInfo = array();
				$rxn = $obj->rxReferenceNumber;
				if (strlen($rxn) > 0) {
					$medicationInfo[] = 'Rx Reference Number: '.$rxn;
				}
				$medicationInfo[] = 'Description: '.htmlspecialchars($obj->description);
				$medicationInfo[] = 'Directions: '.htmlspecialchars($obj->directions);
				$medicationInfo[] = 'Quantity: '.$obj->quantity.' '.$qualifiers[$obj->quantityQualifier];
				//$medicationInfo[] = 'Quantity: '.$obj->quantity.' '.$obj->quantityQualifier;
				$medicationInfo[] = 'Strength: '.$obj->dose.' '.$qualifiers[$obj->quantityQualifier];
				//$medicationInfo[] = 'Strength: '.$obj->dose.' '.$obj->quantityQualifier;
				$medicationInfo[] = 'Days Supply: '.$obj->daysSupply;
				$refills = $obj->refills;
				if ($obj->prn) {
					$refills = 'PRN';
				}
				$medicationInfo[] = 'Refills: '.$refills;
				$substitution = 'Permitted';
				if ($obj->substitution == 0) {
					$substitution = 'Not Permitted';
				}
				$medicationInfo[] = 'Substitutions: '.$substitution;
				$medicationInfo[] = 'Date Prescribed: '.date('Y-m-d',strtotime($obj->datePrescribed));
				//$medicationInfo[] = 'NDC: '.$obj->hipaaNDC;
				//$medicationInfo[] = 'Dosage Form: '.DataTables::getDosageForm($obj->chmedDose);
				//$medicationInfo[] = 'DB Code: '.$obj->pkey;
				$medicationInfo[] = 'Note: '.htmlspecialchars($obj->comment);
				$medicationInfo = implode(' <br /> ',$medicationInfo);

				$info = '<div style="margin-left:75px;margin-top:-18px;margin-bottom:5px;">
					<fieldset>
						<legend title="'.htmlspecialchars($patientInfo).'">'.__('Patient').'</legend>'.htmlspecialchars($patientInfo).'
					</fieldset>
					<fieldset>
						<legend title="'.htmlspecialchars($pharmacyInfo).'">'.__('Pharmacy').'</legend>'.htmlspecialchars($pharmacyInfo).'
					</fieldset>
					<fieldset title="'.htmlspecialchars($medicationInfo).'">
						<legend>'.__('Medication').'</legend>'.htmlspecialchars($medicationInfo).'
					</fieldset></div>';
				$node = $leaf->addChild('row');
				$guid = NSDR::create_guid();
				$node->addAttribute('id',$guid);
//				$node->addAttribute('style','vertical-align:top;height:50px;');
				$node->addChild('cell','<![CDATA['.$info.']]>');
				$node->addChild('cell','');
				$node->addChild('cell','');
				$node->addChild('cell','');
			}
                }

                header('content-type: text/xml');
		$this->view->content = $xml->asXml();
		$this->view->content = html_entity_decode($this->view->content);
		file_put_contents('/tmp/esign.xml',$this->view->content);
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

	public function forwardForSigningAction() {
		$this->view->ormClass = $this->_getParam('ormClass');
		$this->view->ormId = $this->_getParam('ormId');
		$this->render();
	}

	public function processForwardForSigningAction() {
		$providerId = (int)$this->_getParam('providerId');
		$ormClass = $this->_getParam('ormClass');
		$ormId = $this->_getParam('ormId');

		$data = false;
		$esign = new ESignature();
		$esign->objectClass = $ormClass;
		$esign->objectId = $ormId;
		$esign->populateByObject();
		if ($esign->signingUserId > 0) {
			$data = true;
			$esign->signingUserId = $providerId;
			$esign->persist();
		}
		/*if (class_exists($ormClass)) {
			$orm = new $ormClass();
			$primaryKeys = $orm->_primaryKeys;
			$key = $primaryKeys[0];
			$orm->$key = $ormId;
			$orm->populate();
			if ($orm instanceof Document && $orm->signatureNeeded()) {
				ESignature::createSignatureEntry($orm,$providerId);
			}
			$data = true;
		}*/
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
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
