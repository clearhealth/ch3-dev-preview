<?php
/*****************************************************************************
*	TestCase.php
*
*	Author:  ClearHealth Inc. (www.clear-health.com)	2009
*	
*	ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*	respective logos, icons, and terms are registered trademarks 
*	of ClearHealth Inc.
*
*	Though this software is open source you MAY NOT use our 
*	trademarks, graphics, logos and icons without explicit permission. 
*	Derivitive works MUST NOT be primarily identified using our 
*	trademarks, though statements such as "Based on ClearHealth(TM) 
*	Technology" or "incoporating ClearHealth(TM) source code" 
*	are permissible.
*
*	This file is licensed under the GPL V3, you can find
*	a copy of that license by visiting:
*	http://www.fsf.org/licensing/licenses/gpl.html
*	
*****************************************************************************/

/**
 * PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

class TestCase extends PHPUnit_Framework_TestCase {
	// workaround for error: PDOException: You cannot serialize or unserialize PDO instances
	protected $backupGlobals = false;
	protected $_autoLoggedIn = true;

	public function setUp() {
		$this->_setUpEnv();
		$this->_setUpDB();
		$this->_setUpCache();
		$this->_setUpACL();
		if ($this->_autoLoggedIn) {
			$this->_setupAutoLogin();
		}
	}

	private function _setUpEnv() {
		try {
			date_default_timezone_set(TEST_DATE_TIMEZONE);
		}
		catch (Zend_Exception $e) {
			die($e->getMessage());
		}
	}

	private function _setUpDB() {
		try {
			$dbAdapter = Zend_Db::factory(Zend_Registry::get('config')->database);
			$dbAdapter = Zend_Db::factory(TEST_DB_ADAPTER, array('host'=>TEST_DB_HOST,'username'=>TEST_DB_USERNAME,'password'=>TEST_DB_PASSWORD,'dbname'=>TEST_DB_DBNAME));

			$dbAdapter->query("SET NAMES 'utf8'");
		}
		catch (Zend_Exception $e) {
			die ($e->getMessage());
		}
		Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
		Zend_Registry::set('dbAdapter',$dbAdapter);
	}

	private function _setUpAutoLogin() {
		$user = new User();
		$user->username = TEST_LOGIN_USERNAME;
		$user->populateWithUsername();
		Zend_Auth::getInstance()->getStorage()->write($user);
	}

	private function _setUpCache() {
		$frontendOptions = array('lifetime' => 3600, 'automatic_serialization' => true);
		$backendOptions = array('file_name_prefix' => 'clearhealth', 'hashed_directory_level' => 1, 'cache_dir' => '/tmp/', 'hashed_directory_umask' => 0700);
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		Zend_Registry::set('cache', $cache);

		$cache = new Memcache();
		$cache->connect('127.0.0.1',11211);
		$status = $cache->getServerStatus('127.0.0.1',11211);
		if ($status === 0) {
			// memcache server failed, do error trapping?
		}
		Zend_Registry::set('memcache', $cache);
	}

	private function _setUpACL() {
		$memcache = Zend_Registry::get('memcache');
		$key = 'acl';
		$acl = $memcache->get($key);
		if ($acl === false) {
			$acl = WebVista_Acl::getInstance();
			// populate acl from db
			$acl->populate();
			// save to memcache
			$memcache->set($key,$acl);
		}
		Zend_Registry::set('acl',$acl);
	}
}
