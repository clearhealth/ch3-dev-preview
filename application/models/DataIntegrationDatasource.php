<?php
/*****************************************************************************
*       DataIntegrationDatasource.php
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


class DataIntegrationDatasource extends DataIntegration {

	protected $dataIntegrationDatasourceId;
	protected $guid;
	protected $name;
	protected $datasource;
	protected $handlerType;
	protected $_table = 'dataIntegrationDatasources';
	protected $_primaryKeys = array('dataIntegrationDatasourceId');

	public function getCustomIterator($dbSelect = null) {
		if ($dbSelect === null) {
			$db = Zend_Registry::get('dbAdapter');
			$handlerSelect = $db->select()
					    ->from('handlers','dataIntegrationDatasourceId')
					    ->where('handlerType = ?',$this->handlerType);
			$dbSelect = $db->select()
					->from($this->_table)
					->where('handlerType = ?',$this->handlerType)
					->where('dataIntegrationDatasourceId NOT IN ( '.$handlerSelect->__toString().' )',$this->handlerType)
					->order('name');
		}
		return parent::getIterator($dbSelect);
	}

}
