<?php
/*****************************************************************************
*       CalendarController.php
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
 * Calendar controller
 */
class CalendarController extends WebVista_Controller_Action {

	protected $_session;

	public function init() {
		$this->_session = new Zend_Session_Namespace(__CLASS__);
	}

	public function listEventsAction() {
		$colIndex = $this->_getParam('colIndex');
		$filterColumns = $this->_session->filter->columns;
		$columns = array();
		if (strlen($colIndex) > 0) {
			$cols = explode(',',$colIndex);
			foreach ($cols as $col) {
				if (isset($filterColumns[$col])) {
					$columns[$col] = $filterColumns[$col];
				}
			}
		}
		else {
			$columns = $filterColumns;
		}
		$data = array();
		foreach ($columns as $index => $col) {
			$data[$index]['events'] = $this->generateEventColumnData($index);
			$header = $this->_generateColumnHeader($col);
			$data[$index]['header'] = $header['header'];
		}

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}


    /**
     * Default action to dispatch
     */
    public function indexAction() {
        $this->viewDayAction();
    }

    public function ajaxGenerateTimeColumnDataAction() {
        $data = $this->generateTimeColumnData();
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;
        $json->direct($data);
    }

    public function ajaxGenerateEventColumnDataAction() {
	calcTS();
	$columnIndex = $this->_getParam('columnIndex');
       	trigger_error("before generate column: " . calcTS(),E_USER_NOTICE);
        $data = $this->generateEventColumnData($columnIndex);
        trigger_error("after generate column: " .calcTS(),E_USER_NOTICE);
        $json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
        $json->suppressExit = true;
        $json->direct($data);
    }

	public function ajaxStoreAppointmentAction() {
		$appointmentId = $this->_getParam('appointmentId');
		$columnId = $this->_getParam('columnId');
		$filter = $this->getCurrentDisplayFilter();

		$app = new Appointment();
		$app->appointmentId = $appointmentId;
		$app->populate();

		$arr = array();
		$arr['appointmentId'] = $app->appointmentId;
		$arr['lastChangeDate'] = $app->lastChangeDate;
		$arr['patientId'] = $app->patientId;
		$arr['title'] = $app->title;

		$this->_session->storageAppointments[$appointmentId] = $arr;

		$data = array();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function ajaxCheckAppointmentAction() {
		$appointmentId = $this->_getParam('appointmentId');
		$filter = $this->getCurrentDisplayFilter();
		$exists = false;
		foreach ($filter->columns as $index => $col) {
			if (isset($this->_session->currentAppointments[$index][$appointmentId])) {
				$exists = true;
				break;
			}
		}

		$data = array();
		$data['existsInOtherColumn'] = $exists;
		if ($exists) {
			$data['columnId'] = $index;
		}
		$app = new Appointment();
		$app->appointmentId = $appointmentId;
		$app->populate();
		$hasChanged = false;
		if (isset($this->_session->storageAppointments[$appointmentId])) {
			$row = $this->_session->storageAppointments[$appointmentId];
			if ($row['lastChangeDate'] != $app->lastChangeDate) {
				$hasChanged = true;
				$patient = new Patient();
				$patient->setPersonId($row['patientId']);
				$patient->populate();
				$person = $patient->person;
				$data['recentChanges'] = "{$app->start} - {$app->end}\n {$person->last_name}, {$person->first_name} (#{$row['patientId']}) \n {$row['title']}";
			}
		}
		$data['hasChanged'] = $hasChanged;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function getColumnHeadersAction() {
		$rows = array();
		$filter = $this->getCurrentDisplayFilter();
		foreach ($filter->columns as $index=>$column) {
			$rows[$index] = $this->_generateColumnHeader($column);
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($rows);
	}

	protected function _generateColumnHeader(Array $column) {
		$data = array();
		$data['header'] = "{$column['dateFilter']}<br>";
		$title = $column['dateFilter'];
		// temporarily set the header as providerId
		$providerId = $column['providerId'];
		$roomId = 0;
		if (isset($column['roomId'])) {
			$roomId = $column['roomId'];
		}
		if ($providerId > 0) {
			$provider = new Provider();
			$provider->setPersonId($providerId);
			$provider->populate();
			$name = $provider->last_name.', '.$provider->first_name;
			// we simply replace the comma with its html equivalent (&#44;) because this may cause not to render the header
			$data['header'] .= str_replace(',','&#44;',$name);
			$title .= ' -> '.$name;
		}
		if ($roomId > 0) {
			$room = new Room();
			$room->id = $roomId;
			$room->populate();
			if ($providerId > 0) {
				$data['header'] .= '<br>';
			}
			$data['header'] .= $room->name;
			$title .= ' -> '.$room->name;
		}
		$data['header'] = '<label title="'.$title.'">'.$data['header'].'</label>';
		return $data;
	}

	public function ajaxGetColumnHeaderAction() {
		$columnIndex = $this->_getParam('columnIndex');
		$filter = $this->getCurrentDisplayFilter();
		if (!isset($filter->columns[$columnIndex])) {
			throw new Exception(__("Cannot generate column with that index, there is no filter defined for that column Index: ") . $columnIndex);
		}

		$data = $this->_generateColumnHeader($filter->columns[$columnIndex]);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

    public function viewDayAction() {
	$this->view->columns = $this->getCurrentDisplayFilter()->columns;
	$this->render('view-day');
    }

	public function setFilterAction() {
		$id = $this->_getParam('id');
		$filter = $this->getCurrentDisplayFilter();
		if (!isset($filter->columns[$id])) {
			throw new Exception(__("Cannot set filter column with that index, there is no filter defined for that column Index: ") . $id);
		}
		$column = $filter->columns[$id];
		$this->view->columnId = $id;
		$this->view->data = $column;
		$this->render('set-filter');
	}

	public function processSetFilterAction() {
		$calendar = $this->_getParam('calendar');
		$id = $calendar['columnId'];
		$filter = $this->getCurrentDisplayFilter();
		if (!isset($filter->columns[$id])) {
			throw new Exception(__("Cannot set filter column with that index, there is no filter defined for that column Index: ") . $id);
		}

		$filterState =  new FilterState();
		$data = array();
		if ($id > 0) {
			$filterState->filterStateId = $id;
			$filterState->populate();
		}
		if (!isset($calendar['tabName'])) {
			$filterState->tabName = Menu::getCurrentlySelectedActivityGroup();
		}
		$filterState->populateWithArray($calendar);
		$filterState->persist();
		$this->_session->filter->columns[$id] = $filterState->toArray();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function ajaxUpdateAppointmentAction() {
		$idFrom = $this->_getParam('idFrom');

		$columnIdFrom = $this->_getParam('columnIdFrom');
		$columnIdTo = $this->_getParam('columnIdTo');
		$isCopy = $this->_getParam('isCopy');
		$timeTo = $this->_getParam('timeTo');

		$filter = $this->getCurrentDisplayFilter();
		$columns = $filter->columns;
		if (!isset($columns[$columnIdFrom])) {
			throw new Exception(__("Cannot generate SOURCE column with that index, there is no filter defined for that column Index: ") . $columnIdFrom);
		}
		if (!isset($columns[$columnIdTo])) {
			throw new Exception(__("Cannot generate TO column with that index, there is no filter defined for that column Index: ") . $columnIdTo);
		}

		$columnFrom = $columns[$columnIdFrom];
		$columnTo = $columns[$columnIdTo];

		$providerIdFrom = $columnFrom['providerId'];
		$providerIdTo = $columnTo['providerId'];
		$roomIdTo = $columnTo['roomId'];

		$app = new Appointment();
		$app->appointmentId = (int)$idFrom;
		$app->populate();

		$data = array();
		if ($this->_getParam('changeDate') && $this->_getParam('changeDate') == 1) {
			$startDate = $filter->date;
		}
		else {
			$x = explode(' ', $app->start);
			$startDate = $x[0];
		}

		$startTime = strtotime($app->start);
		$endTime = strtotime($app->end);
		$diffTime = $endTime - $startTime;

		$newStartTime = strtotime($startDate . ' ' . $timeTo);
		$newEndTime = $newStartTime + $diffTime;

		$app->start = date('Y-m-d H:i:s', $newStartTime);
		$app->end = date('Y-m-d H:i:s', $newEndTime);
		$data['timeTo'] = $timeTo;

		$app->lastChangeDate = date('Y-m-d H:i:s');
		$app->providerId = $providerIdTo;
		$app->roomId = $roomIdTo;
		if (strtolower($isCopy) == 'true') {
			// zero out appointmentId to act a new copy
			$app->appointmentId = 0;
		}
		$app->persist();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$data['columnIdFrom'] = $columnIdFrom;
		$data['columnIdTo'] = $columnIdTo;
		$json->direct($data);
	}

	public function ajaxGetMenuAction() {
		$menus = array();
		$menus[] = array('text'=>__('Add Column'), 'id'=>'add_column');
		$menus[] = array('text'=>__('Remove This Column'), 'id'=>'remove_column');
		$menus[] = array('text'=>__('Select Date'), 'id'=>'select_date');
		$menus[] = array('text'=>__('Edit This Appointment'), 'id'=>'edit_appointment');
		$menus[] = array('text'=>__('Create Visit'), 'id'=>'create_visit');
		$menus[] = array('text'=>__('Add Payment'), 'id'=>'add_payment');
		$menus[] = array('text'=>__('Cancel Move'), 'id'=>'cancel_move');
		$menus[] = array('text'=>__('Find First'), 'id'=>'find_first');
		$this->view->menus =  $menus;
		$this->view->stations = LegacyEnum::getEnumArray('routing_stations');
		header('Content-Type: application/xml;');
		$this->render('ajax-get-menu');
	}

	public function ajaxRemoveColumnAction() {
		$id = $this->_getParam('id');
		$filter = $this->getCurrentDisplayFilter();
		$columns = $filter->columns;
		if (!isset($columns[$id])) {
			throw new Exception(__("Cannot generate column with that index, there is no filter defined for that column Index: ") . $id);
		}
		$filterState = new FilterState();
		if (isset($this->_session->filter->columns[$id]['filterStateId'])) {
			$filterStateId = $this->_session->filter->columns[$id]['filterStateId'];
			$filterState->deleteByFilterStateId($filterStateId);
		}
		unset($this->_session->filter->columns[$id]);
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$data = array();
		$data['ret'] = true;
		$json->direct($data);
	}

	public function addColumnAction() {
		$this->render('add-column');
	}

	public function processAddColumnAction() {
		$calendar = $this->_getParam('calendar');

		$filter = $this->getCurrentDisplayFilter();
		$providerId = $calendar['providerId'];
		$roomId = $calendar['roomId'];

		/*
		$scheduleEvent = new ScheduleEvent();
		$scheduleEvent->providerId = $providerId;
		$scheduleEvent->roomId = $roomId;
		$scheduleEvent->title = "Event {$providerId}";
		$scheduleEvent->start = $filter->date . ' ' . $filter->end;
		$scheduleEvent->end = $filter->date . ' ' . $filter->start;
		$scheduleEvent->persist();
		*/

		$filterState =  new FilterState();
		if (!isset($calendar['tabName'])) {
			$filterState->tabName = Menu::getCurrentlySelectedActivityGroup();
		}
		$filterState->populateWithArray($calendar);
		$filterState->persist();

		$this->_session->filter->columns[] = $filterState->toArray();
		$data = array();

		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$data['providerId'] = $providerId;
		$data['columnId'] = max(array_keys($this->_session->filter->columns));
		$json->direct($data);
	}

	public function addAppointmentAction() {
		$appointmentId = $this->_getParam('appointmentId');

		$appointment = new Appointment();
		if (strlen($appointmentId) > 0) {
			$filter = $this->getCurrentDisplayFilter();

			$appointment->appointmentId = (int)$appointmentId;
			$appointment->populate();
			$this->view->start = date('H:i', strtotime($appointment->start));
			$this->view->end = date('H:i', strtotime($appointment->end));
			foreach ($filter->columns as $index=>$col) {
				if (($col['providerId'] > 0 && $col['roomId'] > 0 && $col['providerId'] == $appointment->providerId && $col['roomId'] == $appointment->roomId) ||
				    ($col['providerId'] > 0 && $col['providerId'] == $appointment->providerId) ||
				    ($col['roomId'] > 0 && $col['roomId'] == $appointment->roomId)) {
					$this->view->columnId = $index;
					break;
				}
			}

			$recordNumber = $appointment->patient->record_number;
			$lastName = $appointment->patient->last_name;
			$firstName = $appointment->patient->first_name;
			$middleInitial = '';
			if (strlen($appointment->patient->middle_name) > 0) {
				$middleInitial = $appointment->patient->middle_name{0};
			}
			$this->view->patient = "{$lastName}, {$firstName} {$middleInitial} #{$recordNumber} PID:{$appointment->patient->person_id}";
		}
		else {
			$columnId = $this->_getParam('columnId');
			$rowId = $this->_getParam('rowId');
			$start = $this->_getParam('start');
			if (strlen($columnId) > 0) {
				$this->view->columnId = $columnId;
				$filter = $this->getCurrentDisplayFilter();
				if (!isset($filter->columns[$columnId])) {
					throw new Exception(__("Cannot generate column with that index, there is no filter defined for that column Index: ") . $columnId);
				}
				$column = $filter->columns[$columnId];
				$appointment->providerId = (isset($column['providerId'])) ? $column['providerId'] : 0;
				$appointment->roomId = (isset($column['roomId'])) ? $column['roomId'] : 0;
			}
			if (strlen($start) > 0) {
				$this->view->start = $start;
				$this->view->end = date('H:i', strtotime('+1 hour', strtotime($start)));
			}
		}

		$form = new WebVista_Form(array('name' => 'add-appointment'));
		$form->setAction(Zend_Registry::get('baseUrl') . "calendar.raw/process-add-appointment");
		$form->loadORM($appointment, "Appointment");
		$form->setWindow('windowNewAppointment');
		$this->view->form = $form;

		$reasons = array();
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName(PatientNote::ENUM_REASON_PARENT_NAME);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ctr = 0;
		foreach ($enumerationIterator as $enum) {
			// since data type of patient_note.reason is tinyint we simply use the counter as id
			$reasons[$ctr++] = $enum->name;
		}

		/*
		$patientNotes = array();
		$patientNote = new PatientNote();
		$patientNoteIterator = $patientNote->getIterator();
		$filters = array();
		$filters['patient_id'] = (int)$appointment->patientId;
		$filters['active'] = 1;
		$filters['posting'] = 0;
		$patientNoteIterator->setFilters($filters);
		foreach ($patientNoteIterator as $row) {
			$patientNotes[$row->patientNoteId] = $reasons[$row->reason];
		}
		$this->view->patientNotes = $patientNotes;
		*/

		$phones = array();
		$phone = new PhoneNumber();
		$phoneIterator = $phone->getIteratorByPatientId($appointment->patientId);
		foreach ($phoneIterator as $row) {
			$phones[] = $row->number;
		}
		$this->view->phones = $phones;

		$appointmentTemplate = new AppointmentTemplate();
		$appointmentReasons = $appointmentTemplate->getAppointmentReasons();
		$this->view->appointmentReasons = $appointmentReasons;

		$this->view->appointment = $appointment;
		$this->render('add-appointment');
	}

	public function processAddAppointmentAction() {
		$appointment = $this->_getParam('appointment');
		$paramProviders = array();
		foreach ($appointment as $key=>$val) {
			$providerPrefix = 'providerId-';
			if (substr($key,0,strlen($providerPrefix)) == $providerPrefix) {
				$paramProviders[] = $val;
				unset($appointment[$key]);
			}
		}
		if (count($paramProviders) > 0) {
			// assign the first providerId
			$appointment['providerId'] = array_shift($paramProviders);
		}
		// extra providers if any, can be retrieved using $paramProviders variable, not sure where to place it
		$columnId = $this->_getParam('columnId');
		$rowId = $this->_getParam('rowId');
		$forced = (int)$this->_getParam('forced');
		$filter = $this->getCurrentDisplayFilter();

		if (strlen($columnId) <= 0) {
			// look for the column of the input provider
			if (strlen($appointment['providerId']) > 0) {
				foreach ($filter->columns as $index=>$column) {
					if ($column['providerId'] == $appointment['providerId']) {
						$columnId = $index;
						break;
					}
				}
			}
			// in the meantime, no room checked, how are we going to check for the room?
		}
		if (!isset($filter->columns[$columnId])) {
			throw new Exception(__("Cannot generate column with that index, there is no filter defined for that column Index: ") . $columnId);
		}

		$column = $filter->columns[$columnId];

		$data = array();
		$data['columnId'] = $columnId;
		$data['rowId'] = $rowId;

		$app = new Appointment();
		$app->populateWithArray($appointment);
		if ($app->appointmentId > 0) {
			$app->lastChangeId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
			$app->lastChangeDate = date('Y-m-d H:i:s');
		}
		else {
			$app->creatorId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
			$app->createdDate = date('Y-m-d H:i:s');
		}
		//$app->providerId = $appointment['providerId'];

		//$app->patientId = substr($appointment['patient'],stripos($appointment['patient'],'PID:') + 4);
		$app->walkin = isset($appointment['walkin'])?1:0;
		$app->start = $filter->date . ' ' . date('H:i:s', strtotime($appointment['start']));
		$app->end = $filter->date . ' ' . date('H:i:s', strtotime($appointment['end']));

		if (!$forced && $error = $app->checkRules()) { // prompt the user if the appointment being made would be a double book or is outside of schedule time.
			$data['error'] = $error;
		}
		else {
			$app->persist();
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function ajaxSetFilterDayAction() {
		$day = $this->_getParam('day');
		$filter = $this->getCurrentDisplayFilter();
		$time = 0;
		if ($day == 'next') {
			$time = '+1 day';
			$date = strtotime($time,strtotime($filter->date));
			$this->_session->filter->date = date('Y-m-d',$date);
		}
		else if ($day == 'previous') {
			$time = '-1 day';
			$date = strtotime($time,strtotime($filter->date));
			$this->_session->filter->date = date('Y-m-d',$date);
		}
		else {
			$x = explode('-',$day);
			$m = $x[1];
			$d = $x[2];
			$y = $x[0];
			if (count($x) != 3 || !checkdate($m,$d,$y)) {
				$msg = 'Invalid date format!';
				throw new Exception($msg);
			}
			$this->_session->filter->date = date('Y-m-d',strtotime($day));
		}
		$columns = $this->_session->filter->columns;
		foreach ($columns as $index=>$col) {
			if (isset($col['dateFilter'])) {
				if ($time === 0) {
					$tmpDate = $this->_session->filter->date;
				}
				else {
					$tmpDate = date('Y-m-d',strtotime($time,strtotime($col['dateFilter'])));
				}
				$this->_session->filter->columns[$index]['dateFilter'] = $tmpDate;
			}
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$data = array();
		$json->direct($data);
	}

	public function posContextMenuAction() {
		header('Content-Type: application/xml;');
		$this->render('pos-context-menu');
	}

	protected function _generateEligibilityRowData(InsuredRelationship $insuredRelationship) {
		$row = array();
		$row[] = $insuredRelationship->displayDateLastVerified;
		$row[] = $insuredRelationship->displayProgram;
		$row[] = $insuredRelationship->displayExpires;
		$row[] = $insuredRelationship->displayVerified;
		$row[] = $insuredRelationship->desc;
		return $row;
	}

	public function processUpdateEligibilityAction() {
		$id = (int)$this->_getParam('id');
		$data = false;
		if ($id > 0) {
			$insuredRelationship = new InsuredRelationship();
			$insuredRelationship->insuredRelationshipId = $id;
			$insuredRelationship->populate();
		}
		$params = $this->_getParam('pos');
		$insuredRelationship->populateWithArray($params);
		$insuredRelationship->persist();
		$data = array('row'=>$this->_generateEligibilityRowData($insuredRelationship));
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function processCheckEligibilityAction() {
		$id = (int)$this->_getParam('id');
		$data = false;
		if ($id > 0) {
			$insuredRelationship = InsuredRelationship::eligibilityCheck($id);
			$data = array('row'=>$this->_generateEligibilityRowData($insuredRelationship));
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function pointOfSaleAction() {
		$appointmentId = (int)$this->_getParam('appointmentId');
		$listPayments = array();
		$listCharges = array();
		$listEligibility = array();
		if ($appointmentId > 0) {
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();
			$personId = (int)$appointment->patientId;

			$visit = new Visit();
			$visit->appointmentId = $appointmentId;
			$visit->populateByAppointmentId();

			$payment = new Payment();
			$paymentIterator = $payment->getIteratorByVisitId($visit->visitId);
			//$paymentIterator = $payment->getMostRecentPayments();
			foreach ($paymentIterator as $pay) {
				$listPayments[$pay->paymentId] = array(
					date('Y-m-d',strtotime($pay->paymentDate)), // date
					$pay->paymentType, // type
					$pay->amount, // amount
					$pay->title, // note
				);
			}

			$miscCharge = new MiscCharge();
			/*$miscChargeIterator = $miscCharge->getIteratorByVisitId($visitId);
			foreach ($miscChargeIterator as $misc) {
				$listCharges[$misc->miscChargeId] = array(
					date('Y-m-d',strtotime($misc->chargeDate)), // date
					'', // type
					$misc->amount, // amount
					$misc->note, // note
				);
			}*/
			$results = $miscCharge->getUnpaidCharges();
			foreach ($results as $id=>$row) {
				$listCharges[$id] = array(
					$row['date'], // date
					$row['type'], // type
					$row['amount'], // amount
					$row['note'], // note
				);
			}

			$insuredRelationship = new InsuredRelationship();
			$insuredRelationship->personId = $personId;
			$insuredRelationshipIterator = $insuredRelationship->getActiveEligibility();
			foreach ($insuredRelationshipIterator as $item) {
				$listEligibility[$item->insuredRelationshipId] = $this->_generateEligibilityRowData($item);
			}
		}
		$this->view->listPayments = $listPayments;
		$this->view->listCharges = $listCharges;
		$this->view->listEligibility = $listEligibility;
		$this->render('point-of-sale');
	}

    protected function getCurrentDisplayFilter() {
	//unset($this->_session->filter);
	if (isset($this->_session->filter)) {
		return $this->_session->filter;
	}
        $filter = new StdClass();
        $filter->date = date('Y-m-d');
        $filter->increment = 15;
        $filter->start = '07:00';
        $filter->end = '17:00';
	$filter->columns = array();
	// retrieve from database
	$filterStateIterator = new FilterStateIterator();
	$filters = array();
	$filters['tabName'] = Menu::getCurrentlySelectedActivityGroup();
	$filterStateIterator->setFilters($filters);
	foreach ($filterStateIterator as $state) {
        	//$filter->columns[] = array('providerId' => $state->providerId,'roomId'=>$state->roomId);
        	$filter->columns[] = $state->toArray();
	}
	if (!count($filter->columns) > 0) {
       		$filter->columns[] = array('providerId'=>'placeHolderId');
	}
	// save to session
        $this->_session->filter = $filter;
	return $filter;
    }

    protected function generateEventColumnData($columnIndex) {
	$columnIndex = (int) $columnIndex;
	$columnData = array();
        $scheduleEventIterator = new ScheduleEventIterator();
        $appointmentIterator = new AppointmentIterator();

	if (!isset($this->getCurrentDisplayFilter()->columns[$columnIndex])) {
		throw new Exception(__("Cannot generate column with that index, there is no filter defined for that column Index: ") . $columnIndex);
	}

	$this->_session->currentAppointments[$columnIndex] = array();
	$filter = $this->getCurrentDisplayFilter();
	$filterTimeStart = strtotime($filter->start);
	$filterTimeEnd = strtotime($filter->end);

	$paramFilters = $filter->columns[$columnIndex];
	if (isset($paramFilters['dateFilter'])) {
		$filter->date = date('Y-m-d',strtotime($paramFilters['dateFilter']));
	}
	$paramFilters['start'] = $filter->date . ' ' . $filter->start;
	$paramFilters['end'] = $filter->date . ' ' . $filter->end;
	$paramFilters['start'] = $filter->date . ' ' . $filter->start;
	$paramFilters['end'] = $filter->date . ' 23:59:59';
	$paramFilters['showCancelledAppointments'] = $paramFilters['showCancelledAppointments'];
	$scheduleEventIterator->setFilter($paramFilters);

	// we need to get the length of time to create number of rows in the grid
	$timeLen = (($filterTimeEnd - $filterTimeStart) / 60) / $filter->increment;
	for ($i=0;$i<=$timeLen;$i++) {
		$row = array();
		// assign row id as rowNumber and columnIndex
		$row['id'] = $i.$columnIndex;
		$row['data'][0] = '';
		$columnData[$i] = $row;
	}

	$filterToTimeStart = strtotime($paramFilters['start']);
	$appointmentIterator->setFilter($paramFilters);
	// hold the temporary data counter
	$tmpDataCtr = array();
	$colMultiplier = 1;
	$patient = new Patient();
	$room = new Room();
	$zIndex = 0;
	foreach ($appointmentIterator as $row) {
		$startToTime = strtotime($row->start);
		$endToTime = strtotime($row->end);
		$tmpStart = date('H:i', $startToTime);
		$tmpEnd = date('H:i', $endToTime);
		$timeLen = (($endToTime - $startToTime) / 60) / $filter->increment;
		$tmpIndex = (($startToTime - $filterToTimeStart) / 60) / $filter->increment;

		if (!isset($columnData[$tmpIndex])) {
			break;
		}

		$index = $tmpIndex;
		for ($j=1;$j<=$timeLen;$j++) {
			if (!isset($columnData[$index])) {
				break;
			}
			$index++;
		}
		$j--;

		$height = 20 * $j * 1.1;
		$marginLeft = 8;
		$multiplier = 1;

		// generate ranges code inside if ($multiplier === 1) block
		$incTime = $startToTime;
		$ranges = array();
		for ($ctr=1;$ctr<=$timeLen;$ctr++) {
			$ranges[] = date('H:i',$incTime);
			$incTime = strtotime("+{$filter->increment} minutes",$incTime);
		}

		// check for appointment intersection
		foreach ($tmpDataCtr as $keyCtr=>$dataCtr) {
			if (in_array($tmpStart,$dataCtr['ranges'])) {
				// merge the ranges if we need to have a nested multiple bookings
				// uncomment if this is not the case and move the generate ranges to its proper location for code optimization
				$tmpDataCtr[$keyCtr]['ranges'] = array_merge($dataCtr['ranges'],$ranges);
				$tmpDataCtr[$keyCtr]['multiplier']++;
				$multiplier = $tmpDataCtr[$keyCtr]['multiplier'];
				break;
			}
		}
		if ($multiplier === 1) {
			$tmpDataCtr[] = array('ranges'=>$ranges,'multiplier'=>$multiplier);
		}
		else {
			$marginLeft = ($multiplier-1) * 250;
		}
		if ($multiplier > $colMultiplier) {
			$colMultiplier = $multiplier;
		}

		$patient->setPersonId($row->patientId);
		$patient->populate();
		$person = $patient->person;
		$room->setRoomId($row->roomId);
		$room->populate();
		$this->_session->currentAppointments[$columnIndex][$row->appointmentId] = $row;
		$mark = '';
		if (strlen($row->appointmentCode) > 0) {
			//$mark = "({$row->appointmentCode})";
		}
		$zIndex++;
		// where to use room?
		$columnData[$tmpIndex]['id'] = $row->appointmentId . 'i' . $columnData[$tmpIndex]['id'];
		$appointmentId = $row->appointmentId;

		$visitIcon = '';
		$visit = new Visit();
		$visit->appointmentId = $appointmentId;
		$visit->populateByAppointmentId();
		if ($visit->visitId > 0) {
			$visitIcon = '<img src="'.$this->view->baseUrl.'/img/appointment_visit.png" alt="'.__('Visit').'" title="'.__('Visit').'" style="border:0px;height:18px;width:18px;margin-left:5px;" />';
		}

		$routingStatuses = array();
		if (strlen($row->appointmentCode) > 0) {
			$routingStatuses[] = __('Mark').': '.$row->appointmentCode;
		}
		$routing = new Routing();
		$routing->personId = $row->patientId;
		$routing->appointmentId = $row->appointmentId;
		$routing->providerId = $row->providerId;
		$routing->roomId = $row->roomId;
		$routing->populateByAppointments();
		if (strlen($routing->stationId) > 0) {
			$routingStatuses[] = __('Station').': '.$routing->stationId;
		}
		$routingStatus = implode(' ',$routingStatuses);

		//$columnData[$tmpIndex]['data'][0] .= "<div onclick=\"setAppointmentId('$row->appointmentId')\" ondblclick=\"appointmentEdit('$columnIndex','{$columnData[$tmpIndex]['id']}',0,'$row->appointmentId')\" style=\"float:left;position:absolute;margin-top:-11.9px;height:{$height}px;width:230px;overflow:hidden;border:thin solid black;margin-left:{$marginLeft}px;padding-left:2px;background-color:lightgrey;z-index:{$zIndex};\" class=\"dataForeground\" id=\"event{$appointmentId}\" onmouseover=\"expandAppointment({$appointmentId},this);\" onmouseout=\"shrinkAppointment({$appointmentId},this,{$height},{$zIndex});\">{$tmpStart}-{$tmpEnd} <a href=\"javascript:showPatientDetails({$row->patientId});\">{$person->last_name}, {$person->first_name} (#{$row->patientId})</a> {$visitIcon} <br />{$routingStatus}<div class=\"bottomInner\" id=\"bottomInnerId{$appointmentId}\">{$row->title} {$mark}</div></div>";
		$columnData[$tmpIndex]['data'][0] .= "<div onmousedown=\"setAppointmentId('$row->appointmentId')\" style=\"float:left;position:absolute;margin-top:-11.9px;height:{$height}px;width:230px;overflow:hidden;border:thin solid black;margin-left:{$marginLeft}px;padding-left:2px;background-color:lightgrey;z-index:{$zIndex};\" class=\"dataForeground\" id=\"event{$appointmentId}\" onmouseover=\"expandAppointment({$appointmentId},this);\" onmouseout=\"shrinkAppointment({$appointmentId},this,{$height},{$zIndex});\">{$tmpStart}-{$tmpEnd} <a href=\"javascript:showPatientDetails({$row->patientId});\">{$person->last_name}, {$person->first_name} (#{$row->patientId})</a> {$visitIcon} <br />{$routingStatus}<div class=\"bottomInner\" id=\"bottomInnerId{$appointmentId}\">{$row->title} {$mark}</div></div>";
		$columnData[$tmpIndex]['userdata']['visitId'] = $visit->visitId;
		$columnData[$tmpIndex]['userdata']['appointmentId'] = $row->appointmentId;
		$columnData[$tmpIndex]['userdata']['length'] = $j;
		if (!isset($columnData[$tmpIndex]['userdata']['ctr'])) {
			$columnData[$tmpIndex]['userdata']['ctr'] = 0;
		}
		$columnData[$tmpIndex]['userdata']['ctr']++;
	}
	$columnData[0]['userdata']['colMultiplier'] = $colMultiplier;
	$columnData[0]['userdata']['providerId'] = $paramFilters['providerId'];
	$roomId = 0;
	if (isset($paramFilters['roomId'])) {
		$roomId = $paramFilters['roomId'];
	}
	$columnData[0]['userdata']['roomId'] = $roomId;

	foreach($scheduleEventIterator as $event) {
		$x = explode(' ', $event->start);
		$eventTimeStart = strtotime($x[1]);
		$x = explode(' ', $event->end);
		$eventTimeEnd = strtotime($x[1]);
		// get the starting index
		$index = (($eventTimeStart - $filterTimeStart) / 60) / $filter->increment;
		$tmpIndex = $index;
		$color = $event->provider->color;
		if ($event->roomId > 0 && strlen($event->room->color) > 0) {
			$color = $event->room->color;
		}
		if (substr($color,0,1) != '#') {
			$color = '#'.$color;
		}
		while ($eventTimeStart < $eventTimeEnd) {
			$eventDateTimeStart = date('Y-m-d H:i:s',$eventTimeStart);
			$eventTimeStart = strtotime("+{$filter->increment} minutes",$eventTimeStart);
			$columnData[$tmpIndex]['style'] = 'background-color:'.$color.';border-color:lightgrey;';
			$columnData[$index]['userdata']['title'] = $event->title;
			$tmpIndex++;
		}
        }

        $ret = array();
        $ret['rows'] = $columnData;
        return $ret;
    }

    protected function generateTimeColumnData() {
        $filter = $this->getCurrentDisplayFilter();
        $data = array();
        $timeStart = strtotime("{$filter->date} {$filter->start}");
        $timeEnd = strtotime("{$filter->date} {$filter->end}");
        while ($timeStart <= $timeEnd) {
            $tmp = array();
            $tmp['id'] = $timeStart;
            $tmp['data'][] = date('H:i',$timeStart);
            $data[] = $tmp;
            $timeStart = strtotime("+{$filter->increment} minutes",$timeStart);
        }
	$data[0]['userdata']['numberOfRows'] = count($data);
        $ret = array();
        $ret['rows'] = $data;
        return $ret;
    }

}
