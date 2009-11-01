<?php
/*****************************************************************************
*       ClinicalNote.php
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


class ClinicalNote extends WebVista_Model_ORM implements Document {
	protected $clinicalNoteId;
	protected $personId;
	protected $visitId;
	protected $clinicalNoteDefinitionId;
	protected $clinicalNoteDefinition;
	protected $dateTime;
	protected $authoringPersonId;
	protected $consultationId;
	protected $locationId;
	protected $eSignatureId;

	protected $_primaryKeys = array('clinicalNoteId');
	protected $_table = "clinicalNotes";
	protected $_cascadePersist = false;
	
	function __construct() {
		parent::__construct();
		$this->clinicalNoteDefinition = new ClinicalNoteDefinition();
		$this->clinicalNoteDefinition->_cascadePersist = false;
	}

	function setClinicalNoteDefinitionId($key) {
		if ((int)$key != $this->clinicalNoteTemplateId) {
			$cnDefinition = new ClinicalNoteDefinition();
			$cnDefinition->_cascadePersist = false;
			unset($this->clinicalNoteDefinition);
			$this->clinicalNoteDefinition = $cnDefinition;
		}
		$this->clinicalNoteDefinitionId = (int)$key;
		$this->clinicalNoteDefinition->clinicalNoteDefinitionId = (int)$key;
	}

	function __get($key) {
		if (isset($this->$key)) {
			return $this->$key;
		}
		elseif (in_array($key,$this->clinicalNoteDefinition->ORMFields())) {
			return $this->clinicalNoteDefinition->__get($key);
		}
		/*elseif (!is_null($this->clinicalNoteDefinition->__get($key))) {
			return $this->clinicalNoteDefinition->__get($key);
		}*/
		return parent::__get($key);
	}

	function getSummary() {
		$this->clinicalNoteDefinition->populate();
                return $this->clinicalNoteDefinition->title;
	}

	function getDocumentId() {
		return $this->clinicalNoteId;
	}
	function setDocumentId($id) {
		$this->clinicalNoteId = (int)$id;
	}

	function getContent() {
		return "";
	}

	static function getPrettyName() {
		return "Clinical Notes";
	}

	public static function getControllerName() {
		return "ClinicalNotesController";
	}
	
	function setSigned($eSignatureId) {
		$this->eSignatureId = (int)$eSignatureId;
		$this->persist();
	}
}
