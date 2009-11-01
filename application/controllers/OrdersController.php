<?php
/*****************************************************************************
*       OrdersController.php
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
 * Orders controller
 */
class OrdersController extends WebVista_Controller_Action {

	public function indexAction() {
		$this->render('index');
	}

	public function listCommonOrdersAction() {
		$this->view->commonOrders = $this->_getCommonOrders();
		$this->render();
	}

	public function toolbarXmlAction() {
		$id = $this->_getParam('id');
		if (strlen($id) <= 0) {
			throw new Exception('Id is empty');
		}
		$this->view->toolbars = $this->_getToolbars($id);
		header("Content-type: text/xml");
		$this->render();
	}

	public function filterJsonAction() {
		$rows = array();
		$filters = $this->_getFilters();
		foreach ($filters as $id=>$data) {
			$tmp = array();
			$tmp['id'] = $id;
			$tmp['data'][] = $data;
			$rows[] = $tmp;
		}

		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function writeOrdersJsonAction() {
		$rows = array();
		$writeOrders = $this->_getWriteOrders();
		foreach ($writeOrders as $id=>$data) {
			$tmp = array();
			$tmp['id'] = $id;
			$tmp['data'][] = $data;
			$rows[] = $tmp;
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	public function textOrdersAction() {
		$form = new WebVista_Form(array('name' => 'textOrders'));
		$form->setAction(Zend_Registry::get('baseUrl') . "orders.raw/order-process");
		$order = new Order();
		$form->loadORM($order, "Order");
		$form->setWindow('windowTextOnlyOrders');
		$this->view->form = $form;
		$this->render();
	}

	public function orderProcessAction() {
		$textOrders = $this->_getParam('order');
		$status = $this->_getParam('status');
		$order = new Order();
		$order->populateWithArray($textOrders);
		$writeOrders = $this->_getWriteOrders();
		if (strlen($status) > 0 && array_key_exists($status,$writeOrders)) {
			$order->service = $writeOrders[$status];
		}
		$order->status = 'Active';
		$order->persist();
		$msg = __("Record Saved");
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct(array('msg'=>$msg));
	}

	public function listOrdersJsonAction() {
		$filter = $this->_getParam('filter');
		$rows = array();
		$orderIterator = new OrderIterator();
		$orderIterator->setFilter($filter);
		foreach ($orderIterator as $order) {
			$tmp = array();
			$tmp['id'] = $order->orderId;
			$tmp['data'][] = $order->service;
			$tmp['data'][] = $order->status;
			$tmp['data'][] = $order->orderText;
			$start = __('(Not Specified)');
			if ($order->dateStart != '0000-00-00 00:00:00') {
				$start = date('m/d/Y',strtotime($order->dateStart));
			}
			$stop = __('(Not Specified)');
			if ($order->dateStop != '0000-00-00 00:00:00') {
				$stop = date('m/d/Y',strtotime($order->dateStop));
			}
			$tmp['data'][] = __('Start') . ': ' . $start . ' ' . __('Stop') . ': ' . $stop;
			$rows[] = $tmp;
		}
		$data = array();
		$data['rows'] = $rows;
		$json = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$json->suppressExit = true;
		$json->direct($data);
	}

	protected function _getFilters() {
		$filters = array();
		$filters['active_orders'] = __('Active Orders (includes Pending & Recent Activity) - ALLSERVICES');
		$filters['current_orders'] = __('Current Orders (Active & Pending status only)');
		$filters['expiring_orders'] = __('Expiring Orders');
		$filters['unsigned_orders'] = __('Unsigned Orders');
		$filters['recently_expired_orders'] = __('Recently Expired Orders');
		return $filters;
	}

	protected function _getWriteOrders() {
		$writeOrders = array();
		$writeOrders['common_orders'] = __('Common Orders');
		$writeOrders['cardiology'] = __('Cardiology');
		$writeOrders['gastroenterology'] = __('Gastroenterology');
		$writeOrders['hematology'] = __('Hematology/Oncology');
		$writeOrders['infectious_disease'] = __('Infectious Disease');
		$writeOrders['nephrology'] = __('Nephrology');
		$writeOrders['ob_gyn'] = __('Ob/Gyn/Womens Health');
		$writeOrders['orthopedics'] = __('Orthopedics');
		$writeOrders['pediatrics'] = __('Pediatrics');
		$writeOrders['pulmonary'] = __('Pulmonary');
		$writeOrders['psychiatry'] = __('Psychiatry');
		$writeOrders['general_surgery'] = __('General Surgery');
		$writeOrders['nursing_orders'] = __('Nursing Orders');
		$writeOrders['consults_procedures'] = __('Consults/Procedures');
		$writeOrders['diet_orders'] = __('Diet Orders');
		$writeOrders['lab_test_menu'] = __('Laboratory Test Menu');
		$writeOrders['specialty_orders'] = __('Orders By Specialty');
		$writeOrders['meds_inpatient'] = __('Meds, Inpatient');
		$writeOrders['iv_fluids'] = __('IV Fluids');
		$writeOrders['imaging'] = __('Imaging');
		$writeOrders['admission_orders'] = __('Admission Orders');
		$writeOrders['text_orders'] = __('Text Only Orders');
		return $writeOrders;
	}

	protected function _getCommonOrders() {
		$orders = array();
		$orders[] = array('id'=>'patient_status','text'=>'Patient Status');
		$orders[] = array('id'=>'patient_movement','text'=>'Patient Movement');
		$orders[] = array('id'=>'activity_orders','text'=>'Activity Orders');
		$orders[] = array('id'=>'vitals','text'=>'Vitals');
		return $orders;
	}

	protected function _getToolbars($id) {
		$toolbars = array();
		$toolbars['patient_status'][] = array('id'=>'patient_condition','text'=>'Patient Condition');
		$toolbars['patient_status'][] = array('id'=>'patient_diagnosis','text'=>'Patient Diagnosis');
		$toolbars['patient_status'][] = array('id'=>'dnr_status','text'=>'DNR Status');

		$toolbars['patient_movement'][] = array('id'=>'admit_patient','text'=>'Admit Patient');
		$toolbars['patient_movement'][] = array('id'=>'discharge_patient','text'=>'Discharge Patient');
		$toolbars['patient_movement'][] = array('id'=>'transfer_patient','text'=>'Transfer Patient');

		$toolbars['activity_orders'][] = array('id'=>'ad_lib','text'=>'Ad Lib');
		$toolbars['activity_orders'][] = array('id'=>'out_of_bed','text'=>'Out of Bed');
		$toolbars['activity_orders'][] = array('id'=>'may_leave_ward','text'=>'May Leave Ward');

		$toolbars['vitals'][] = array('id'=>'tpr','text'=>'TPR');
		$toolbars['vitals'][] = array('id'=>'bp','text'=>'BP');
		$toolbars['vitals'][] = array('id'=>'temp','text'=>'Temp');
		return $toolbars[$id];
	}
}
