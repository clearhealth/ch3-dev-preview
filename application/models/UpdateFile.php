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
	protected $channelId;
	protected $channel;
	protected $license;

	protected $_table = 'updateFiles';
	protected $_primaryKeys = array('updateFileId');

	protected $_fd = null; // file descriptor
	protected $_tables = array(); // list of all existing tables
	protected $_changes = array(); // diff results container

	const USER_CHANNEL_ID = 0;
	const USER_CHANNEL = 'User Channel';

	public function persist() {
		$filename = $this->getUploadFilename();
		$version = (int)$this->version;
		if ($version <= 0) {
			$this->version = $this->getLatestVersion() + 1;
		}
		$ret = parent::persist();
		if ($ret && $this->_persistMode == self::DELETE && file_exists($filename)) {
			unlink($filename);
		}
		return $ret;
	}

	public function setUpdateFileId($val) {
		$updateFileId = (int)$val;
		$this->updateFileId = $updateFileId;
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

	public function getIteratorActive($sqlSelect = null) {
		if ($sqlSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$sqlSelect = $db->select()
					->from($this->_table)
					->where('active = 1')
					->order('channel ASC')
					->order('version DESC');
		}
		return $this->getIterator($sqlSelect);
	}

	public function getLatestVersion() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('channel = ?',$this->channel)
				->order('version DESC')
				->limit(1);
		$version = 0;
		if ($row = $db->fetchRow($sqlSelect)) {
			$version = $row['version'];
		}
		return $version;
	}

	public function getUploadDir() {
		$basePath = Zend_Registry::get('basePath');
		return $basePath . 'tmp/updates/';
	}

	public function getUploadFilename() {
		return $this->getUploadDir().$this->updateFileId.'.xml';
	}

	public function getData() {
		static $data = null;
		if ($data !== null) {
			return $data;
		}
		$filename = $this->getUploadFilename();
		if (file_exists($filename)) {
			$data = file_get_contents($filename);
		}
		return $data;
	}

	public function getAllVersions() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table,array('MAX(version) AS version','channelId'))
				->where('channelId != ?',self::USER_CHANNEL_ID)
				->group('channelId');
		$versions = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$versions[$row['channelId']] = $row['version'];
			}
		}
		return $versions;
	}

	public function verify($data = null) {
		if ($data === null) {
			$data = file_get_contents($this->getUploadFilename());
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
		if ($channelId = $elem->getAttribute('channelId')) {
			$this->channelId = (int)$channelId;
		}
		if ($channel = $elem->getAttribute('channel')) {
			$this->channel = $channel;
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
		if ($license = $elem->getAttribute('license')) {
			$this->license = $license;
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
