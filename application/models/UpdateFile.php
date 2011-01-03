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
	protected $status = 'New';
	protected $queue;

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
		return Zend_Registry::get('basePath').'data'.DIRECTORY_SEPARATOR.'updates'.DIRECTORY_SEPARATOR;
	}

	public function getUploadFilename() {
		return $this->getUploadDir().$this->updateFileId.'.xml';
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

	public function verify($filename) {
		set_time_limit(0);
		if (!file_exists($filename)) {
			throw new Exception('File '.$filename.' does not exists');
		}
		$zd = gzopen($filename,'r');
		if (!$zd) {
			throw new Exception('Could not open gzip file '.$filename);
		}
		$file = $this->getUploadFilename();
		$fp = fopen($file,'w');
		if (!$fp) {
			throw new Exception('Could not write file '.$file);
		}
		$signatureTag = '';
		$ctr = 1;
		while (!gzeof($zd)) {
			$buffer = gzgets($zd,4096);
			if ($signatureTag == '' && $ctr++ == 2) { // line 2 is expected to be a <signature> tag
				$signatureTag = $buffer;
				continue;
			}
			fwrite($fp,$buffer);
		}
		$signature = substr($signatureTag,11,strlen($signatureTag)-24);
		gzclose($zd);
		if ($signature == '') {
			throw new Exception('Invalid signature');
		}

		$hash = hash_file('sha256',$file);
		$keyFile = Zend_Registry::get('basePath');
		$keyFile .= Zend_Registry::get('config')->healthcloud->updateServerPubKeyPath;
		$serverPublicKey = file_get_contents($keyFile);
		$publicKey = openssl_get_publickey($serverPublicKey);
		openssl_public_decrypt(base64_decode($signature),$verifyHash,$publicKey);
		openssl_free_key($publicKey);
		$verifyHash = trim($verifyHash);
		if ($hash !== $verifyHash) {
			$error = __('Data verification with signature failed.');
			trigger_error($error);
			throw new Exception($error);
		}
		return true;
	}

	public function getIteratorByQueue() {
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('queue = 1')
				->order('dateTime DESC');
		return parent::getIterator($sqlSelect);
	}

}
