<?php
/*****************************************************************************
*       AccountsController.php
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


class AccountsController extends WebVista_Controller_Action {

	protected $_session;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function indexAction() {
		if (!isset($this->_session->filters)) {
			$filters = array();
			$filters['DateStart'] = date('Y-m-d',strtotime('-1 month'));
			$filters['DateEnd'] = date('Y-m-d');
			$filters['facilities'] = array();
			$filters['payers'] = array();
			$filters['providers'] = array();
			$tmp = array('active'=>0,'operator'=>'=','operand1'=>'','operand2'=>'');
			$filters['billed'] = $tmp;
			$filters['paid'] = $tmp;
			$filters['writeoff'] = $tmp;
			$filters['balance'] = $tmp;
			$this->_session->filters = $filters;
		}
		$this->view->filters = $this->_session->filters;

		$this->render();
	}

	public function listAction() {
		$sessions = $this->_session->filters;
		$filters = array();
		$filters['dateRange'] = array('start'=>$sessions['DateStart'],'end'=>$sessions['DateEnd']);
		if (isset($sessions['facilities']) && count($sessions['facilities']) > 0) { // practiceId_buildingId_roomId
			foreach ($sessions['facilities'] as $key=>$value) {
				if (!$value) continue;
				if (!isset($filters['facilities'])) $filters['facilities'] = array();
				$x = explode('_',$key);
				$practiceId = $x[0];
				$buildingId = $x[1];
				$roomId = $x[2];
				$filters['facilities'][] = array('practice'=>$practiceId,'building'=>$buildingId,'room'=>$roomId);
			}
		}
		if (isset($sessions['payers'])&& count($sessions['payers']) > 0) {
			foreach ($sessions['payers'] as $key=>$value) {
				if (!$value) continue;
				if (!isset($filters['payers'])) $filters['payers'] = array();
				$filters['payers'][] = $key;
			}
		}
		if (isset($sessions['providers'])&& count($sessions['providers']) > 0) {
			foreach ($sessions['providers'] as $key=>$value) {
				if (!$value) continue;
				if (!isset($filters['providers'])) $filters['providers'] = array();
				$filters['providers'][] = $key;
			}
		}
		$filters['billed'] = isset($sessions['billed'])?$sessions['billed']:0;
		$filters['paid'] = isset($sessions['paid'])?$sessions['paid']:0;
		$filters['writeoff'] = isset($sessions['writeoff'])?$sessions['writeoff']:0;
		$filters['balance'] = isset($sessions['balance'])?$sessions['balance']:0;

		$rows = array();
		$claimLines = ClaimLine::listAccounts($filters);
		$miscCharges = MiscCharge::listAccounts($filters);
		$payments = Payment::listAccounts($filters);
		// claim lines, misc charges and payments
		$appointmentId = 0;
		$list = array_merge($claimLines,$miscCharges);
		foreach ($list as $account) {
			$billed = (float)$account['billed'];
			$paid = (float)$account['paid'];
			$writeoff = (float)$account['writeOff'];
			$balance = $billed - ($paid + $writeoff);

			$names = array('billed','paid','writeoff','balance');
			foreach ($names as $name) {
				if (!isset($filters[$name]) || !$filters[$name]['active']) continue;
				$operator = $filters[$name]['operator'];
				$operand1 = $filters[$name]['operand1'];
				$operand2 = $filters[$name]['operand2'];
				if ($operator == '=' && !($$name == $operand1)) continue 2;
				else if ($operator == '>' && !($$name > $operand1)) continue 2;
				else if ($operator == '>=' && !($$name >= $operand1)) continue 2;
				else if ($operator == '<' && !($$name < $operand1)) continue 2;
				else if ($operator == '<=' && !($$name <= $operand1)) continue 2;
				else if ($operator == 'between' && $operand2 > 0 && !($$name >= $operand1 && $$name <= $operand2)) {
					continue 2;
				}
			}

			$rows[] = array(
				'id'=>$account['id'],
				'data'=>array(
					substr($account['dateOfTreatment'],0,10), // Date
					substr($account['dateBilled'],0,10), // Date Billed
					$account['patientName'], // Patient
					$account['payer'], // Payer
					'$'.$billed, // Billed
					'$'.$paid, // Paid
					'$'.$writeoff, // Write Off
					'$'.abs($balance), // Balance
					$account['facility'], // Facility
					$account['providerName'], // Provider
				),
			);
		}
		$data = array('rows'=>$rows);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function advancedFiltersAction() {
		$this->view->balanceOperators = Claim::balanceOperators();
		$filters = $this->_session->filters;
		$this->view->filters = $filters;
		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice','Building','Room'));
		$facilities = array();
		foreach($facilityIterator as $facility) {
			$key = $facility['Practice']->practiceId.'_'.$facility['Building']->buildingId.'_'.$facility['Room']->roomId;
			$name = $facility['Practice']->name.'->'.$facility['Building']->name.'->'.$facility['Room']->name;
			$facilities[$key] = $name;
		}
		$this->view->facilities = $facilities;
		$this->render();
	}

	public function setFiltersAction() {
		$params = $this->_getParam('filters');
		if (is_array($params)) {
			$filters = $this->_session->filters;
			foreach ($params as $key=>$value) {
				$filters[$key] = $value;
			}
			$this->_session->filters = $filters;
		}
		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function patientAction() {
		if (!isset($this->_session->patientFilters)) $this->_session->patientFilters = array();
		$filters = $this->_session->patientFilters;
		if (!isset($filters['dateStart'])) {
			$filters['dateStart'] = date('Y-m-d',strtotime('-1 week'));
			$this->_session->patientFilters = $filters;
		}
		if (!isset($filters['dateEnd'])) {
			$filters['dateEnd'] = date('Y-m-d');
			$this->_session->patientFilters = $filters;
		}
		$this->view->filters = $filters;
		$this->view->personId = (int)$this->_getParam('personId');

		$facilities = array(''=>'');
		$facilityIterator = new FacilityIterator();
		$facilityIterator->setFilter(array('Practice','Building','Room'));
		foreach($facilityIterator as $facility) {
			$key = $facility['Practice']->practiceId.'_'.$facility['Building']->buildingId.'_'.$facility['Room']->roomId;
			$name = $facility['Practice']->name.'->'.$facility['Building']->name.'->'.$facility['Room']->name;
			$facilities[$key] = $name;
		}
		$this->view->facilities = $facilities;
		$payers = array(''=>'');
		foreach (InsuranceProgram::getInsurancePrograms() as $key=>$value) {
			$payers[$key] = $value;
		}
		$this->view->payers = $payers;
		$providers = array(''=>'');
		$provider = new Provider();
		foreach ($provider->getIter() as $row) {
			$providers[$row->personId] = $row->displayName;
		}
		$this->view->providers = $providers;
		$users = array(''=>'');
		$db = Zend_Registry::get('dbAdapter');
		$user = new User();
		$sqlSelect = $db->select()
				->from($user->_table)
				->order('username');
		foreach ($user->getIterator($sqlSelect) as $row) {
			$users[$row->userId] = $row->username;
		}
		$this->view->users = $users;
		$this->render();
	}

	public function listPatientAccountsAction() {
		$personId = (int)$this->_getParam('personId');
		$rows = array();
		$filters = $this->_session->patientFilters;
		$iterator = new VisitIterator();
		$iterator->setFilters(array(
			'patientId'=>$personId,
			'dateRange'=>$filters['dateStart'].':'.$filters['dateEnd'],
			'facilityId'=>$filters['facilityId'],
			'payerId'=>$filters['payerId'],
			'providerId'=>$filters['providerId'],
			'userId'=>$filters['userId'],
		));
		foreach ($iterator as $item) {
			$visitId = (int)$item->visitId;

			$total = 0;
			$pendingInsurance = 0;
			$paidInsurance = 0;
			$paidPatient = 0;
			foreach (ClaimLine::claimsList(array('visitId'=>$visitId)) as $claim) {
				$total = $claim['claims']['total'] + $claim['miscCharges']['total'];
				$paidInsurance = $claim['payments']['total'] + $claim['writeoffs']['total'];
			}
			$balance = $total - $paidInsurance - $paidPatient;
			$row = array();
			$row['id'] = $visitId;
			$row['data'] = array();
			$row['data'][] = $this->view->baseUrl.'/accounts.raw/list-patient-account-details?visitId='.$visitId;
			$row['data'][] = substr($item->dateOfTreatment,0,10);
			$row['data'][] = $item->insuranceProgram;
			$row['data'][] = '$'.abs($total);
			$row['data'][] = '$'.abs($pendingInsurance);
			$row['data'][] = '$'.abs($paidInsurance);
			$row['data'][] = '$'.abs($paidPatient);
			$row['data'][] = '$'.abs($balance);
			$rows[] = $row;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function listPatientAccountDetailsAction() {
		$visitId = (int)$this->_getParam('visitId');
		$visit = new Visit();
		$visit->visitId = $visitId;
		$visit->populate();
		$summary = $visit->accountSummary;
		$rows = array();
		foreach ($summary['claimFiles']['details'] as $data) {
			$claimFile = $data['claimFile'];
			$visit = $data['visit'];
			$claimFileId = (int)$claimFile->claimFileId;
			$row = array();
			$row['id'] = $visitId;
			$row['data'] = array();
			$row['data'][] = $claimFileId; // Id
			$row['data'][] = InsuranceProgram::getInsuranceProgram($claimFile->payerId); // Payer Name
			$row['data'][] = substr($claimFile->dateBilled,0,10); // Date Billed
			$row['data'][] = substr($claimFile->dateTime,0,10); // Date
			$row['data'][] = '$'.$claimFile->billed; // Billed
			$row['data'][] = '$'.$claimFile->paid; // Paid
			$row['data'][] = '$'.$claimFile->writeOff; // Write Off
			$row['data'][] = '$'.$claimFile->balance; // Balance
			$row['data'][] = ''; // Chk #
			$row['data'][] = $visit->facility; // Facility
			$row['data'][] = $visit->providerDisplayName; // Provider
			$row['data'][] = $claimFile->enteredBy; // Entered By

			$rows[] = $row;
		}

		// misc charges
		foreach ($summary['miscCharges']['details'] as $row) {
			$rows[] = array(
				'id'=>$row->miscChargeId,
				'data'=>array(
					'Misc Charge', // Id
					'', // Payer Name
					substr($row->chargeDate,0,10), // Date Billed
					'', // Date
					'$'.$row->amount, // Billed
					'', // Paid
					'', // Write Off
					'', // Balance
					'', // Chk #
					'', // Facility
					'', // Provider
					'', // Entered By
				),
			);
		}
		// payments
		foreach ($summary['payments']['details'] as $row) {
			$rows[] = array(
				'id'=>$row->paymentId,
				'data'=>array(
					'Payment', // Id
					InsuranceProgram::getInsuranceProgram($row->payerId), // Payer
					'', // Date Billed
					substr($row->paymentDate,0,10), // Date
					'', // Billed
					'$'.$row->amount, // Paid
					'', // Write Off
					'', // Balance
					$row->refNum, // Chk #
					'', // Facility
					'', // Provider
					$row->enteredBy, // Entered By
				),
			);
		}
		// writeoffs
		foreach ($summary['writeOffs']['details'] as $row) {
			$rows[] = array(
				'id'=>$row->writeOffId,
				'data'=>array(
					'Write Off', // Id
					InsuranceProgram::getInsuranceProgram($row->payerId), // Payer
					'', // Date Billed
					substr($row->timestamp,0,10), // Date
					'', // Billed
					'$'.$row->amount, // Paid
					'', // Write Off
					'', // Balance
					'', // Chk #
					'', // Facility
					'', // Provider
					$row->enteredBy, // Entered By
				),
			);
		}

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('rows'=>$rows));
	}

	public function setPatientFiltersAction() {
		$params = $this->_getParam('filters');
		if (is_array($params)) {
			$filters = $this->_session->patientFilters;
			foreach ($params as $key=>$value) {
				$filters[$key] = $value;
			}
			$this->_session->patientFilters = $filters;
		}
		$data = true;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function paymentAction() {
		$personId = (int)$this->_getParam('personId');
		$payment = new Payment();
		$payment->personId = $personId;
		$form = new WebVista_Form(array('name'=>'paymentId'));
		$form->setAction(Zend_Registry::get('baseUrl').'accounts.raw/process-payment');
		$form->loadORM($payment,'Payment');
		$form->setWindow('windowUnallocPayment');
		$this->view->form = $form;

		$guid = 'd1d9039a-a21b-4dfb-b6fa-ec5f41331682';
		$enumeration = new Enumeration();
		$enumeration->populateByGuid($guid);
		$closure = new EnumerationClosure();
		$this->view->paymentTypes = $closure->getAllDescendants($enumeration->enumerationId,1,true)->toArray('key','name');
		$this->render();
	}

	public function processPaymentAction() {
		$params = $this->_getParam('payment');
		$data = false;
		if (is_array($params)) {
			$payment = new Payment();
			$payment->populateWithArray($params);
			$payment->persist();
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

}
