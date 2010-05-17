<?php
/*****************************************************************************
*       UpdateManagerController.php
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
 * Update Manager controller
 */
class UpdateManagerController extends WebVista_Controller_Action {

	protected $_session = null;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	/**
	 * Default action to dispatch
	 */
	public function indexAction() {
		//$this->_download(); // download update files automatically
		$this->render('index');
	}

	public function toolbarAction() {
		$this->view->xmlHeader = '<?xml version="1.0" encoding="iso-8859-1"?>'.PHP_EOL;
		header('Content-Type: text/xml');
		$this->render('toolbar');
	}

	public function listAction() {
		$rows = array();
		$updateFile = new UpdateFile();
		$updateFileIterator = $updateFile->getIteratorActive();
		$alterTable = new AlterTable();
		foreach ($updateFileIterator as $item) {
			$changes = $alterTable->generateChanges($item->blob['data']);
			if (!count($changes) > 0) {
				continue;
			}
			$row = array();
			$row['id'] = $item->updateFileId;
			$row['data'][] = $item->name.' (v'.$item->version.')';
			$row['data'][] = '';
			foreach ($changes as $key=>$val) {
				$tmp = array();
				$tmp['id'] = $item->updateFileId.'_'.$key;
				$tmp['data'][] = $val;
				$tmp['data'][] = '';
				$row['rows'][] = $tmp;
			}
			$rows[] = $row;
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function listXmlAction() {
		$baseStr = "<?xml version='1.0' standalone='yes'?><rows></rows>";
		$xml = new SimpleXMLElement($baseStr);
		$updateFile = new UpdateFile();
		$updateFileIterator = $updateFile->getIteratorActive();
		$alterTable = new AlterTable();
		$channel = null;
		$ctr = 1;
		foreach ($updateFileIterator as $item) {
			//$changes = $alterTable->generateChanges($item->data);
			//if (!count($changes) > 0) {
			//	continue;
			//}
			if ($channel === null || $channel != $item->channel) {
				$channel = $item->channel;
				$channelXml = $xml->addChild('row',$channel);
				$channelXml->addAttribute('id',$ctr++);
				$channelXml->addChild('cell',$channel);
				//$channelXml->addChild('cell','');
			}
			$parent = $channelXml->addChild('row');
			$parent->addAttribute('id',$item->updateFileId);
			$parent->addChild('cell',$item->name.' (v'.$item->version.')');
			$parent->addChild('cell','');
			//foreach ($changes as $key=>$val) {
			//	$child = $parent->addChild('row');
			//	$child->addAttribute('id',$item->updateFileId.'_'.$key);
			//	$child->addChild('cell',$val);
			//	$child->addChild('cell','');
			//}
		}
		header('content-type: text/xml');
		$this->view->content = $xml->asXml();
		$this->render('list-xml');
	}

	public function applyAction() {
		$updateFileId = (int)$this->_getParam('updateFileId');
		$updateFile = new UpdateFile();
		$updateFile->updateFileId = $updateFileId;
		$updateFile->populate();
		$license = $updateFile->license;
		if (!strlen($license) > 0) {
			$license = <<<EOL
       Author:  ClearHealth Inc. (www.clear-health.com)        2009
       
       ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
       respective logos, icons, and terms are registered trademarks 
       of ClearHealth Inc.

       Though this software is open source you MAY NOT use our 
       trademarks, graphics, logos and icons without explicit permission. 
       Derivitive works MUST NOT be primarily identified using our 
       trademarks, though statements such as "Based on ClearHealth(TM) 
       Technology" or "incoporating ClearHealth(TM) source code" 
       are permissible.

       This file is licensed under the GPL V3, you can find
       a copy of that license by visiting:
       http://www.fsf.org/licensing/licenses/gpl.html
EOL;
		}
		$updateFile->license = $license;
		$this->view->updateFile = $updateFile;
		$this->render('apply');
	}

	public function processApplyAction() {
		$updateFileId = (int)$this->_getParam('updateFileId');
		$updateFile = new UpdateFile();
		$updateFile->updateFileId = $updateFileId;
		$updateFile->populate();
		$data = $updateFile->data;
		$alterTable = new AlterTable();
		$ret = $alterTable->generateSqlChanges($data);
		if ($ret === true) {
			$alterTable->executeSqlChanges();
			$updateFile->active = 0;
			$updateFile->persist();

			$audit = new Audit();
			$audit->objectClass = 'UpdateManager';
			$audit->userId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
			$audit->message = 'License of update file '.$updateFile->name.' from '.$updateFile->channel.' channel was accepted and updates applied successfully.';
			$audit->dateTime = date('Y-m-d H:i:s');
			$audit->persist();
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function checkAction() {
		$data = array();
		$output = $this->_fetch('check');
		if ($output === false) {
			$data['code'] = 400;
			$data['msg'] = __('There was an error connecting to HealthCloud');
			trigger_error($output,E_USER_NOTICE);
		}
		else {
			$xml = simplexml_load_string($output);
			if (isset($xml->error)) {
				$data['code'] = 201;
				$data['msg'] = (string)$xml->error->errorMsg;
			}
			else {
				$data['code'] = 200;
				$sessVersions = array();
				$versions = array();
				foreach ($xml as $version) {
					$version = (string)$version;
					$sessVersions[$version] = $version;
					$versions[] = $version;
				}
				$this->_session->versions = $sessVersions;
				$data['msg'] = $versions;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function downloadAction() {
		$param = $this->_getParam('version');
		$x = explode('_',$param);
		$version = (int)$x[0];
		$channel = 0;
		if (isset($x[1])) {
			$channel = (int)$x[1];
		}
		$version = array('version'=>$version,'channel'=>$channel);

		$updateFile = new UpdateFile();
		$uploadDir = $updateFile->getUploadDir();
		$error = false;
		if (!is_dir($uploadDir)) {
			$error = $uploadDir.' directory does not exists';
			trigger_error($error,E_USER_NOTICE);
		}
		else if (!is_writable($uploadDir)) {
			$error = $uploadDir.' directory is not writable';
			trigger_error($error,E_USER_NOTICE);
		}

		if ($error !== false) {
			$ret = $error;
		}
		else if (($ret = $this->_fetch('download',$version)) === false) {
			$ret = __('There was an error connecting to HealthCloud');
			trigger_error($ret,E_USER_NOTICE);
		}
		else {
			try {
				$filename = tempnam(sys_get_temp_dir(),'uf_');
				file_put_contents($filename,$ret);
				ob_start();
				readgzfile($filename);
				$ret = ob_get_clean();
				unlink($filename);
				$responseXml = simplexml_load_string($ret);
				if (isset($responseXml->error)) {
					$error = __('There was an error fetching update file');
					$error .= ' Error code: '.$responseXml->error->errorCode.' Error Message: '.$responseXml->error->errorMsg;
					$ret = (string)$responseXml->error->errorMsg;
					trigger_error($error,E_USER_NOTICE);
				}
				else {
					$updateFile->active = 1;
					$updateFile->dateTime = date('Y-m-d H:i:s');
					try {
						$updateFile->verify($ret);
						$updateFile->persist();
						file_put_contents($updateFile->getUploadFilename(),$ret);
						unset($this->_session->versions[$param]);
						$ret = true;
						$next = array_shift($this->_session->versions);
						if ($next !== null) {
							$ret = array('next'=>$next);
						}
					}
					catch (Exception $e) {
						$error = __('Invalid signature');
						$ret = $error.': '.$e->getMessage();
						trigger_error($ret,E_USER_NOTICE);
					}
				}
			}
			catch (Exception $e) {
				$error = __('There was an error with the downloaded file');
				$ret = $error.': '.$e->getMessage();
				$error .= ' Error code: '.$e->getCode().' Error Message: '.$e->getMessage();
				trigger_error($error,E_USER_NOTICE);
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	protected function _fetch($action,$version=null) {
		$ch = curl_init();
		$updateServerUrl = Zend_Registry::get('config')->healthcloud->updateServerUrl;
		$updateServerUrl .= '/'.$action;
		$updateFile = new UpdateFile();
		$data = array();
		$data['apiKey'] = Zend_Registry::get('config')->healthcloud->apiKey;
		if ($version === null) {
			$data['version'] = $updateFile->getAllVersions();
		}
		else {
			$data['version'] = $version;
		}
		$data = http_build_query($data);
		curl_setopt($ch,CURLOPT_URL,$updateServerUrl);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($ch,CURLOPT_USERPWD,'admin:ch3!');
		$ret = curl_exec($ch);
		$curlErrno = curl_errno($ch);
		$curlError = curl_errno($ch);
		curl_close($ch);
		if ($curlErrno) {
			$ret = false;
		}
		return $ret;
	}


	public function uploadAction() {
		$updateFile = new UpdateFile();
		$form = new WebVista_Form(array('name'=>'edit'));
		$form->setAction(Zend_Registry::get('baseUrl') . 'update-manager.raw/process-upload');
		$form->loadORM($updateFile,'updateFile');
		$form->setWindow('winNewUploadId');
		$form->setAttrib('enctype','multipart/form-data');
		$this->view->form = $form;
		$this->render('upload');
	}

	public function processUploadAction() {
		$updateFile = new UpdateFile();
		$uploadDir = $updateFile->getUploadDir();

		if (!is_writable($uploadDir)) {
			$msg = 'tmp directory is not writable';
		}
		else if (!isset($_FILES['uploadFile'])) {
			$msg = __('No uploaded file');
		}
		else if ($_FILES['uploadFile']['error'] !== 0) {
			$msg = __('Error in uploading');
		}
		else if (stripos($_FILES['uploadFile']['type'],'xml') === false) {
			$msg = __('Invalid file format, must be an XML file.');
		}
		else {
			$file = $_FILES['uploadFile'];
			$data = file_get_contents($file['tmp_name']);
			if (!$xml = simplexml_load_string($data)) {
				$msg = __('Invalid xml format.');
			}
		}
		if (isset($msg)) {
			$this->_session->errMsg = $msg;
			throw new Exception($msg);
		}
		$params = $this->_getParam('updateFile');
		$md5sum = md5($data);
		$updateFile->channelId = UpdateFile::USER_CHANNEL_ID;
		$updateFile->channel = UpdateFile::USER_CHANNEL;
		$updateFile->active = 1;
		$updateFile->name = $file['name'];
		$updateFile->mimeType = $file['type'];
		$updateFile->md5sum = $md5sum;
		$updateFile->description = $params['description'];
		$updateFile->dateTime = date('Y-m-d H:i:s');
		$updateFile->persist();

		$filename = $updateFile->getUploadFilename();
		file_put_contents($filename,$data);

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$result = $json->direct(array('updateFileId'=>$updateFile->updateFileId),false);
		$this->getResponse()->setHeader('Content-Type', 'text/html');
		$this->view->result = $result;
		$this->render('process-upload');
	}

	public function viewUploadProgressAction() {
		if (isset($this->_session->errMsg)) {
			$percent = array('err_msg'=>$this->_session->errMsg);
			unset($this->_session->errMsg);
		}
		else {
			$status = apc_fetch('upload_'.$this->_getParam('uploadKey'));
			$percent = 0;
			if ($status['current'] > 0 ) {
				$percent = $status['current']/$status['total']*100;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($percent);
	}

	public function viewDetailsAction() {
		$updateFileId = (int)$this->_getParam('updateFileId');
		$updateFile = new UpdateFile();
		$updateFile->updateFileId = $updateFileId;
		$updateFile->populate();
		$alterTable = new AlterTable();
		$this->view->name = $updateFile->channel.': '.$updateFile->name;
		$this->view->data = $alterTable->generateChanges($updateFile->data);
		$this->render('view-details');
	}

}

