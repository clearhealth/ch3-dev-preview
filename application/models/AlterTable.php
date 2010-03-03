<?php
/*****************************************************************************
*       AlterTable.php
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


class AlterTable {

	protected $_newTables = array(); // list of all new tables
	protected $_tables = array(); // list of all existing tables
	protected $_changes = array(); // diff results container
	protected $_sqlFile = ''; // sqlchanges.sql location
	protected $_sequenceIds = array('-1'=>'');

	public function __construct($filename = 'sqlchanges.sql') {
		$this->_sqlFile = Zend_Registry::get('basePath').'tmp/'.$filename;
	}

	public function getChanges($data) {
		return $this->_changes;
	}

	protected function _populateTableDefinitions(SimpleXMLElement $xml,$withSql = true) {
		$db = Zend_Registry::get('dbAdapter');
		$tableRes = $db->query('SHOW TABLES');
		$tableRes->setFetchMode(Zend_Db::FETCH_NUM);
		$this->_tables = array();
		foreach ($tableRes->fetchAll() as $table) {
			$this->_tables[$table[0]] = true;
		}
		foreach ($this->_tables as $tableName=>$value) {
			$res = $db->query("SHOW COLUMNS FROM `$tableName`");
			$res->setFetchMode(Zend_Db::FETCH_ASSOC);
			$this->_tables[$tableName] = array();
			foreach ($res->fetchAll() as $row) {
				$this->_tables[$tableName][$row['Field']] = $row;
			}
		}

		$sql = '';
		foreach ($xml as $table) {
			foreach($table as $structure) {
				$tableName = (string)$structure->attributes()->name;
				if (!isset($this->_tables[$tableName])) {
					$this->_tables[$tableName] = true;
					$this->_newTables[$tableName] = true;
				}
				trigger_error('processing table: '.$tableName,E_USER_NOTICE);
				switch ($structure->getName()) {
					case 'table_structure':
						$sql .= $this->_checkTableStructure($structure,$withSql); // check with/without generating sql statements
						break;
					case 'table_data':
						$sql .= $this->_checkTableData($structure,$withSql); // check with/without generating sql statements
						break;
				}
			}
		}
		return $sql;
	}

	public function generateChanges($data) {
		$this->_changes = array();
		if (!$xml = simplexml_load_string($data)) {
			$msg = __('Invalid XML file');
			trigger_error($msg,E_USER_NOTICE);
			return false;
		}
		$this->_populateTableDefinitions($xml,false);

		return $this->_changes;
	}

	public function executeSqlChanges() {
		$dbParams = Zend_Registry::get('config')->database->params;
		$cmd = 'mysql -f -u '.$dbParams->username;
		if (strlen($dbParams->password) > 0) {
			$cmd .= ' -p '.$dbParams->password;
		}
		$cmd .= ' '.$dbParams->dbname.' < '.$this->_sqlFile;
		trigger_error('Executing command: '.$cmd,E_USER_NOTICE);
		return exec($cmd);
	}

	public function generateSqlChanges($data) {
		if (!$xml = simplexml_load_string($data)) {
			$msg = __('Invalid XML file');
			trigger_error($msg,E_USER_NOTICE);
			return $msg;
		}
		if (!$fd = fopen($this->_sqlFile,'w')) {
			$msg = __('Could not open file: '.$this->_sqlFile);
			trigger_error($msg,E_USER_NOTICE);
			return $msg;
		}
		$sql = $this->_populateTableDefinitions($xml);
		fwrite($fd,$sql);
		fclose($fd);
		return true;
	}

	protected function _checkTableStructure($structure,$withSql = true) {
		$ret = false;
		$sql = '';
		$tableName = (string)$structure->attributes()->name;
		trigger_error("Checking structure for table: $tableName",E_USER_NOTICE);
		if (isset($this->_tables[$tableName]) && !isset($this->_newTables[$tableName])) {
			trigger_error("Table $tableName exists",E_USER_NOTICE);
			$retval = $this->_checkTableFields($tableName,$structure);
			$ctr = count($retval);
			if ($ctr > 0) {
				$sql .= implode("\n",$retval);
				$this->_changes[] = $ctr.' alter statements for table '.$tableName;
			}
		}
		else {
			$msg = 'New table '.$tableName;
			$this->_changes[] = $msg;
			trigger_error($msg,E_USER_NOTICE);
			$sql = "CREATE TABLE `{$tableName}` (\n";
			$keys = array();
			foreach($structure as $objType => $field) {
				if ($objType == 'field') {
					$fieldName = (string)$field->attributes()->Field;
					$sql .= "\t`{$fieldName}` {$field->attributes()->Type} " . (($field->attributes()->Null == 'NO') ? ' NOT NULL ' : ' NULL ') . ",\n";
					// table structure in global _tables container variable
					$row = array();
					$row['Field'] = $fieldName;
					$row['Type'] = (string)$field->attributes()->Type;
					$row['Null'] = (string)$field->attributes()->Null;
					$row['Key'] = (string)$field->attributes()->Key;
					$row['Default'] = (string)$field->attributes()->Default;
					$row['Extra'] = (string)$field->attributes()->Extra;
					if ($this->_tables[$tableName] === true) {
						$this->_tables[$tableName] = array();
					}
					$this->_tables[$tableName][$row['Field']] = $row;
				}
				elseif ($objType == 'key') {
					$xmlKeyName = (string)$field->attributes()->Key_name;
					if ($field->attributes()->Key_name == 'PRIMARY') {
						$xmlKeyName = 'PRIMARY KEY';
					}
					elseif($field->attributes()->Non_unique == 0) {
						$xmlKeyName = "UNIQUE KEY `" . $xmlKeyName . "`";
					}
					elseif($field->attributes()->Non_unique == 1) {
						$xmlKeyName = "KEY `" . $xmlKeyName . "`";
					}

					if (!isset($keys[$xmlKeyName])) {
						$keys[$xmlKeyName] = array();
					}
					$keys[$xmlKeyName][] = (string)$field->attributes()->Column_name;
				}
			}
			if (!$withSql) {
				return '';
			}

			foreach ($keys as $keyName => $keyData) {
				$sql .= "\t$keyName (`" . implode('`,`',$keyData) . "`),\n";
			}
			$sql = substr($sql,0,-2) . "\n";
			$sql .= ") ENGINE=INNODB DEFAULT CHARSET=utf8;\n\n";
		}
		return $sql;
	}

	protected function _checkTableData($structure,$withSql = true) {
		$db = Zend_Registry::get('dbAdapter');
		$ret = false;
		$tableName = (string)$structure->attributes()->name;
		trigger_error("Checking data for table: $tableName",E_USER_NOTICE);
		$rows = array();
		foreach($structure as $row) {
			if ((string)$row->getName() != 'row') {
				continue;
			}
			$ret = true;
			$tableColumns = $this->_tables[$tableName];
			$primaryKey = null;
			foreach ($row as $objType => $field) {
				$fieldName = (string)$field->attributes()->name;
				if ($objType != 'field' || !array_key_exists($fieldName,$tableColumns)) {
					continue;
				}
				$fieldValue = (string)$field;
				if ($fieldName == 'guid') {
					$sqlSelect = $db->select()
							->from($tableName)
							->where('guid != ?','')
							->where('guid = ?',$fieldValue);
					if ($guidRow = $db->fetchRow($sqlSelect)) { // data already exists
						continue 2; // proceed to the outer loop
					}
				}
				if (preg_match('/\[@lastSequenceId(.*)\]/',$fieldValue,$matches)) {
					$key = -1;
					if (strlen($matches[1]) > 0) {
						$index = substr($matches[1],1);
						if (isset($this->_sequenceIds[$index])) {
							$key = $index;
						}
					}
					$fieldValue = $this->_sequenceIds[$key];
				}
				$tableColumns[$fieldName] = $fieldValue;
				if ($this->_tables[$tableName][$fieldName]['Key'] == 'PRI' && !isset($matches[1])) {
					$primaryKey = $fieldName;
				}
			}
			if ($withSql && $primaryKey !== null) {
				if (preg_match('/\[@nextSequenceId(.*)\]/',$tableColumns[$primaryKey],$matches)) {
					$tableColumns[$primaryKey] = WebVista_Model_ORM::nextSequenceId();
					$key = -1;
					if (strlen($matches[1]) > 0) {
						$key = substr($matches[1],1);
					}
					$this->_sequenceIds[$key] = $tableColumns[$primaryKey];
					trigger_error('nextSequenceId generated for: '.$tableName.'.'.$primaryKey,E_USER_NOTICE);
				}
			}
			$rows[] = $tableColumns;
		}
		$ctr = count($rows);
		if ($ctr > 0) {
			$this->_changes[] = $ctr.' insert statements to table '.$tableName;
		}
		if (!$withSql) {
			return $ret;
		}
		$columnNames = array();
		foreach ($this->_tables[$tableName] as $fieldName=>$col) {
			$columnNames[] = $db->quoteIdentifier($fieldName);
		}
		$sql = "INSERT INTO ".$db->quoteTableAs($tableName)." (".implode(',',$columnNames).") VALUES";
		foreach ($rows as $row) {
			$sql .= PHP_EOL.'('.$db->quote($row).'),';
		}
		$sql = substr($sql,0,-1).';'.PHP_EOL;
		return $sql;
	}

	protected function _checkTableFields($tableName,$structure) {
		$sql = array();
		if (isset($this->_newTables[$tableName])) { // new table
			return $sql;
		}
		$db = Zend_Registry::get('dbAdapter');
		$res = $db->query("SHOW COLUMNS FROM `$tableName`");
		$res->setFetchMode(Zend_Db::FETCH_ASSOC);
		foreach ($structure as $objType => $fieldData) {
			if ($objType == 'field') {
				$xmlFieldName = (string)$fieldData->attributes()->Field;
				if (!isset($this->_tables[$tableName][$xmlFieldName])) {
					$sql[] = "ALTER TABLE `$tableName` ADD `$xmlFieldName` " . (string)$fieldData->attributes()->Type . (($fieldData->attributes()->Null == "NO") ? " NOT NULL " : " NULL ") . ";\n";
				}
			}
		}
		return $sql;
	}

}
