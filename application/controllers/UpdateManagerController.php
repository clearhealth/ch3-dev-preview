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
		$updateFileIterator = $updateFile->getIteratorActiveBlob();
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
		$updateFileIterator = $updateFile->getIteratorActiveBlob();
		$alterTable = new AlterTable();
		foreach ($updateFileIterator as $item) {
			$changes = $alterTable->generateChanges($item->blob['data']);
			if (!count($changes) > 0) {
				continue;
			}
			$parent = $xml->addChild('row');
			$parent->addAttribute('id',$item->updateFileId);
			$parent->addChild('cell',$item->name.' (v'.$item->version.')');
			$parent->addChild('cell','');
			foreach ($changes as $key=>$val) {
				$child = $parent->addChild('row');
				$child->addAttribute('id',$item->updateFileId.'_'.$key);
				$child->addChild('cell',$val);
				$child->addChild('cell','');
			}
		}
		header('content-type: text/xml');
		$this->view->content = $xml->asXml();
		$this->render('list-xml');
	}

	public function applyAction() {
		$id = (int)$this->_getParam('id');
		$updateFile = new UpdateFile();
		$updateFile->updateFileId = $id;
		$updateFile->populate();
		$data = $updateFile->blob['data'];
		$alterTable = new AlterTable();
		$ret = $alterTable->generateSqlChanges($data);
		if ($ret === true) {
			$alterTable->executeSqlChanges();
			$updateFile->active = 0;
			$updateFile->persist(false);
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
				$data['msg'] = (string)$xml->version;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function downloadAction() {
		$ret = $this->_fetch('download');
		if ($ret === false) {
			$ret = __('There was an error connecting to HealthCloud');
			trigger_error($ret,E_USER_NOTICE);
		}
		else {
			try {
				$responseXml = simplexml_load_string($ret);
				if (isset($responseXml->error)) {
					$error = __('There was an error fetching update file');
					$error .= ' Error code: '.$responseXml->error->errorCode.' Error Message: '.$responseXml->error->errorMsg;
					$ret = (string)$responseXml->error->errorMsg;
					trigger_error($error,E_USER_NOTICE);
				}
				else {
					$updateFile = new UpdateFile();
					$updateFile->active = 1;
					$updateFile->dateTime = date('Y-m-d H:i:s');
					$updateFile->blob = array('updateFileId' => $updateFile->updateFileId,'data' => $ret);
					try {
						$updateFile->verify();
						$updateFile->persist();
						$ret = true;
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

	protected function _fetch($action) {
		$ch = curl_init();
		$updateServerUrl = Zend_Registry::get('config')->healthcloud->updateServerUrl;
		$updateServerUrl .= '/'.$action;
		$updateFile = new UpdateFile();
		$data = array();
		$data['apiKey'] = Zend_Registry::get('config')->healthcloud->apiKey;
		$data['version'] = $updateFile->getLatestVersion();
		curl_setopt($ch,CURLOPT_URL,$updateServerUrl);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
		$ret = curl_exec($ch);
		$curlErrno = curl_errno($ch);
		$curlError = curl_errno($ch);
		curl_close($ch);
		if ($curlErrno) {
			$ret = false;
		}
		return $ret;
	}

}

