<?php
/*****************************************************************************
*       ProcessHL7.php
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


class ProcessHL7 extends ProcessAbstract {

	protected $_handlers = array();

	protected function _populateHandlers() {
		$this->_handlers = array();
		try {
			$cacheCodeObjects = Zend_Registry::get('cacheCodeObjects');
		} catch (Exception $e) {
			$cacheCodeObjects = array();
		}
		$handler = new Handler();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($handler->_table)
			       ->where('handlerType = '.Handler::HANDLER_TYPE_HL7)
			       ->where('active = 1');
		$handlerIterator = $handler->getIterator($dbSelect);
		foreach ($handlerIterator as $item) {
			$conditionObject = $item->conditionObject;
			if (!strlen($conditionObject) > 0) {
				$conditionObject = $item->generateDefaultConditionObject();
			}
			$md5 = md5($conditionObject);
			if (!in_array($md5,$cacheCodeObjects)) {
				$cacheCodeObjects[] = $md5;
				eval($conditionObject); // TODO: needs to be validated
			}
			$this->_handlers[] = $item;
		}
		Zend_Registry::set('cacheCodeObjects',$cacheCodeObjects);
	}

	/**
	 * Process condition and do action
	 * @param Handler $handler Handler ORM
	 * @param Audit $audit Audit ORM
	 * @return boolean Return TRUE if successful, FALSE otherwise
	 */
	public function process(Audit $audit) {
		$this->_populateHandlers();

		$ret = true;
		foreach ($this->_handlers as $handler) {
			$result = $this->_doProcess($handler,$audit);
			if ($result === false) {
				// log processing errors
				$processingError = new ProcessingError();
				$processingError->_shouldAudit = false;
				$processingError->auditId = $audit->auditId;
				$processingError->handlerId = $handler->handlerId;
				$processingError->persist();
			}
			$ret |= $result;
		}

		return $ret;
	}

	protected function _doProcess(Handler $handler,Audit $audit) {
		$ret = false;

		$handlerName = Handler::normalizeHandlerName($handler->name);
		$classConditionHandler = $handlerName.'ConditionHandler';
		if (!parent::isParentOf($classConditionHandler,'DataIntegrationConditionHandlerAbstract')) {
			return false;
		}
		if (call_user_func_array(array($classConditionHandler,'matchAudit'),array($audit))) {
			do {
				$classDatasource = $handlerName.'DataIntegrationDatasource';
				if (!parent::isParentOf($classDatasource,'DataIntegrationDatasourceAbstract')) {
					return false;
				}
				$data = call_user_func_array(array($classDatasource,'sourceData'),array($audit));
				switch ($handler->direction) {
					case 'INCOMING':
						$classAction = $handlerName.'DataIntegrationAction';
						if (!parent::isParentOf($classAction,'DataIntegrationActionAbstract')) {
							return false;
						}
						$ret = call_user_func_array(array($classAction,'act'),array($audit,$data));
						break;
					case 'OUTGOING':
						$classDestination = $handlerName.'DataIntegrationDestination';
						if (!parent::isParentOf($classDestination,'DataIntegrationDestinationAbstract')) {
							return false;
						}
						$template = new DataIntegrationTemplate();
						$template->dataIntegrationTemplateId = $handler->dataIntegrationTemplateId;
						$template->populate();
						$ret = call_user_func_array(array($classDestination,'transmit'),array($audit,$template));
						// temporarily set to true, transmit() skeleton does not provide boolean return
						$ret = true;
						break;
				}
			} while(false);
		}
		return $ret;
	}

}
