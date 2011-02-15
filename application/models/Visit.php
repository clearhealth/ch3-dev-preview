<?php
/*****************************************************************************
*       Visit.php
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


class Visit extends WebVista_Model_ORM {
	protected $encounter_id;
	protected $encounter_reason;
	protected $patient_id;
	protected $building_id;
	protected $date_of_treatment;
	protected $treating_person_id;
	protected $timestamp;
	protected $last_change_user_id;
	protected $status;
	protected $occurence_id;
	protected $created_by_user_id;
	protected $payer_group_id;
	protected $current_payer;
	protected $room_id;
	protected $practice_id;
	protected $activePayerId;
	protected $closed;
	protected $void;
	protected $appointmentId;
	protected $_providerDisplayName = ''; //placeholder for use in visit list iterator
	protected $_locationName = ''; //placeholder for use in visit list iterator
	protected $_claimRule = array(); // placeholder for claim rule's warning or block message and event type

	protected $_legacyORMNaming = true;
	protected $_table = "encounter";
	protected $_primaryKeys = array("encounter_id");

	public function persist() {
		$ret = parent::persist();
		if ($this->closed) { // recalculate claim lines
			$ret = self::recalculateClaims($this);
		}
		return $ret;
	}

	public static function recalculateClaims(self $visit) {
		$fees = $visit->calculateFees(true);
		$hasProcedure = false;
		$claimId = WebVista_Model_ORM::nextSequenceId('claimSequences');
		$copay = $visit->getCopay();

		$totalPaid = 0;
		$personId = (int)$visit->patientId;
		$userId = (int)Zend_Auth::getInstance()->getIdentity()->personId;
		$visitId = (int)$visit->visitId;
		$discountPayerId = InsuranceProgram::lookupSystemId('Discounts'); // ID of System->Discounts
		$creditPayerId = InsuranceProgram::lookupSystemId('Credit'); // ID of System->Credit
		$payerId = $visit->activePayerId;
		foreach ($fees['details'] as $id=>$values) {
			// update claim or create if not exists
			$fee = (float)$values['fee'];
			$feeDiscounted = (float)$values['feeDiscounted'];
			$claimLine = new ClaimLine();
			$claimLine->populateWithPatientProcedure($values['orm'],$visit);
			$claimLine->claimId = $claimId;
			$claimLine->baseFee = $fee;
			$claimLine->adjustedFee = $feeDiscounted;
			$claimLine->persist();

			$claimLineId = (int)$claimLine->claimLineId;

			$billable = $fee;
			if ($feeDiscounted > 0) {
				// add writeoffs
				$writeOff = new WriteOff();
				$writeOff->personId = $personId;
				$writeOff->claimLineId = $claimLineId;
				$writeOff->visitId = $visitId;
				$writeOff->appointmentId = $visit->appointmentId;
				$writeOff->amount = $feeDiscounted;
				$writeOff->userId = $userId;
				$writeOff->timestamp = date('Y-m-d H:i:s');
				$writeOff->title = 'discount';
				$writeOff->payerId = $discountPayerId;
				$writeOff->persist();
				$billable -= $feeDiscounted;
			}
			if ($billable > 0) {
				foreach ($copay['details'] as $paymentId=>$payment) {
					$amount = (float)$payment->unallocated;
					if (!$amount > 0) {
						unset($copay['details'][$paymentId]);
						continue;
					}
					if ($amount > $billable) $amount = $billable;
					$payment->allocated += $amount;
					$payment->persist();
					$copay['details'][$paymentId] = $payment;
					$totalPaid += $amount;

					$postingJournal = new PostingJournal();
					$postingJournal->patientId = $personId;
					$postingJournal->payerId = $payerId;
					$postingJournal->claimLineId = $claimLineId;
					$postingJournal->visitId = $visitId;
					$postingJournal->amount = $amount;
					$postingJournal->note = 'copay posting';
					$postingJournal->userId = $userId;
					$dateTime = date('Y-m-d H:i:s');
					$postingJournal->datePosted = $dateTime;
					$postingJournal->dateTime = $dateTime;
					$postingJournal->persist();
					$billable -= $amount;
					if ($billable <= 0) break;
				}
			}

			$hasProcedure = true;
		}
		if ($copay['total'] > $totalPaid) { // if copay is greater than all claimlines reamining dollars are posted to credit program
			foreach ($copay['details'] as $paymentId=>$payment) {
				$amount = (float)$payment->unallocated;
				$payment->allocated += $amount;
				$payment->persist();

				$postingJournal = new PostingJournal();
				$postingJournal->patientId = $personId;
				$postingJournal->payerId = $creditPayerId;
				$postingJournal->visitId = $visitId;
				$postingJournal->amount = $amount;
				$postingJournal->note = 'remaining copay balance';
				$postingJournal->userId = $userId;
				$dateTime = date('Y-m-d H:i:s');
				$postingJournal->datePosted = $dateTime;
				$postingJournal->dateTime = $dateTime;
				$postingJournal->persist();
			}
		}
		if (!$hasProcedure) {
			$visitId = $visit->visitId;
			$payment = new Payment();
			foreach ($payment->getIteratorByVisitId($visitId) as $row) {
				// If visit has copay then at closing copay should be turned into unallocated payment (not associated with visit).
				$row->visitId = 0;
				$row->persist();
			}
		}
		else {
			$visit = ClaimRule::checkRules($visit,$fees);
		}
		return $visit;
	}

	function getIterator($objSelect = null) {
		return new VisitIterator($objSelect);
	}
	function setLocationName($locationName) {
		$this->_locationName = $locationName;
	}
	function getLocationName() {
		if (!strlen($this->_locationName) > 0 && $this->buildingId > 0) {
			$building = new Building();
			$building->buildingId = $this->buildingId;
			$building->populate();
			$this->_locationName = $building->name;
		}
		return $this->_locationName;
	}
	function setProviderDisplayName($providerDisplayName) {
		$this->_providerDisplayName = $providerDisplayName;
	}
	function getProviderDisplayName() {
		$provider = new Provider();
		$provider->person_id = $this->treating_person_id;
		$provider->populate();
		return $provider->person->getDisplayName();
	}

	public function getVisitId() {
		return $this->encounter_id;
	}

	public function setVisitId($id) {
		$this->encounter_id = $id;
	}

	public function getInsuranceProgram() {
		return InsuranceProgram::getInsuranceProgram($this->activePayerId);
	}

	public function ormEditMethod($ormId,$isAdd) {
		return $this->ormVisitTypeEditMethod($ormId,$isAdd);
	}

	public function ormVisitTypeEditMethod($ormId,$isAdd) {
		$controller = Zend_Controller_Front::getInstance();
		$request = $controller->getRequest();
		$enumerationId = (int)$request->getParam('enumerationId');

		$view = Zend_Layout::getMvcInstance()->getView();
		$params = array();
		if ($isAdd) {
			$params['parentId'] = $enumerationId;
			unset($_GET['enumerationId']); // remove enumerationId from params list
			$params['grid'] = 'enumItemsGrid';
		}
		else {
			$params['enumerationId'] = $enumerationId;
			$params['ormId'] = $ormId;
		}
		return $view->action('edit-type','visit-details',null,$params);
	}

	public static function ormClasses() {
		return array(
			'Visit' => 'Visit Type',
			'ProcedureCodesCPT' => 'Procedure',
			'DiagnosisCodesICD' => 'Diagnosis',
		);
	}

	public function populateByAppointmentId($appointmentId = null) {
		if ($appointmentId === null) {
			$appointmentId = $this->appointmentId;
		}
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('appointmentId = ?',(int)$appointmentId);
		$ret = $this->populateWithSql($sqlSelect->__toString());
		$this->postPopulate();
		return $ret;
	}

	public function getProviderId() {
		return $this->treating_person_id;
	}

	public function setProviderId($id) {
		$this->treating_person_id = (int)$id;
	}

	public function getDisplayStatus() {
		if ($this->closed) {
			return 'closed';
		}
		else if ($this->void) {
			return 'void';
		}
		else {
			return 'open';
		}
	}

	public function populateLatestVisit($personId=null) {
		if ($personId === null) $personId = $this->patient_id;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('patient_id = ?',(int)$personId)
				->order('date_of_treatment DESC')
				->limit(1);
		return $this->populateWithSql($sqlSelect->__toString());
	}

	public function calculateFees($recompute=null) { // pass true or false to override visit.closed checking
		if ($recompute === null) $recompute = ($this->closed)?false:true;
		$total = 0;
		$discounted = 0;
		$visitFlat = 0;
		$visitPercentage = 0;
		$codeFlat = 0;
		$codePercentage = 0;

		$discountApplied = array();
		if ($recompute) {
			$insuranceProgramId = (int)$this->activePayerId;
			$dateOfVisit = date('Y-m-d',strtotime($this->date_of_treatment));
			$statistics = PatientStatisticsDefinition::getPatientStatistics((int)$this->patient_id);
			$familySize = isset($statistics['family_size'])?$statistics['family_size']:0;
			$monthlyIncome = isset($statistics['monthly_income'])?$statistics['monthly_income']:0;

			$retDiscount = DiscountTable::checkDiscount($insuranceProgramId,$dateOfVisit,$familySize,$monthlyIncome);
			if ($retDiscount !== false) {
				$discount = (float)$retDiscount['discount'];
				switch ($retDiscount['discountType']) {
					case DiscountTable::DISCOUNT_TYPE_FLAT_VISIT:
						$discountApplied[] = 'Flat Visit: $'.$discount;
						$visitFlat += $discount;
						break;
					case DiscountTable::DISCOUNT_TYPE_FLAT_CODE:
						$discountApplied[] = 'Flat Code: $'.$discount;
						$codeFlat += $discount;
						break;
					case DiscountTable::DISCOUNT_TYPE_PERC_VISIT:
						$discountApplied[] = 'Percentage Visit: '.$discount.'%';
						$visitPercentage += ($discount / 100);
						break;
					case DiscountTable::DISCOUNT_TYPE_PERC_CODE:
						$discountApplied[] = 'Percentage Code: '.$discount.'%';
						$codePercentage += ($discount / 100);
						break;
				}
			}
		}

		$details = array();
		$iterator = new PatientProcedureIterator();
		$iterator->setFilters(array('visitId'=>(int)$this->encounter_id));
		$firstProcedureId = null;
		foreach ($iterator as $patientProcedure) {
			$patientProcedureId = (int)$patientProcedure->patientProcedureId;
			if ($recompute) {
				$code = $patientProcedure->code;
				$fee = '-.--';
				$feeDiscounted = '-.--';
				$discountedRate = '';
				$retFee = FeeSchedule::checkFee($insuranceProgramId,$dateOfVisit,$code);
				if ($retFee !== false && (float)$retFee['fee'] != 0) {
					$fee = (float)$retFee['fee'];
					$tmpFee = 0;
					for ($i = 1; $i <= 4; $i++) {
						$modifier = 'modifier'.$i;
						if (!strlen($patientProcedure->$modifier) > 0) continue;
						switch ($patientProcedure->$modifier) {
							case $retFee['modifier1']:
								$tmpFee += (float)$retFee['modifier1fee'];
								break;
							case $retFee['modifier2']:
								$tmpFee += (float)$retFee['modifier2fee'];
								break;
							case $retFee['modifier3']:
								$tmpFee += (float)$retFee['modifier3fee'];
								break;
							case $retFee['modifier4']:
								$tmpFee += (float)$retFee['modifier4fee'];
								break;
						}
					}
					if ($tmpFee > 0) $fee = $tmpFee;
					// calculate discounts
					if ($codeFlat > 0) {
						$feeDiscounted += $codeFlat;
					}
					if ($codePercentage > 0) {
						$feeDiscounted += ($feeDiscounted * $codePercentage);
					}
				}
				if ($firstProcedureId === null) $firstProcedureId = $patientProcedureId;
			}
			else {
				$fee = $patientProcedure->baseFee;
				$feeDiscounted = $patientProcedure->adjustedFee;
			}
			$quantity = (int)$patientProcedure->quantity;
			if ($quantity > 0) {
				$fee *= $quantity;
				$feeDiscounted *= $quantity;
			}
			$total += $fee;
			$discounted += (float)$feeDiscounted;
			$details[$patientProcedureId] = array();
			$details[$patientProcedureId]['orm'] = $patientProcedure;
			$details[$patientProcedureId]['fee'] = $fee;
			$details[$patientProcedureId]['feeDiscounted'] = $feeDiscounted;
		}
		if ($visitFlat > 0) {
			$discounted += $visitFlat;
			// update the first procedure
			if ($firstProcedureId !== null) $details[$firstProcedureId]['feeDiscounted'] += $visitFlat;
		}
		if ($visitPercentage > 0) {
			$discounted += ($discounted * $visitPercentage);
			// update the first procedure
			if ($firstProcedureId !== null) $details[$firstProcedureId]['feeDiscounted'] += ($details[$firstProcedureId]['feeDiscounted'] * $visitFlat);
		}
		$row['discountApplied'] = $discountApplied;
		$row['details'] = $details;
		$row['total'] = $total;
		$row['discounted'] = $discounted;
		return $row;
	}

	public function syncClaimsInsurance() {
		$visitId = (int)$this->encounter_id;
		$payerId = (int)$this->activePayerId;
		$db = Zend_Registry::get('dbAdapter');
		$claim = new ClaimLine();
		$sql = 'UPDATE `'.$claim->_table.'` SET `insuranceProgramId` = '.$payerId.' WHERE `visitId` = '.$visitId;
		return $db->query($sql);
	}

	public function getIteratorByPersonId($personId=null) {
		if ($personId === null) $personId = $this->patient_id;
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from($this->_table)
				->where('patient_id = ?',(int)$personId)
				->order('date_of_treatment DESC');
		return $this->getIterator($sqlSelect);
	}

	public function getAccountSummary() {
		$visitId = (int)$this->encounter_id;
		$ret = array(
			'claimFiles'=>array(
				'details'=>array(),
				'totals'=>array(),
			),
			'miscCharges'=>array(
				'details'=>array(),
				'totals'=>array(),
			),
			'payments'=>array(
				'details'=>array(),
				'totals'=>array(),
			),
			'writeOffs'=>array(
				'details'=>array(),
				'totals'=>array(),
			),
		);
		$totalBilled = 0;
		$totalPaid = 0;
		$totalWO = 0;
		$totalBalance = 0;
		foreach (ClaimFile::listClaims(array('visitId'=>$visitId)) as $data) {
			$claimFile = $data['claimFile'];
			$totalBilled += (float)$claimFile->billed;
			$totalPaid += (float)$claimFile->paid;
			$totalWO += (float)$claimFile->writeOff;
			$totalBalance += (float)$claimFile->balance;
			$ret['claimFiles']['details'][] = $data;
		}
		$ret['claimFiles']['totals'] = array(
			'billed'=>$totalBilled,
			'paid'=>$totalPaid,
			'writeOff'=>$totalWO,
			'balance'=>$totalBalance,
		);

		// misc charges
		$miscCharge = new MiscCharge();
		$totalBilled = 0;
		foreach ($miscCharge->getIteratorByVisitId($visitId) as $row) {
			$totalBilled += (float)$row->amount;
			$ret['miscCharges']['details'][] = $row;
		}
		$ret['miscCharges']['totals'] = array(
			'billed'=>$totalBilled,
			'paid'=>0,
			'writeOff'=>0,
			'balance'=>0,
		);
		// payments
		$payment = new Payment();
		$totalPaid = 0;
		foreach ($payment->getIteratorByVisitId($visitId) as $row) {
			$totalPaid += (float)$row->amount;
			$ret['payments']['details'][] = $row;
		}
		$ret['payments']['totals'] = array(
			'billed'=>0,
			'paid'=>$totalPaid,
			'writeOff'=>0,
			'balance'=>0,
		);
		// writeoffs
		$writeOff = new WriteOff();
		$totalWO = 0;
		foreach ($writeOff->getIteratorByVisitId($visitId) as $row) {
			$totalWO += (float)$row->amount;
			$ret['writeOffs']['details'][] = $row;
		}
		$ret['writeOffs']['totals'] = array(
			'billed'=>0,
			'paid'=>0,
			'writeOff'=>$totalWO,
			'balance'=>0,
		);
		return $ret;
	}

	public function getFacility() {
		$ret = '';
		$roomId = (int)$this->roomId;
		if ($roomId > 0) {
			$room = new Room();
			$room->roomId = $roomId;
			$room->populate();
			$facility = $room->building->name.'->'.$room->name;
		}
		return $ret;
	}

	public function getCopay() {
		// payments with appointmentId
		$db = Zend_Registry::get('dbAdapter');
		$sqlSelect = $db->select()
				->from('payment')
				->where('personId = ?',(int)$this->patientId)
				->where('encounter_id = ?',(int)$this->encounter_id)
				->where('(amount - allocated) > 0')
				->where('appointmentId != 0');
		$total = 0;
		$details = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$payment = new Payment();
				$payment->populateWithArray($row);
				$total += $payment->unallocated;
				$details[$row['payment_id']] = $payment;
			}
		}
		return array(
			'total'=>$total,
			'details'=>$details,
		);
	}

	public function getUnallocatedFunds() {
		return Payment::listUnallocatedFunds($this->patientId);
	}

	public function getUniqueClaimIds() {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new ClaimLine();
		$sqlSelect = $db->select()
				->from($orm->_table,array('claimId'))
				->where('visitId = ?',$this->encounter_id)
				->group('claimId');
		$ret = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				$ret[] = $row['claimId'];
			}
		}
		return $ret;
	}

	public function getUniqueClaims() {
		$db = Zend_Registry::get('dbAdapter');
		$orm = new ClaimLine();
		$sqlSelect = $db->select()
				->from($orm->_table,array('claimId','insuranceProgramId AS payerId'))
				->where('visitId = ?',$this->encounter_id)
				->group('claimId');
		$ret = array();
		if ($rows = $db->fetchAll($sqlSelect)) {
			foreach ($rows as $row) {
				foreach ($row as $key=>$value) $ret[$key][] = $value;
			}
		}
		return $ret;
	}

}
