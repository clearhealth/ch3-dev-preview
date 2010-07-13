<?php
/*****************************************************************************
*       ClaimsController.php
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


class ClaimsController extends WebVista_Controller_Action {

	public function indexAction() {
		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice','Building','Room'));
		$this->view->facilityIterator = $facilityIterator;
		$this->view->insurers = InsuranceProgram::getInsurancePrograms();
		$this->render();
	}

	public function advancedFiltersAction() {
		$this->view->balanceOperators = Claim::balanceOperators();
		$this->render();
	}

	public function listAction() {
		$filters = array();
		$dateRange = $this->_getParam('dateRange'); // mm/dd/yyyy-mm/dd/yyyy
		$dosStart = date('Y-m-d');
		$dosEnd = $dosStart;
		if ($dateRange !== null) {
			$x = explode('-',$dateRange);
			$dosStart = date('Y-m-d',strtotime($x[0]));
			$dosEnd = date('Y-m-d',strtotime($x[1]));
		}
		$filters['DOSDateRange'] = array('start'=>$dosStart,'end'=>$dosEnd);
		$facility = $this->_getParam('facility'); // practiceId_buildingId_roomId
		if (strlen($facility) > 0) {
			$x = explode('_',$facility);
			$practiceId = $x[0];
			$buildingId = $x[1];
			$roomId = $x[2];
			$filters['facility'] = array('practice'=>$practiceId,'building'=>$buildingId,'room'=>$roomId);
		}
		$filters['insurer'] = (int)$this->_getParam('insurer');
		$total = $this->_getParam('total');
		if ($total !== null) {
			$filters['total'] = (float)$total;
		}
		$paid = $this->_getParam('paid');
		if ($paid !== null) {
			$filters['paid'] = (float)$paid;
		}
		$writeoff = $this->_getParam('writeoff');
		if ($writeoff !== null) {
			$filters['writeoff'] = (float)$writeoff;
		}
		$balance = $this->_getParam('balance'); // operator::balance::balance2
		if ($balance !== null) {
			$x = explode('::',$balance);
			$balance1 = $x[1];
			$balance2 = 0;
			if ($x[0] == 'between') {
				$balance2 = $x[2];
			}
			$balanceOperator = $x[0];
			$balanceOperators = Claim::balanceOperators();
			if (!isset($balanceOperators[$balanceOperator])) {
				$balanceOperator = '=';
			}
			$filters['balance'] = array('operator'=>$balanceOperator,'operand1'=>$balance1,'operand2'=>$balance2);
		}

		$claimIterator = new ClaimIterator();
		$claimIterator->setFilters($filters);
		$data = array();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
