<?php
/*****************************************************************************
*       ClinicalNotesFormController.php
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


class ClinicalNotesFormController extends WebVista_Controller_Action {

        protected $_form;
        protected $_cn;

        public function init() {
                $this->_session = new Zend_Session_Namespace(__CLASS__);
                $cprss = new Zend_Session_Namespace('CprsController');
        }

	public function indexAction() {
		$this->render();
	}

	function templateAction() {
		$clinicalNoteId = $this->_getParam('clinicalNoteId',0);
		$cn = new ClinicalNote();
		$cn->clinicalNoteId = (int)$clinicalNoteId;
		$cn->populate();
		$this->_cn = $cn;
		$templateId = $cn->clinicalNoteTemplateId;
		assert("$templateId > 0");
		$cnTemplate = $cn->clinicalNoteDefinition->clinicalNoteTemplate;
		$this->_form = new WebVista_Form(array('name' => 'cn-template-form'));
                $this->_form->setAction(Zend_Registry::get('baseUrl') . "clinical-notes-form.raw/process");
		$cnXML = simplexml_load_string($cnTemplate->template);
		$element = $this->_form->createElement('button','print', array('label' => 'Print'));
                $element->setAttrib('onclick',"
printHtml = dojo.byId('mainToolbar').innerHTML; 
printHtml +=  dojo.byId('cntemplateform').innerHTML;
dojo.byId('iframeprint').contentWindow.document.body.innerHTML = printHtml;
dojo.byId('iframeprint').contentWindow.focus();
dojo.byId('iframeprint').contentWindow.print();
//dojo.byId('iframeprint').contentWindow.document.body.innerHTML = '';
printHtml = '';
");
                $element->setValue('Print');
		// pre-register print element to view so that other elements can override particularly the onclick event
		$this->view->elementPrint = $element;
                $this->_form->addElement($element);
		$this->_buildForm($cnXML);
		$this->_form->addElement($this->_form->createElement('hidden','clinicalNoteId', array('value' => (int)$cn->clinicalNoteId)));

		$db = Zend_Registry::get('dbAdapter');
                $cndSelect = $db->select()
                        ->from('genericData')
			->where("objectClass = 'ClinicalNote'")
			->where("objectId = " . (int)$cn->clinicalNoteId);
		
		if ($cn->eSignatureId > 0) {
			$this->_form->removeElement('ok');
			$esig = new ESignature();
			$esig->eSignatureId = $cn->eSignatureId;
			$esig->populate();
			$signPerson = new Person();
			$signPerson->personId = $esig->signingUserId;
			$signPerson->populate();
			$this->view->signatureInfo = "Signed on: " . $esig->signedDateTime . " by: " . '';
		}

		$formData = array();
		foreach($db->query($cndSelect)->fetchAll() as $row) {
			$formData[$row['name']] = $row['value'];
		}
		$this->_form->populate($formData);

		$this->view->form = $this->_form;
        }

	function processAction() {
		$data = $this->_getParam('namespaceData');
		$saveDate = date('Y-m-d H:i:s');
		$cn = new ClinicalNote();
		$cn->clinicalNoteId = (int)$this->_getParam('clinicalNoteId');
		$cn->populate();
		$cn->dateTime = date('Y-m-d H:i:s');
		$cn->persist();
		foreach($data as $name => $value) {
			$gd = new GenericData();
			$gd->objectClass = "ClinicalNote";
			$gd->objectId = (int)$this->_getParam('clinicalNoteId');
			$gd->dateTime = $saveDate;
			$gd->name = $name;
			$gd->value = $value;
			$gd->persist();
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
                $json->suppressExit = true;

                $json->direct('Data saved.');
	}


	protected function _buildForm($xml) {
		static $_namespaceElements = array();
		foreach ($xml as $question) {
			$formName = preg_replace('/[^a-zA-Z0-9]/','',$question->attributes()->label);
			//var_dump($question->dataPoint);
			$elements = array();
			foreach($question as $key => $item) {
				if ($key == "dataPoint") $dataPoint = $item;
				elseif ($key == "heading") {
					$headingName = preg_replace('/[^a-zA-Z0-9\ ]/','',(string)$item);
					$element = $this->_form->createElement('hidden',$headingName, array('label' => (string)$item, 'disabled' => "disabled"));
					$element->addDecorator('Label', array('tag' => 'h3'));
					$this->_form->addElement($element);
					$elements[] = $headingName;
					continue;
				}
				else { continue; } 
				$type = (string)$dataPoint->attributes()->type;
				if ($this->_cn->eSignatureId > 0 && $type == 'div') {
					$type = 'pre';
				}
				else if ($this->_cn->eSignatureId > 0 && $type == 'richEdit') {
					$type = 'readOnly';
				}
                                if ($type == "img" && (string)$dataPoint->attributes()->draw == "true") {
					$type = 'drawing';
                                }

				$elementName = preg_replace('/[-\.]/','_',(string)$dataPoint->attributes()->namespace);
				$element = $this->_form->createElement($type,$elementName, array('label' => (string)$dataPoint->attributes()->label));
				if ($this->_form->getElement($elementName) instanceof Zend_Form_Element) {
                                        $element =$this->_form->getElement($elementName);
                                }
				if ((string)$dataPoint->attributes()->src) {
					$element->setAttrib("src",(string)$dataPoint->attributes()->src);
				}
				if ((string)$dataPoint->attributes()->class) {
					$element->setAttribute("class",(string)$dataPoint->attributes()->class);
				}
                                if ((string)$dataPoint->attributes()->type == "radio" || $type == "select") {
                                        $element->setLabel("");
                                        $element->setSeparator("&nbsp;&nbsp;");
                                        $element->addMultiOption((string)$dataPoint->attributes()->value,(string)$dataPoint->attributes()->label);
                                        if ((string)$dataPoint->attributes()->default == true) {
                                                $element->setValue((string)$dataPoint->attributes()->value);     
                                        }
					if ((string)$dataPoint->attributes()->type == 'radio') {
						if ((string)$dataPoint->attributes()->radioGroup) {
							$radioGroup = preg_replace('/\./','_',(string)$dataPoint->attributes()->radioGroup);
							$innerHTML = '';
							$elName = 'namespaceData['.$element->getName().']';
							if (!isset($_namespaceElements[$radioGroup])) {
								$_namespaceElements[$radioGroup] = true;
								$innerHTML = '
var '.$radioGroup.'Members = [];
function '.$radioGroup.'(name) {
	var elName = null;
	var el = null;
	for (var i = 0; i < '.$radioGroup.'Members.length; i++) {
		elName = '.$radioGroup.'Members[i];
		el = document.getElementsByName(elName)[0];
		if (!el) {
			continue;
		}
		if (el.checked && elName != name) {
			el.checked = false;
			break; // there is only on checked element in a group
		} 
	}
}
';
							}
							$innerHTML .= $radioGroup.'Members.push("'.$elName.'");';
                                        		$element->addDecorator('ScriptTag',array('placement' => 'APPEND','tag' => 'script','innerHTML' => $innerHTML,'noAttribs' => true));
							$element->setAttrib('onchange',$radioGroup.'("'.$elName.'")');
						}
						if ((string)$dataPoint->attributes()->radioGroupDefault && (string)$dataPoint->attributes()->radioGroupDefault == '1') {
							$element->setAttrib('checked','checked');
						}
					}
                                }
                                if ((string)$dataPoint->attributes()->type == "checkbox") {
					$element->AddDecorator('Label', array('placement' => 'APPEND'));
					$element->AddDecorator('HtmlTag', array('placement' => 'PREPEND', 'tag' => '<br />'));
				}
                                if (strlen((string)$dataPoint->attributes()->templateText) > 0) {
					$templateName = (string)$dataPoint->attributes()->templateText;
					//$element->setValue($this->view->action('index','template-text',null,array('personId' => $this->_cn->personId)));
					$element->setValue($this->view->action('templated-text','template-text',null,array('personId' => $this->_cn->personId,'templateName'=>$templateName)));
                                }

				if ((string)$dataPoint->script) {
                                        $element->addDecorator("ScriptTag",array('placement' => 'APPEND','tag' => 'script','innerHTML' => (string)$dataPoint->script,'noAttribs' => true));
                                }

				$element->setBelongsTo('namespaceData');
				//var_dump($element);
				$this->_form->addElement($element);
				$elements[] = $elementName;
			}
			//var_dump($elements);
			if (count($elements) > 0) {
				$this->_form->addDisplayGroup($elements,(string)$question->attributes()->label,array("legend" => (string)$question->attributes()->label));
				if (strlen(trim((string)$question->attributes()->containerStyle)) > 0) { 
				    $displayGroup = $this->_form->getDisplayGroup((string)$question->attributes()->label);
				    $style = preg_replace('/\xEF\xBB\xBF/','',trim((string)$question->attributes()->containerStyle));
				    $displayGroup->setDecorators(array(
						'FormElements',
						array('HtmlTag',
						  array('tag'=>'dl', 
						    'style'=> $style
						  )
						),
						'Fieldset'
					));
				}
			}
		}
	}
}
