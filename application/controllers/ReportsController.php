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


class ReportsController extends WebVista_Controller_Action
{

	public function init() {
        	$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		$report = new Report();
		$report->id = 1;
		$report->populate();
		//echo $report->toString();
		foreach($report->reportQueries as $query) {
			echo $query->execute()->toXml();
		}
		exit;
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
}
