<?php
/*****************************************************************************
*       UpdateFile.php
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


class UpdateFile extends WebVista_Model_ORM {

	protected $updateFileId;
	protected $name;
	protected $dateTime;
	protected $md5sum;
	protected $version;
	protected $active;
	protected $description;
	protected $blob = array();

	protected $_table = 'updateFiles';
	protected $_primaryKeys = array('updateFileId');

	protected $_fd = null; // file descriptor
	protected $_tables = array(); // list of all existing tables
	protected $_changes = array(); // diff results container

	public function __construct() {
		$this->blob = array();
	}

	public function populate($includeBlob = true) {
		$ret = parent::populate();
		if (!$includeBlob) {
			return $ret;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('updateFileBlobs')
				->where('updateFileId = ?',$this->updateFileId);
		$this->blob = $db->fetchRow($sqlSelect);
		return $ret;
	}

	public function persist($includeBlob = true) {
		$ret = parent::persist();
		if (!$includeBlob) {
			return $ret;
		}
		if ($this->blob) {
			$db = Zend_Registry::get('dbAdapter');
			if ($this->_persistMode === WebVista_Model_ORM::DELETE) {
				$db->delete('updateFileBlobs','updateFileId='.$this->updateFileId);
			}
			else {
				$db->insert('updateFileBlobs',$this->blob);
			}
		}
		return $ret;
	}

	public function setUpdateFileId($val) {
		$updateFileId = (int)$val;
		$this->updateFileId = $updateFileId;
		$this->blob['updateFileId'] = $this->updateFileId;
	}

	public function populateWithArray($array) {
		parent::populateWithArray($array);
		$blob = array();
		if (isset($array['data'])) {
			$blob['updateFileId'] = $this->updateFileId;
			$blob['data'] = $array['data'];
		}
		$this->blob = $blob;
	}

	public function getIterator($sqlSelect = null) {
		if ($sqlSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$sqlSelect = $db->select()
					->from($this->_table)
					->order('dateTime DESC');
		}
		return parent::getIterator($sqlSelect);
	}

	public function getIteratorBlob($sqlSelect = null) {
		if ($sqlSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$sqlSelect = $db->select()
					->from(array('xf'=>$this->_table))
					->join(array('xfb'=>'updateFileBlobs'),'xf.updateFileId = xfb.updateFileId')
					->order('dateTime DESC');
		}
		return $this->getIterator($sqlSelect);
	}

	public function getIteratorActiveBlob($sqlSelect = null) {
		if ($sqlSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$sqlSelect = $db->select()
					->from(array('xf'=>$this->_table))
					->join(array('xfb'=>'updateFileBlobs'),'xf.updateFileId = xfb.updateFileId')
					->where('active = 1')
					->order('version DESC');
		}
		return $this->getIterator($sqlSelect);
	}

	public function getIteratorByVersion($version = null) {
		if ($version === null) {
			$version = $this->version;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('xf'=>$this->_table))
				->join(array('xfb'=>'updateFileBlobs'),'xf.updateFileId = xfb.updateFileId')
				->where('version > ?',$version)
				->order('dateTime DESC');
		return $this->getIterator($sqlSelect);
	}

	public function getLatestVersion() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->order('version DESC')
				->limit(1);
		$version = '0.0';
		if ($row = $db->fetchRow($sqlSelect)) {
			$version = $row['version'];
		}
		return $version;
	}

	public function populateByLatestVersion() {
		$version = $this->version;
		if (!strlen($this->version) > 0) {
			$version = '0.0';
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from(array('xf'=>$this->_table))
				->join(array('xfb'=>'updateFileBlobs'),'xf.updateFileId = xfb.updateFileId')
				->where('xf.version > ?',$version)
				->order('xf.version ASC')
				->limit(1);
		$ret = false;
		if ($row = $db->fetchRow($sqlSelect)) {
			$this->populateWithArray($row);
			$ret = true;
		}
		return $ret;
	}

	/*
	public function extractMetaData($data = null) {
		if ($data === null) {
			$data = $this->blob['data'];
		}
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		if (!$doc->loadXML($data)) {
			throw new Exception('Generated XML is invalid');
		}
		$rootNode = $doc->getElementsByTagName('mysqldump');
		if ($rootNode->length <= 0) {
			$node = $doc->createElement('mysqldump');
			$rootDoc = $doc->appendChild($node);
		}
		else {
			$rootDoc = $rootNode->item(0);
		}
		$nodeList = $rootDoc->getElementsByTagName('meta-data');
		if ($nodeList->length > 0) {
			$elem = $nodeList->item(0);
			if ($version = $elem->getAttribute('version')) {
				$this->version = $version;
			}
			$elem->setAttribute('signature','');
			if ($name = $elem->getAttribute('name')) {
				$this->name = $name;
			}
			if ($md5sum = $elem->getAttribute('md5sum')) {
				$this->md5sum = $md5sum;
			}
			if ($description = $elem->getAttribute('description')) {
				$this->description = $description;
			}
		}
	}
	*/

	public function verify($data = null) {
		if ($data === null) {
			$data = $this->blob['data'];
		}
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		if (!$doc->loadXML($data)) {
			throw new Exception('Generated XML is invalid');
		}
		$rootNode = $doc->getElementsByTagName('mysqldump');
		if ($rootNode->length <= 0) {
			$node = $doc->createElement('mysqldump');
			$rootDoc = $doc->appendChild($node);
		}
		else {
			$rootDoc = $rootNode->item(0);
		}
		$nodeList = $rootDoc->getElementsByTagName('meta-data');
		if ($nodeList->length <= 0) {
			$node = $doc->createElement('meta-data');
			$elem = $rootDoc->appendChild($node);
		}
		else {
			$elem = $nodeList->item(0);
		}
		$signature = $elem->getAttribute('signature');
		if ($version = $elem->getAttribute('version')) {
			$this->version = $version;
		}
		$elem->setAttribute('signature','');
		if ($name = $elem->getAttribute('name')) {
			$this->name = $name;
		}
		if ($md5sum = $elem->getAttribute('md5sum')) {
			$this->md5sum = $md5sum;
		}
		if ($description = $elem->getAttribute('description')) {
			$this->description = $description;
		}
		$newData = $doc->saveXML();

		$hash = md5($newData);
		$userKey = new UserKey();
		$userKey->userId = $this->signingUserId;
		$userKey->populate();
		$keyFile = Zend_Registry::get('basePath');
		$keyFile .= Zend_Registry::get('config')->healthcloud->updateServerPubKeyPath;
		$serverPublicKey = file_get_contents($keyFile);
		$publicKey = openssl_get_publickey($serverPublicKey);
		openssl_public_decrypt(base64_decode($signature),$verifyHash,$publicKey);
		openssl_free_key($publicKey);
		if ($hash !== $verifyHash) {
			throw new Exception('Data verification with signature failed.');
		}
		return true;
	}

}
