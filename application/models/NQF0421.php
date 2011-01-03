<?php
/*****************************************************************************
*       NQF0421.php
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


class NQF0421 extends NQF {

	protected static $results = array();

	public static function getResults() {
		return self::$results;
	}

	/*
	 * NQF0421 / PQRI128: gov.cms.nqf.0421 (Core - 1)
	 * Title: Adult Weight Screening and Follow-Up
	 * Description: Percentage of patients aged 18 years and older with a calculated BMI in the past six months or during the current visit documented in the medical record AND if the most recent BMI is outside parameters, a follow-up plan is documented.
	 */
	public function populate() {
		// measurement duration: 12 months
		$db = Zend_Registry::get('dbAdapter');
		$dateStart = $this->dateStart;
		$dateEnd = $this->dateEnd;
		$providerId = $this->providerId;

		$pregnancyICD9Codes = array(
			// ICD9: pregnancy
			'630', '631', '632', '633.00', '633.01', '633.10', '633.11',
			'633.20', '633.21', '633.80', '633.81', '633.90', '633.91', '634.00',
			'634.01', '634.02', '634.10', '634.11', '634.12', '634.20', '634.21',
			'634.22', '34.30', '634.31', '634.32', '634.40', '634.41', '634.42',
			'634.50', '634.51', '634.52', '634.60', '634.61', '634.62', '634.70',
			'634.71', '634.72', '634.80', '634.81', '634.82', '634.90', '634.91',
			'634.92', '635.00', '635.01', '635.02', '635.10', '635.11', '635.12',
			'635.20', '635.21', '635.22', '635.30', '635.31', '635.32', '635.40',
			'635.41', '635.42', '635.50', '635.51', '635.52', '635.60', '635.61',
			'635.62', '635.70', '635.71', '635.72', '635.80', '635.81', '635.82',
			'635.90', '635.91', '635.92', '636.00', '636.01', '636.02', '636.10',
			'636.11', '636.12', '636.20', '636.21', '636.22', '636.30', '636.31',
			'636.32', '636.40', '636.41', '636.42', '636.50', '636.51', '636.52',
			'636.60', '636.61', '636.62', '636.70', '636.71', '636.72', '636.80',
			'636.81', '636.82', '636.90', '636.91', '636.92', '637.00', '637.01',
			'637.02', '637.10', '637.11', '637.12', '637.20', '637.21', '637.22',
			'637.30', '637.31', '637.32', '637.40', '637.41', '637.42', '637.50',
			'637.51', '637.52', '637.60', '637.61', '637.62', '637.70', '637.71',
			'637.72', '637.80', '637.81', '637.82', '637.90', '637.91', '637.92',
			'638.0', '638.1', '638.2', '638.3', '638.4', '638.5', '638.6',
			'638.7', '638.8', '638.9', '639.0', '639.1', '639.2', '639.3',
			'639.4', '639.5', '639.6', '639.8', '639.9', '640.00', '640.01',
			'640.03', '640.80', '640.81', '640.83', '640.90', '640.91', '640.93',
			'641.00', '641.01', '641.03', '641.10', '641.11', '641.13', '641.20',
			'641.21', '641.23', '641.30', '641.31', '641.33', '641.80', '641.81',
			'641.83', '641.90', '641.91', '641.93', '642.00', '642.01', '642.02',
			'642.03', '642.04', '642.10', '642.11', '642.12', '642.13', '642.14',
			'642.20', '642.21', '642.22', '642.23', '642.24', '642.30', '642.31',
			'642.32', '642.33', '642.34', '642.40', '642.41', '642.42', '642.43',
			'642.44', '642.50', '642.51', '642.52', '642.53', '642.54', '642.60',
			'642.61', '642.62', '642.63', '642.64', '642.70', '642.71', '642.72',
			'642.73', '642.74', '642.90', '642.91', '642.92', '642.93', '642.94',
			'643.00', '643.01', '643.03', '643.10', '643.11', '643.13', '643.20',
			'643.21', '643.23', '643.80', '643.81', '643.83', '643.90', '643.91',
			'643.93', '644.00', '644.03', '644.10', '644.13', '644.20', '644.21',
			'645.10', '645.11', '642.13', '645.20', '645.21', '645.23', '646.00',
			'646.01', '646.03', '646.10', '646.11', '646.12', '646.13', '646.14',
			'646.20', '646.21', '646.22', '646.23', '646.24', '646.30', '646.31',
			'646.33', '646.40', '646.41', '646.42', '646.43', '646.44', '646.50',
			'646.51', '646.52', '646.53', '646.54', '646.60', '646.61', '646.62',
			'646.63', '646.64', '646.70', '646.71', '646.73', '646.80', '646.81',
			'646.82', '646.83', '646.84', '646.90', '646.91', '646.93', '647.00',
			'647.01', '647.02', '647.03', '647.04', '647.10', '647.11', '647.12',
			'647.13', '647.14', '647.20', '647.21', '647.22', '647.23', '647.24',
			'647.30', '647.31', '647.32', '647.33', '647.34', '647.40', '647.41',
			'647.42', '647.43', '647.44', '647.50', '647.51', '647.52', '647.53',
			'647.54', '647.60', '647.61', '647.62', '647.63', '647.64', '647.80',
			'647.81', '647.82', '647.83', '647.84', '647.90', '647.91', '647.92',
			'647.93', '647.94', '648.00', '648.01', '648.02', '648.03', '648.04',
			'648.10', '648.11', '648.12', '648.13', '648.14', '648.20', '648.21',
			'648.22', '648.23', '648.24', '648.30', '648.31', '648.32', '648.33',
			'648.34', '648.40', '648.41', '648.42', '648.43', '648.44', '648.50',
			'648.51', '648.52', '648.53', '648.54', '648.60', '648.61', '648.62',
			'648.63', '648.64', '648.70', '648.71', '648.72', '648.73', '648.74',
			'648.80', '648.81', '648.82', '648.83', '648.84', '648.90', '648.91',
			'648.92', '648.93', '648.94', '649.00', '649.01', '649.02', '649.03',
			'649.04', '649.10', '649.11', '649.12', '649.13', '649.14', '649.20',
			'649.21', '649.22', '649.23', '649.24', '649.30', '649.31', '649.32',
			'649.33', '649.34', '649.40', '649.41', '649.42', '649.43', '649.44',
			'649.50', '649.51', '649.53', '649.60', '649.61', '649.62', '649.63',
			'649.64', '649.70', '649.71', '649.73', '650', '651.00', '651.01',
			'651.03', '651.10', '651.11', '651.13', '651.20', '651.21', '651.23',
			'651.30', '651.31', '651.33', '651.40', '651.41', '651.43', '651.50',
			'651.51', '651.53', '651.60', '651.61', '651.63', '651.70', '651.71',
			'651.73', '651.80', '651.81', '651.83', '651.90', '651.91', '651.93',
			'652.00', '652.01', '652.03', '652.10', '652.11', '652.13', '652.20',
			'652.21', '652.23', '652.30', '652.31', '652.33', '652.40', '652.41',
			'652.43', '652.50', '652.51', '652.53', '652.60', '652.61', '652.63',
			'652.70', '652.71', '652.73', '652.80', '652.81', '652.83', '652.90',
			'652.91', '652.93', '653.00', '653.01', '653.03', '653.10', '653.11',
			'653.13', '653.20', '653.21', '653.23', '653.30', '653.31', '653.33',
			'653.40', '653.41', '653.43', '653.50', '653.51', '653.53', '653.60',
			'653.61', '653.63', '653.70', '653.71', '653.73', '653.80', '653.81',
			'653.83', '653.90', '653.91', '653.93', '654.00', '654.01', '654.02',
			'654.03', '654.04', '654.10', '654.11', '654.12', '654.13', '654.14',
			'654.20', '654.21', '654.23', '654.30', '654.31', '654.32', '654.33',
			'654.34', '654.40', '654.41', '654.42', '654.43', '654.44', '654.50',
			'654.51', '654.52', '654.53', '654.54', '654.60', '654.61', '654.62',
			'654.63', '654.64', '654.70', '654.71', '654.72', '654.73', '654.74',
			'654.80', '654.81', '654.82', '654.83', '654.84', '654.90', '654.91',
			'654.92', '654.93', '654.94', '655.00', '655.01', '655.03', '655.10',
			'655.11', '655.13', '655.20', '655.21', '655.23', '655.30', '655.31',
			'655.33', '655.40', '655.41', '655.43', '655.50', '655.51', '655.53',
			'655.60', '655.61', '655.63', '655.70', '655.71', '655.73', '655.80',
			'655.81', '655.83', '655.90', '655.91', '655.93', '656.00', '656.01',
			'656.03', '656.10', '656.11', '656.13', '656.20', '656.21', '656.23',
			'656.30', '656.31', '656.33', '656.40', '656.41', '656.43', '656.50',
			'656.51', '656.53', '656.60', '656.61', '656.63', '656.70', '656.71',
			'656.73', '656.80', '656.81', '656.83', '656.90', '656.91', '656.93',
			'657.00', '657.01', '657.03', '658.00', '658.01', '658.03', '658.10',
			'658.11', '658.13', '658.20', '658.21', '658.23', '658.30', '658.31',
			'658.33', '658.40', '658.41', '658.43', '658.80', '658.81', '658.83',
			'658.90', '658.91', '658.93', '659.00', '659.01', '659.03', '659.10',
			'659.11', '659.13', '659.20', '659.21', '659.23', '659.30', '659.31',
			'659.33', '659.40', '659.41', '659.43', '659.50', '659.51', '659.53',
			'659.60', '659.61', '659.63', '659.70', '659.71', '659.73', '659.80',
			'659.81', '659.83', '659.90', '659.91', '659.93', '660.00', '660.01',
			'660.03', '660.10', '660.11', '660.13', '660.20', '660.21', '660.23',
			'660.30', '660.31', '660.33', '660.40', '660.41', '660.43', '660.50',
			'660.51', '660.53', '660.60', '660.61', '660.63', '660.70', '660.71',
			'660.73', '660.80', '660.81', '660.83', '660.90', '660.91', '660.93',
			'661.00', '661.01', '661.03', '661.10', '661.11', '661.13', '661.20',
			'661.21', '661.23', '661.30', '661.31', '661.33', '661.40', '661.41',
			'661.43', '661.90', '661.91', '661.93', '662.00', '662.01', '662.03',
			'662.10', '662.11', '662.13', '662.20', '662.21', '662.23', '662.30',
			'662.31', '662.33', '663.00', '663.01', '663.03', '663.10', '663.11',
			'663.13', '663.20', '663.21', '663.23', '663.30', '663.31', '663.33',
			'663.40', '663.41', '663.43', '663.50', '663.51', '663.53', '663.60',
			'663.61', '663.63', '663.80', '663.81', '663.83', '663.90', '663.91',
			'663.93', '664.00', '664.01', '664.04', '664.10', '664.11', '664.14',
			'664.20', '664.21', '664.24', '664.30', '664.31', '664.34', '664.40',
			'664.41', '664.44', '664.50', '664.51', '664.54', '664.60', '664.61',
			'664.64', '664.80', '664.81', '664.84', '664.90', '664.91', '664.94',
			'665.00', '665.01', '665.03', '665.10', '665.11', '665.20', '665.22',
			'665.24', '665.30', '665.31', '665.34', '665.40', '665.41', '665.44',
			'665.50', '665.51', '665.54', '665.60', '665.61', '665.64', '665.70',
			'665.71', '665.72', '665.74', '665.80', '665.81', '665.82', '665.83',
			'665.84', '665.90', '665.91', '665.92', '665.93', '665.94', '666.00',
			'666.02', '666.04', '666.10', '666.12', '666.14', '666.20', '666.22',
			'666.24', '666.30', '666.32', '666.34', '667.00', '667.02', '667.04',
			'667.10', '667.12', '667.14', '668.00', '668.01', '668.02', '668.03',
			'668.04', '668.10', '668.11', '668.12', '668.13', '668.14', '668.20',
			'668.21', '668.22', '668.23', '668.24', '668.80', '668.81', '668.82',
			'668.83', '668.84', '668.90', '668.91', '668.92', '668.93', '668.94',
			'669.00', '669.01', '669.02', '669.03', '669.04', '669.10', '669.11',
			'669.12', '669.13', '669.14', '669.20', '669.21', '669.22', '669.23',
			'669.24', '669.30', '669.32', '669.34', '669.40', '669.41', '669.42',
			'669.43', '669.44', '669.50', '669.51', '669.60', '669.61', '669.70',
			'669.71', '669.80', '669.81', '669.82', '669.83', '669.84', '669.90',
			'669.91', '669.92', '669.93', '669.94', '670.00', '670.02', '670.04',
			'670.10', '670.12', '670.14', '670.20', '670.22', '670.24', '670.30',
			'670.32', '670.34', '670.80', '670.82', '670.84', '671.00', '671.01',
			'671.02', '671.03', '671.04', '671.10', '671.11', '671.12', '671.13',
			'671.14', '671.20', '671.21', '671.22', '671.23', '671.24', '671.30',
			'671.31', '671.33', '671.40', '671.42', '671.44', '671.50', '671.51',
			'671.52', '671.53', '671.54', '671.80', '671.81', '671.82', '671.83',
			'671.84', '671.90', '671.91', '671.92', '671.93', '671.94', '672.00',
			'672.02', '672.04', '673.00', '673.01', '673.02', '673.03', '673.04',
			'673.10', '673.11', '673.12', '673.13', '673.14', '673.20', '673.21',
			'673.22', '673.23', '673.24', '673.30', '673.31', '673.32', '673.33',
			'673.34', '673.80', '673.81', '673.82', '673.83', '673.84', '674.00',
			'674.01', '674.02', '674.03', '674.04', '674.10', '674.12', '674.14',
			'674.20', '674.22', '674.24', '674.30', '674.32', '674.34', '674.40',
			'674.42', '674.44', '674.50', '674.51', '674.52', '674.53', '674.54',
			'674.80', '674.82', '674.84', '674.90', '674.92', '674.94', '675.00',
			'675.01', '675.02', '675.03', '675.04', '675.10', '675.11', '675.12',
			'675.13', '675.14', '675.20', '675.21', '675.22', '675.23', '675.24',
			'675.80', '675.81', '675.82', '675.83', '675.84', '675.90', '675.91',
			'675.92', '675.93', '675.94', '676.00', '676.01', '676.02', '676.03',
			'676.04', '676.10', '676.11', '676.12', '676.13', '676.14', '676.20',
			'676.21', '676.22', '676.23', '676.24', '676.30', '676.31', '676.32',
			'676.33', '676.34', '676.40', '676.41', '676.42', '676.43', '676.44',
			'676.50', '676.51', '676.52', '676.53', '676.54', '676.60', '676.61',
			'676.62', '676.63', '676.64', '676.80', '676.81', '676.82', '676.83',
			'676.84', '676.90', '676.91', '676.92', '676.93', '676.94', 'V22.0',
			'V22.1', 'V22.2', 'V23.0', 'V23.1', 'V23.2', 'V23.3', 'V23.41',
			'V23.49', 'V23.5', 'V23.7', 'V23.81', 'V23.82', 'V23.83', 'V23.84',
			'V23.85', 'V23.86', 'V23.89', 'V23.9', 'V28.0', 'V28.1', 'V28.2',
			'V28.3', 'V28.4', 'V28.5', 'V28.6', 'V28.81', 'V28.82', 'V28.89',
			'V28.9', 

			// SNOMED-CT: pregnancy
			'16356006', '198624007', '198626009', '198627000', '239101008',
			'289908002', '31601007', '34801009', '38720006', '41991004',
			'43990006', '44782008', '60000008', '60810003', '64254006',
			'65147003', '69532007', '79290002', '79586000', '80997009',
			'82661006', '87605005', '90968009', '9899009',

		);
		$terminalIllnessCodes = array(
			// SNOMED-CT: terminal illness
			'162607003', '162608008', '300936002',
		);

		$criteria = array(
			'>= 65'=>array('22','29'),
			'BETWEEN 18 AND 64'=>array('18.5','24'),
		);
		$ctr = 0;
		$ret = array();
		foreach ($criteria as $key=>$value) {
			// INITIAL PATIENT POPULATION
			$initialPopulation = "((DATE_FORMAT('{$dateEnd}','%Y') - DATE_FORMAT(person.date_of_birth,'%Y') - (DATE_FORMAT('{$dateEnd}','00-%m-%d') < DATE_FORMAT(person.date_of_birth,'00-%m-%d'))) {$key})";
			$initialPopulation .= "AND (encounter.date_of_treatment BETWEEN '{$dateStart}' AND '{$dateEnd}') AND encounter.treating_person_id = {$providerId}";

			// DENOMINATOR
			$sql = "SELECT patient.person_id AS patientId,
					patient.record_number AS MRN
				FROM patient
				INNER JOIN person ON person.person_id = patient.person_id
				INNER JOIN encounter ON encounter.patient_id = person.person_id
				WHERE {$initialPopulation}";
			// use loops rather than count because patientId can be used to get patient info
			//file_put_contents('/tmp/nqf.sql',$sql,FILE_APPEND);
			$denominator = array();
			$dbStmt = $db->query($sql);
			while ($row = $dbStmt->fetch()) {
				$denominator[$row['patientId']] = $row;
			}

			// NUMERATOR
			$lookupTables = array(
				array(
					'join'=>'INNER JOIN clinicalNotes ON clinicalNotes.personId = person.person_id
						INNER JOIN genericData ON genericData.objectId = clinicalNotes.clinicalNoteId',
					'where'=>"genericData.name = 'gov.cms.nqf.0421.careGoals.bmiFollowup'",
				),
				array(
					'join'=>'INNER JOIN orders ON orders.patientId = person.person_id',
					'where'=>"orders.textOnlyType = 'DIETARY'",
				),
			);
			$numerator = array();
			foreach ($lookupTables as $lookup) {
				$sql = "SELECT vitalSignGroups.personId AS patientId,
					patient.record_number AS MRN
				FROM patient
				INNER JOIN person ON person.person_id = patient.person_id
				INNER JOIN encounter ON encounter.patient_id = person.person_id
				INNER JOIN vitalSignGroups ON vitalSignGroups.personId = person.person_id
				INNER JOIN vitalSignValues ON vitalSignValues.vitalSignGroupId = vitalSignGroups.vitalSignGroupId
				{$lookup['join']}
				WHERE {$initialPopulation} AND
					(((PERIOD_DIFF(DATE_FORMAT(encounter.date_of_treatment,'%Y%m'),DATE_FORMAT(vitalSignGroups.dateTime,'%Y%m')) - (DAY(encounter.date_of_treatment) < DAY(vitalSignGroups.dateTime))) <= 6) AND
					vitalSignValues.vital = 'BMI' AND 
					(
						(vitalSignValues.value BETWEEN {$value[0]} AND {$value[1]}) OR
						(
							(
								(vitalSignValues.value > {$value[1]} OR vitalSignValues.value < {$value[0]}) AND
								(PERIOD_DIFF(DATE_FORMAT(encounter.date_of_treatment,'%Y%m'),DATE_FORMAT(vitalSignGroups.dateTime,'%Y%m')) - (DAY(encounter.date_of_treatment) < DAY(vitalSignGroups.dateTime)) <= 6)
							) AND
							({$lookup['where']})
						)
					))
				GROUP BY vitalSignGroups.personId";
				//file_put_contents('/tmp/nqf.sql',$sql,FILE_APPEND);
				$dbStmt = $db->query($sql);
				while ($row = $dbStmt->fetch()) {
					$numerator[$row['patientId']] = $row;
				}
			}

			// EXCLUSIONS
			$pregnancyCodes = $this->_formatCodeList($pregnancyICD9Codes);
			$termIllCodes = $this->_formatCodeList($terminalIllnessCodes);

			$lookupTables = array(
				array(
					'join'=>'INNER JOIN clinicalNotes ON clinicalNotes.personId = person.person_id
						INNER JOIN genericData ON genericData.objectId = clinicalNotes.clinicalNoteId',
					'where'=>array(
							"(genericData.name = 'codeLookupICD9' AND (".implode(' OR ',$termIllCodes['generic']).")) AND
							(PERIOD_DIFF(DATE_FORMAT(encounter.date_of_treatment,'%Y%m'),DATE_FORMAT(genericData.dateTime,'%Y%m')) - (DAY(encounter.date_of_treatment) < DAY(genericData.dateTime)) <= 6)",
							"(genericData.name = 'codeLookupICD9' AND (".implode(' OR ',$pregnancyCodes['generic'])."))"
					),
				),
				array(
					'join'=>'INNER JOIN problemLists ON problemLists.personId = patient.person_id',
					'where'=>array(
						"problemLists.code IN (".implode(',',$termIllCodes['code']).") AND
							(PERIOD_DIFF(DATE_FORMAT(encounter.date_of_treatment,'%Y%m'),DATE_FORMAT(problemLists.dateOfOnset,'%Y%m')) - (DAY(encounter.date_of_treatment) < DAY(problemLists.dateOfOnset)) <= 6)",
						"problemLists.code IN (".implode(',',$pregnancyCodes['code']).")",
					),
				),
				array(
					'join'=>'INNER JOIN patientDiagnosis ON patientDiagnosis.patientId = person.person_id',
					'where'=>array(
						"patientDiagnosis.code IN (".implode(',',$termIllCodes['code']).") AND
							(PERIOD_DIFF(DATE_FORMAT(encounter.date_of_treatment,'%Y%m'),DATE_FORMAT(patientDiagnosis.dateTime,'%Y%m')) - (DAY(encounter.date_of_treatment) < DAY(patientDiagnosis.dateTime)) <= 6)",
						"patientDiagnosis.code IN (".implode(',',$pregnancyCodes['code']).")",
					),
				),
			);
			$exclusions = array();
			foreach ($lookupTables as $lookup) {
				$sql = "SELECT patient.person_id AS patientId,
					patient.record_number AS MRN
				FROM patient
				INNER JOIN person ON person.person_id = patient.person_id
				INNER JOIN encounter ON encounter.patient_id = person.person_id
				{$lookup['join']}
				LEFT JOIN patientExams ON patientExams.patientId = person.person_id
				WHERE {$initialPopulation} AND
					(( /* “Patientcharacteristic:Terminalillness”<=6monthsbeforeorsimultaneouslyto “Encounter: encounter outpatient” */
						{$lookup['where'][0]}
					) OR
					( /* “Diagnosisactive:Pregnancy” */
						{$lookup['where'][1]}
					) OR
					( /* “Physical exam not done: patient reason” OR “Physical exam not done: medical reason” OR “Physical rationale physical exam not done: system reason” */
						patientExams.code = 'EXAMPHYS' AND patientExams.result IN ('REFUSED','MU','NA')
					))";
				//file_put_contents('/tmp/nqf.sql',$sql,FILE_APPEND);
				$dbStmt = $db->query($sql);
				while ($row = $dbStmt->fetch()) {
					$exclusions[$row['patientId']] = $row;
				}
			}

			$nctr = count($numerator);
			$dctr = count($denominator);
			$xctr = count($exclusions);
			$percentage = self::calculatePerformanceMeasure($dctr,$nctr,$xctr);
			$ret[] = 'Population criteria '.++$ctr.' = '.'D: '.$dctr.'; N: '.$nctr.'; E: '.$xctr.'; P: '.$percentage;
			self::$results[] = array('denominator'=>$dctr,'numerator'=>$nctr,'exclusions'=>$xctr,'percentage'=>$percentage);
		}
		return implode('<br/>',$ret);
	}

}
