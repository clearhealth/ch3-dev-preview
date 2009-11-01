<?php
/*****************************************************************************
*       ReportQuery.php
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


class ReportQuery extends WebVista_Model_ORM {
	protected $reportQueryId;
	protected $systemName;
	protected $uuid;
	protected $query;
	protected $filters = array();
	protected $_filters = array();
	protected $_primaryKeys = array('reportQueryId');
	protected $_table = "reportQueries";
	protected $_data = array();
	
	function __construct() {
		parent::__construct();
	}

	function execute() {
		$db = Zend_Registry::get('dbAdapter');
		$this->_data = $db->query($this->_filterQuery())->fetchAll();
		return $this;
	}

	function _filterQuery() {
		$query = $this->query;
		$savedFilters = new Zend_Session_Namespace('ReportsController');
		foreach($this->_filters as $key => $filter) {
			switch ($filter['type']) {
				case 'eval':
					$val = eval($filter['eval']);
					$query = preg_replace("/\[$key\]/",$val,$query);
					break;
			}
		}
		return $query;
	}

	function postPopulate() {
		$this->_filters = unserialize($this->filters);
	}

	function toXml() {
		return WebVista_Model_ORM::recurseXml($this->_data,$this->systemName);
	}
}
