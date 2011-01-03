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
			$parent->addChild('cell',$item->status);
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
		$filename = $updateFile->getUploadFilename();
		if (file_exists($filename)) {
			$size = sprintf("%u",filesize($filename));
			$units = array('B','KB','MB','GB','TB');
			$pow = floor(($size?log($size):0)/log(1024));
			$pow = min($pow,count($units)-1);
			$size /= pow(1024,$pow);
			if (($pow == 2 && round($size,1) > 10) ||$pow > 2) { // queue if > 10 MB
				$updateFile->queue = 1;
				$updateFile->status = 'Pending';
				$updateFile->persist();
			}
		}
		$audit = new Audit();
		$audit->objectClass = 'UpdateManager';
		$audit->userId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$audit->message = 'License of update file '.$updateFile->name.' from '.$updateFile->channel.' channel was accepted';
		$audit->dateTime = date('Y-m-d H:i:s');

		if ($updateFile->queue) {
			$audit->message .= ' and updates pending to apply.';
			$ret = true;
		}
		else {
			$updateFile->queue = 0;
			$alterTable = new AlterTable();
			$ret = $alterTable->generateSqlChanges($filename);
			if ($ret === true) {
				$alterTable->executeSqlChanges();
				//$updateFile->active = 0;
				$updateFile->status = 'Completed';
				$updateFile->persist();
				$audit->message .= ' and updates applied successfully.';
			}
			else {
				$audit->message .= ' and updates failed to apply.';
				$updateFile->status = 'Error: '.$ret;
				$updateFile->persist();
			}
		}
		$audit->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

	public function checkAction() {
		$data = array();
		$sessVersions = array();
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
				$versions = array();
				foreach ($xml as $update) {
					$version = (string)$update->version;
					$tmp = array();
					foreach ($update as $key=>$value) {
						$tmp[$key] = (string)$value;
					}
					$sessVersions[$version] = $tmp;
					$versions[] = $version;
				}
				$data['msg'] = $versions;
			}
		}
		$this->_session->versions = $sessVersions;
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

				$updateFile->active = 1;
				$updateFile->dateTime = date('Y-m-d H:i:s');
				try {
					$updateFile->populateWithArray($this->_session->versions[$param]);
					if (substr($updateFile->name,-3) == '.gz') {
						$updateFile->name = substr($updateFile->name,0,-3);
					}
					$updateFile->version = $version['version'];
					$updateFile->persist();
					$contents = $updateFile->verify($filename);
					//file_put_contents($updateFile->getUploadFilename(),$contents);
					unset($this->_session->versions[$param]);
					$ret = true;
					list($next,$val) = each($this->_session->versions);
					if ($next !== null) {
						$ret = array('next'=>$next);
					}
				}
				catch (Exception $e) {
					$updateFile->setPersistMode(WebVista_Model_ORM::DELETE);
					$error = __('Invalid signature');
					$ret = $error.': '.$e->getMessage();
					trigger_error($ret,E_USER_NOTICE);
				}
				$updateFile->persist();
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
		if ($curlErrno) {
			trigger_error(curl_error($ch));
			$ret = false;
		}
		curl_close($ch);
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

		if (!is_dir($uploadDir)) {
			$msg = $uploadDir.' directory does not exist';
		}
		else if (!is_writable($uploadDir)) {
			$msg = $uploadDir.' directory is not writable';
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
		}
		if (isset($msg)) {
			$this->_session->errMsg = $msg;
			throw new Exception($msg);
		}
		$params = $this->_getParam('updateFile');
		$updateFile->channelId = UpdateFile::USER_CHANNEL_ID;
		$updateFile->channel = UpdateFile::USER_CHANNEL;
		$updateFile->active = 1;
		$updateFile->name = $file['name'];
		$updateFile->mimeType = $file['type'];
		$updateFile->md5sum = md5_file($file['tmp_name']);
		$updateFile->description = $params['description'];
		$updateFile->dateTime = date('Y-m-d H:i:s');
		$updateFile->persist();

		move_uploaded_file($file['tmp_name'],$updateFile->getUploadFilename());

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
		$this->view->data = $alterTable->generateChanges($updateFile->getUploadFilename());
		$this->render('view-details');
	}

	public function processDeleteAction() {
		$param = $this->_getParam('id');
		$ids = explode(',',$param);
		$ret = false;
		foreach ($ids as $updateFileId) {
			if (!$updateFileId > 0) continue;
			$ret = true;
			$updateFile = new UpdateFile();
			$updateFile->updateFileId = (int)$updateFileId;
			$updateFile->populate();
			if (!strlen($updateFile->version) > 0) continue;
			//$updateFile->active = 0;
			$updateFile->setPersistMode(WebVista_Model_ORM::DELETE);
			$updateFile->persist();
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($ret);
	}

}

