<?php
/*****************************************************************************
*       ReportsController.php
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


class ReportsController extends WebVista_Controller_Action {

	public function init() {
        	$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		$this->render('index');
		return;
		$report = new Report();
		$report->id = 1;
		$report->populate();
		//echo $report->toString();
		foreach($report->reportQueries as $query) {
			echo $query->execute()->toXml();
		}
		exit;
	}

	public function listReportsAction() {
		$xml = new SimpleXMLElement('<rows/>');
		ReportBaseClosure::generateXMLTree($xml);
                header('content-type: text/xml');
		$this->view->content = $xml->asXml();
		$this->render('list');
	}

	public function pdfTemplateAction() {
		$reportTemplateId = (int)$this->_getParam('reportTemplateId');
		setlocale(LC_CTYPE, 'en_US');
		$xmlData =  PdfController::toXML(array(),'FlowSheet',null);
                $this->_forward('pdf-merge-attachment','pdf', null, array('attachmentReferenceId' => $reportTemplateId,'xmlData'=>$xmlData));
	}
	
	public function flowSheetTemplateAction() {
		$personId = (int)$this->_getParam('personId');
		$patient = new Patient();
		$patient->personId = $personId;
		$patient->populate();
		$vitalSignIter = new VitalSignGroupsIterator();
		$vitalSignIter->setFilter(array("personId" => $personId));
		$xmlData =  PdfController::toXML($patient,'Patient',null);
		$xmlData .= "<VitalSignGroups>";
		$loop = 0;
		foreach($vitalSignIter as $vitalGroup) {
			$xmlData .=  PdfController::toXML($vitalGroup,'VitalSignGroup',null);
			if ($loop > 5) exit;
			$loop++;
		}
		$xmlData .= "</VitalSignGroups>";
		//header('Content-type: text/xml;');
		//echo $xmlData;exit;
		$this->_forward('pdf-merge-attachment','pdf', null, array('attachmentReferenceId' => '5','xmlData'=>$xmlData));
	}

	function binaryTemplateAction() {
		$templateId = (int) $this->_getParam('templateId');
		$reportTemplate = new ReportTemplate();
		$reportTemplate->reportTemplateId = $templateId;
		$reportTemplate->populate();
		$this->getResponse()->setHeader('Content-Type', 'application/pdf');
		$this->view->content = $reportTemplate->template;
		$this->render();
	} 

	public function getReportAction() {
		$baseId = (int)$this->_getParam('baseId');
		$data = array(
			'filters'=>array(),
			'views'=>array(),
		);
		$reportBase = new ReportBase();
		$reportBase->reportBaseId = $baseId;
		$reportBase->populate();
		foreach ($reportBase->reportFilters as $reportFilter) {
			$filter = array();
			$filter['id'] = $reportFilter->id;
			$filter['name'] = $reportFilter->name;
			$filter['defaultValue'] = $reportFilter->defaultValue;
			$filter['type'] = $reportFilter->type;
			$filter['options'] = $reportFilter->options;
			$list = null;
			if ($reportFilter->type == ReportBase::FILTER_TYPE_ENUM) {
				$enumerationClosure = new EnumerationClosure();
				$filter['enums'] = array();
				$paths = $enumerationClosure->generatePaths($reportFilter->enumName['id']);
				foreach ($paths as $id=>$name) {
					$filter['enums'][] = array('id'=>$id,'name'=>$name);
				}
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_QUERY) {
				$reportQuery = new ReportQuery();
				$reportQuery->reportQueryId = (int)$reportFilter->query;
				$reportQuery->populate();
				$filter['queries'] = $reportQuery->executeQuery();
				if ($reportFilter->includeBlank) {
					array_unshift($filter['queries'],array('id'=>'','name'=>'&amp;nbsp;'));
				}
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_BUILDING) {
				$orm = new Building();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'buildingId',
					'name'=>'displayName',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_PRACTICE) {
				$orm = new Practice();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'practiceId',
					'name'=>'name',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_PROVIDER) {
				$orm = new Provider();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'personId',
					'name'=>'displayName',
				);
			}
			else if ($reportFilter->type == ReportBase::FILTER_TYPE_LIST_ROOM) {
				$orm = new Room();
				$list = array(
					'ormIterator'=>$orm->getIterator(),
					'id'=>'roomId',
					'name'=>'displayName',
				);
			}
			if ($list !== null) {
				$filter['lists'] = array();
				foreach ($list['ormIterator'] as $row) {
					$filter['lists'][] = array('id'=>$row->{$list['id']},'name'=>htmlspecialchars($row->{$list['name']}));
				}
			}
			$data['filters'][] = $filter;
		}
		$reportView = new ReportView();
		$filters = array(
			'reportBaseId'=>$reportBase->reportBaseId,
			'active'=>1,
		);
		$reportViewIterator = $reportView->getIteratorByFilters($filters);
		foreach ($reportViewIterator as $view) {
			$row = array();
			$row['id'] = $view->reportViewId;
			$row['data'] = array();
			$row['data'][] = $view->displayName;
			$row['data'][] = $view->runQueriesImmediately;
			$row['data'][] = (strlen($view->showResultsIn) > 0)?$view->showResultsIn:'grid';
			$data['views'][] = $row;
		}
		$this->view->filterTypes = ReportBase::getFilterTypes();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function getResultsAction() {
		$viewId = (int)$this->_getParam('viewId');
		$params = $this->_getAllParams();
		$filterParams = array();
		foreach ($params as $key=>$value) {
			if (substr($key,0,7) != 'filter_') continue;
			$index = substr($key,7);
			$x = explode('_',$index);
			if (!isset($x[1])) continue;
			$index = str_replace('_','.',$index);
			$filterParams[$index] = $value;
		}

		$result = ReportBase::generateResults($viewId,$filterParams);
		$data = $result['data'];
		if (isset($result['value'])) {
			$value = $result['value'];
			switch ($result['type']) {
				case 'file':
					return $this->_forward('flat','files',null,array('data'=>$value));
				case 'xml':
					return $this->_forward('xml','files',null,array('data'=>$value));
				case 'pdf':
					return $this->_forward('pdf-merge-attachment','pdf',null,array('attachmentReferenceId'=>$value['attachmentReferenceId'],'xmlData'=>$value['xmlData']));
				case 'graph': // to be implemented
					break;
				case 'pdr':
					return $this->_forward('flat','files',null,array('data'=>$value));
			}
		}
		//trigger_error(print_r($data,true),E_USER_NOTICE);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
