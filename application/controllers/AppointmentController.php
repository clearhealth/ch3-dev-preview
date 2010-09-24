<?php
/*****************************************************************************
*       AppointmentController.php
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


class AppointmentController extends WebVista_Controller_Action {

	public function ajaxMarkAppointmentAction() {
		$appointmentId =(int) $this->_getParam('appointmentId');
		$mark = $this->_getParam('mark');
		$app = new Appointment();
		$app->appointmentId = $appointmentId;
		$app->populate();
		//todo: compare provided mark against eligible in matching enumeration
		$app->appointmentCode = $mark;
		$app->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(true);	
	}

	public function processCreateVisitAction() {
		$appointmentId = (int)$this->_getParam('appointmentId');
		$data = false;
		$ret = $this->_createVisit($appointmentId);
		if ($ret > 0) {
			$data = true;
		}
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	protected function _createVisit($appointmentId) {
		$ret = 0;
		if ($appointmentId > 0) {
			$appointment = new Appointment();
			$appointment->appointmentId = $appointmentId;
			$appointment->populate();

			$visit = new Visit();
			$visit->patientId = $appointment->patientId;
			$visit->roomId = $appointment->roomId;
			$visit->practiceId = $appointment->practiceId;
			$room = new Room();
			$room->roomId = $visit->roomId;
			$room->populate();
			$visit->buildingId = $room->buildingId;
			$visit->createdByUserId = $appointment->creatorId;
			$visit->lastChangeUserId = $appointment->lastChangeId;
			$visit->treatingPersonId = $appointment->providerId;
			$visit->encounterReason = $appointment->reason;
			$visit->dateOfTreatment = $appointment->createdDate;
			$visit->timestamp = date('Y-m-d H:i:s');

			$visit->appointmentId = $appointment->appointmentId;
			$visit->persist();
			$ret = $visit->visitId;
		}
		return $ret;
	}

	public function addPaymentAction() {
		$columnId = -1;
		$appointmentId = (int)$this->_getParam('appointmentId');
		$visitId = (int)$this->_getParam('visitId');
		$payment = new Payment();
		$payment->visitId = $visitId;
		if (!$visitId > 0) {
			$payment->visitId = $this->_createVisit($appointmentId);
			$columnId = (int)$this->_getParam('columnId');
		}

		$form = new WebVista_Form(array('name'=>'paymentId'));
		$form->setAction(Zend_Registry::get('baseUrl').'appointment.raw/process-add-payment');
		$form->loadORM($payment,'Payment');
		$form->setWindow('winPaymentId');
		$this->view->form = $form;
		$this->view->visitId = $visitId;

		$guid = 'd1d9039a-a21b-4dfb-b6fa-ec5f41331682';
		$enumeration = new Enumeration();
		$enumeration->populateByGuid($guid);
		$closure = new EnumerationClosure();
		$this->view->paymentTypes = $closure->getAllDescendants($enumeration->enumerationId,1,true)->toArray('key','name');

		$this->view->columnId = $columnId;
		$this->render('add-payment');
	}

	public function processAddPaymentAction() {
		$params = $this->_getParam('payment');
		$payment = new Payment();
		$payment->populateWithArray($params);
		$payment->paymentDate = date('Y-m-d H:i:s');
		$payment->timestamp = date('Y-m-d H:i:s');
		$payment->userId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$payment->persist();
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(true);
	}

}
