<?php
/*****************************************************************************
*       NSDR2.php
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
 * Independent class specific for NSDR functionality
 */

class NSDR2 extends NSDR {

	public static function systemStart() {
		$memcache = Zend_Registry::get('memcache');
		$memcache->set(self::$_statusKey,self::$_states[1]);

		// By default all NSDR methods will return an empty/false value
		$methods = array(
			'persist' => 'return false;',
			'populate' => 'return "";',
			'aggregateDisplay' => 'return "";',
			'aggregateDisplayByLine' => 'return "";',
		);

		$nsdrDefinition = new NSDRDefinition();
		$nsdrDefinitionIterator = $nsdrDefinition->getIterator();
		foreach ($nsdrDefinitionIterator as $row) {
			$namespaceAlias = null;
			$ORMClass = null;
			if (strlen($row->aliasFor) > 0 && $row->isNamespaceExists($row->aliasFor)) { // Alias must check first, alias must be canonical
				// temporary implemented this way, it can be changed later
				$namespaceAlias = 'ALIAS:'.$row->aliasFor; // prefix with ALIAS
			}
			else if (self::hasORMClass($row)) {
				$ORMClass = 'ORMCLASS:'.$row->ORMClass; // prefix with ORMCLASS
			}
			foreach ($methods as $method=>$value) {
				$keySuffix = '['.$method.'()]';
				$key = $row->namespace.$keySuffix;
				if ($namespaceAlias !== null) {
					$value = $namespaceAlias.$keySuffix;
				}
				else if ($ORMClass !== null) {
					$value = $ORMClass; // override $value
				}
				trigger_error('NSDR: '.$key.' = '.$value);
				$memcache->set($key,$value);
			}
		}

		$memcache->set(self::$_statusKey,self::$_states[0]);
	}

	public static function systemReload() {
		$ret = false;
		$memcache = Zend_Registry::get('memcache');
		$memcache->set(self::$_statusKey,self::$_states[3]);
		self::systemUnload();
		self::systemStart();
		$ret = true;
		$memcache->set(self::$_statusKey,self::$_states[2]);
		return $ret;
	}

	public static function systemUnload() {
		$memcache = Zend_Registry::get('memcache');
		$memcache->set(self::$_statusKey,self::$_states[5]);

		// By default all NSDR methods will return an empty/false value
		$methods = array(
			'persist' => 'return false;',
			'populate' => 'return "";',
			'aggregateDisplay' => 'return "";',
			'aggregateDisplayByLine' => 'return "";',
		);
		$nsdrDefinition = new NSDRDefinition();
		$nsdrDefinitionIterator = $nsdrDefinition->getIterator();
		foreach ($nsdrDefinitionIterator as $row) {
			foreach ($methods as $method=>$value) {
				$keySuffix = '['.$method.'()]';
				$key = $row->namespace.$keySuffix;
				self::removeNamespace($key);
			}
		}

		$memcache->set(self::$_statusKey,self::$_states[4]);
	}

	/**
	 * Execute memcache value specified by namespace
	 *
	 * @param NSDRBase $nsdrBase
	 * @param mixed $context
	 * @param string $namespace
	 * @param mixed $data
	 * @param int $level Prevents loops for alias
	 * @return mixed
	 */
	protected static function _populateMethod(NSDRBase $nsdrBase,$context,$namespace,$data = null,$level = 0) {
		// $namespace here is in the form of space.name[method()]
		$memcache = Zend_Registry::get('memcache');
		// retrieves logic code in memcache
		$result = $memcache->get($namespace);
		if ($result !== false) { // has cached entry
			if (preg_match('/(.*)\[(.*)\(\)\]$/',$namespace,$matches)) {
				$method = ucfirst($matches[2]);
			}

			if (preg_match('/^([A-Z]+):(.*)/',$result,$resultMatches)) {
				$keyPrefix = $resultMatches[1];
				$value = $resultMatches[2];
				switch ($keyPrefix) {
					case 'ORMCLASS':
						$obj = new $value();
						$methodCall = 'nsdrPopulate';
						if (isset($method)) {
							if (method_exists($obj,'nsdr'.$method)) {
								$methodCall = 'nsdr'.$method;
							}
							else {
								// temporarily lookup the method from NSDRBase
								if (method_exists($nsdrBase,$method)) {
									return $nsdrBase->$method($nsdrBase,$context,$data);
								}
								$msg = __('Method specified does not exists').': '.lcfirst($method).'()';
								throw new Exception($msg);
							}
						}
						return $obj->$methodCall($nsdrBase,$context,$data);
						break;
					case 'ALIAS':
						if ($level < 1) { // prevents loops
							$level++;
							return self::_populateMethod($nsdrBase,$context,$value,$data,$level);
						}
						break;
					default: // unrecognized prefix
						$msg = __('Unrecognized prefix').': '.$keyPrefix;
						break;
				}
			}
			else {
				$nsdrBase->methods[$namespace] = create_function('$tthis,$context,$data',$result);
				return $nsdrBase->methods[$namespace]($nsdrBase,$context,$data);
			}
		}
		else {
			$msg = __('Namespace does not exist in memcache').': '.$namespace;
		}
		throw new Exception($msg);
	}

	/**
	 * Modified populate version
	 *
	 * @param string $request
	 * @return mixed
	 */
	public static function populate($request) {
		if (is_array($request)) {
			$ret = array();
			foreach ($request as $key=>$value) {
				$ret[$key] = self::populate($value);
			}
			return $ret;
		}
		$memcache = Zend_Registry::get('memcache');
		// $request is in the form of 1234::com.clearhealth.person[populate()]
		// tokenize $request
		if (self::systemStatus() != self::$_states[0] && self::systemStatus() != self::$_states[2]) {
			return __('NSDR sub-system is not running and needs to be started').': '.$request;
		}
		$tokens = explode('::',$request);
		if (count($tokens) !== 2) {
			return __('Invalid request').': '.$request;
		}
		$context = $tokens[0]; // contains context
		$namespace = $tokens[1]; // set second token to $namespace as default value

		$methods = array('populate()'); // set default populate() method, this must exist
		$attributes = array();

		if (preg_match('/(.*)\[(.*)\]$/',$tokens[1],$matches)) {
			if (isset($matches[1])) { // namespace exists name.space
				$namespace = $matches[1]; // override $namespace default value
			}
			if (isset($matches[2])) { // functions/arguments exists @optional=fieldvalue,orMethod()
				$hasDefaultPersist = false;
				$x = explode(',',$matches[2]);
				foreach ($x as $v) {
					if ($v == 'populate()') continue;
					if (substr($v,0,1) == '@') { // attribute in the form of @key=value
						$x = explode('=',substr($v,1));
						if (!isset($x[1])) {
							$x[1] = '';
						}
						$attributes[$x[0]] = $x[1];
					}
					else {
						$methods[] = $v;
					}
				}
			}
		}

		$result = null;
		$nsdrBase = new NSDRBase();
		$nsdrBase->_nsdrNamespace = $namespace;
		$nsdrBase->_aliasedNamespace = $request;
		$nsdrBase->_attributes = $attributes;
		foreach ($methods as $method) {
			$key = $namespace.'['.$method.']';
			if ($result !== null) {
				$result = self::_execMethod('populate',$nsdrBase,$context,$key,$result);
			}
			else {
				$result = self::_execMethod('populate',$nsdrBase,$context,$key);
			}
		}
		return $result;
	}

	/**
	 * Execute logic code defined in memcache specified by namespace
	 *
	 * @param string $method Either populate or persist, default to populate if invalid method specified
	 * @param NSDRBase $nsdrBase
	 * @param mixed $context
	 * @param string $namespace
	 * @param mixed $data
	 * @param int $level Prevents loops for alias
	 * @return mixed
	 */
	protected static function _execMethod($method,NSDRBase $nsdrBase,$context,$namespace,$data = null,$level = 0) {
		$definedMethods = array('populate','persist');
		if (!in_array($method,$definedMethods)) {
			$method = 'populate';
		}
		$defaultMethod = ucfirst($method);
		// $namespace here is in the form of space.name[method()]
		$memcache = Zend_Registry::get('memcache');
		// retrieves logic code in memcache
		$result = $memcache->get($namespace);
		trigger_error($context.'::'.$namespace.' = '.$result);
		if ($result !== false) { // has cached entry
			if (preg_match('/(.*)\[(.*)\(\)\]$/',$namespace,$matches)) {
				$nsMethod = ucfirst($matches[2]);
			}
			if (preg_match('/^([A-Z]+):(.*)/',$result,$resultMatches)) {
				$keyPrefix = $resultMatches[1];
				$value = $resultMatches[2];
				switch ($keyPrefix) {
					case 'ORMCLASS':
						$obj = new $value();
						$methodCall = 'nsdr'.$defaultMethod;
						if (isset($nsMethod)) {
							if (method_exists($obj,'nsdr'.$nsMethod)) {
								$methodCall = 'nsdr'.$nsMethod;
							}
							else {
								// temporarily lookup the method from NSDRBase
								if (method_exists($nsdrBase,$nsMethod)) {
									return $nsdrBase->$nsMethod($nsdrBase,$context,$data);
								}
								$msg = __('Method specified does not exists').': '.lcfirst($nsMethod).'()';
								throw new Exception($msg);
							}
						}
						return $obj->$methodCall($nsdrBase,$context,$data);
						break;
					case 'ALIAS':
						if ($level < 1) { // prevents loops
							$level++;
							return self::_execMethod($method,$nsdrBase,$context,$value,$data,$level);
						}
						break;
					default: // unrecognized prefix
						$msg = __('Unrecognized prefix').': '.$keyPrefix;
						break;
				}
			}
			else {
				$nsdrBase->methods[$namespace] = create_function('$tthis,$context,$data',$result);
				return $nsdrBase->methods[$namespace]($nsdrBase,$context,$data);
			}
		}
		else {
			$msg = __('Namespace does not exist in memcache').': '.$namespace;
		}
		trigger_error($msg,E_USER_NOTICE);
		return $msg;
	}

	/**
	 * Persist the given namespace/s
	 *
	 * @param string $request
	 * @param array $data
	 * @return boolean
	 * @throw Exception
	 */
	public static function persist($request,Array $data) {
		$memcache = Zend_Registry::get('memcache');
		// $request is in the form of 1234::com.clearhealth.person[populate()]
		// tokenize $request
		if (self::systemStatus() != self::$_states[0] && self::systemStatus() != self::$_states[2]) {
			return __('NSDR sub-system is not running and needs to be started').': '.$request;
		}
		$tokens = explode('::',$request);
		if (count($tokens) !== 2) {
			return __('Invalid request').': '.$request;
		}
		$context = $tokens[0]; // contains context
		$namespace = $tokens[1]; // set second token to $namespace as default value

		$methods = array('persist()'); // set default persist() method, this must exist
		$attributes = array();

		if (preg_match('/(.*)\[(.*)\]$/',$tokens[1],$matches)) {
			if (isset($matches[1])) { // namespace exists name.space
				$namespace = $matches[1]; // override $namespace default value
			}
			if (isset($matches[2])) { // functions/arguments exists @optional=fieldvalue,orMethod()
				$hasDefaultPersist = false;
				$x = explode(',',$matches[2]);
				foreach ($x as $v) {
					if ($v == 'persist()') continue;
					if (substr($v,0,1) == '@') { // attribute in the form of @key=value
						$x = explode('=',substr($v,1));
						if (!isset($x[1])) {
							$x[1] = '';
						}
						$attributes[$x[0]] = $x[1];
					}
					else {
						$methods[] = $v;
					}
				}
			}
		}

		$result = null;
		$nsdrBase = new NSDRBase();
		$nsdrBase->_nsdrNamespace = $namespace;
		$nsdrBase->_aliasedNamespace = $request;
		$nsdrBase->_attributes = $attributes;
		foreach ($methods as $method) {
			$key = $namespace.'['.$method.']';
			if ($result !== null) {
				$result = self::_execMethod('persist',$nsdrBase,$context,$key,$result);
			}
			else {
				$result = self::_execMethod('persist',$nsdrBase,$context,$key,$data);
			}
		}
		return $result;
	}

	public static function extractNamespace($namespace) {
		$x = explode('.',$namespace);
		if (!isset($x[1])) return $namespace;
		$first = array_shift($x);
		$last = array_pop($x);
		$fx = explode('::',$first);
		if (isset($fx[1])) {
			$first = $fx[1];
		}
		array_unshift($x,$first);
		$lx = explode('[',$last);
		array_push($x,$lx[0]);
		$namespace = implode('.',$x);
		return $namespace;
	}

	public static function removeNamespace($key) {
		$memcache = Zend_Registry::get('memcache');
		trigger_error('Delete memcache key:'.$key,E_USER_NOTICE);
		$ret = $memcache->delete($key,0);
		return $ret;
	}

}

