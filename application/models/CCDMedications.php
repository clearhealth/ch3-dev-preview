<?php
/*****************************************************************************
*       CCDMedications.php
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


class CCDMedications {

	public static function populate(CCD $base,SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.112');
		$templateId->addAttribute('assigningAuthorityName','HITSP/C83');

		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.3.19');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.8');
		$templateId->addAttribute('assigningAuthorityName','HL7 CCD');
		// <!-- Medications section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','10160-0');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$code->addAttribute('codeSystemName','LOINC');
		$code->addAttribute('displayName','History of medication use');
		$section->addChild('title','Medications');
		$medications = array();
		$rows = array();
		$filters = array('patientId'=>$base->patient->personId);
		$base->setFiltersDateRange($filters);
		$iterator = new MedicationIterator();
		$iterator->setFilter($filters);
		foreach ($iterator as $medication) {
			$medications[] = $medication;
			$datePrescribed = '&#160;';
			if ($medication->datePrescribed != '0000-00-00 00:00:00') {
				$datePrescribed = date('M d, Y',strtotime($medication->datePrescribed));
			}
			$baseMed24 = $medication->baseMed24;
			//$status = ($medication->status != 'Discontinued')?'Active':'Discontinued';
			$rows[] = '<tr>
					<td>'.$baseMed24->rxnorm_cuid.'</td>
					<td>Medication</td>
					<td>'.$medication->description.'</td>
					<td>'.$baseMed24->fdaDrugname.'</td>
					<td>'.$baseMed24->tradename.'</td>
					<td>'.$medication->directions.'</td>
					<td>'.$medication->strength.'</td>
					<td>'.$medication->dose.'</td>
					<td>'.$medication->route.'</td>
					<td>'.$medication->schedule.'</td>
					<td>'.$datePrescribed.'</td>
					<td>'.$medication->displayStatus.'</td>
				</tr>';
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
						<thead>
							<tr>
								<th>RxNorm Code</th>
								<th>Product</th>
								<th>Medication</th>
								<th>Generic Name</th>
								<th>Brand Name</th>
								<th>Instructions</th>
								<th>Strength</th>
								<th>Dose</th>
								<th>Route</th>
								<th>Frequency</th>
								<th>Date Started</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>'.implode("\n",$rows).'</tbody>
					</table>';
		$section->addChild('text',$text);

		foreach ($medications as $medication) {
			$quantity = '';
			if (strlen($medication->strength) > 0 && strlen($medication->unit) > 0) {
				$strength = explode(';',$medication->strength);
				$strength = str_replace(',','',$strength[0]);
				$unit = explode(';',$medication->unit);
				if ($strength) $quantity = '<quantity value="'.$strength.'" unit="'.preg_replace('/ /','',strtolower($unit[0])).'"/>';
			}
			$status = $medication->displayStatus;
			$entry = '<substanceAdministration classCode="SBADM" moodCode="EVN">
				<templateId root="2.16.840.1.113883.3.88.11.83.8" assigningAuthorityName="HITSP C83"/>
				<templateId root="2.16.840.1.113883.10.20.1.24" assigningAuthorityName="CCD"/>
				<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7" assigningAuthorityName="IHE PCC"/>
				<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.1" assigningAuthorityName="IHE PCC"/>
				<!--Medication activity template -->
				<id root="'.NSDR::create_guid().'"/>
				<statusCode code="completed"/>
				<effectiveTime xsi:type="IVL_TS">
					<low nullFlavor="UNK"/>
					<high nullFlavor="UNK"/>
				</effectiveTime>
				<effectiveTime xsi:type="PIVL_TS" institutionSpecified="false" operator="A">
					<period value="24" unit="h"/>
				</effectiveTime>
				<routeCode code="C38288" displayName="Oral" codeSystem="2.16.840.1.113883.3.26.1.1" codeSystemName="FDA RouteOfAdministration">
					<!--IHE/PCC recommends that the routeCode be taken from the HL7 RouteOfAdministration code system. However, HITSP/C32, C83 and C80 recommend that for the U.S. Realm it be taken from the FDA RouteOfAdministration code system.-->
					<translation displayName="Swallow, oral" code="PO" codeSystemName="HL7 RouteOfAdministration" codeSystem="2.16.840.1.113883.5.112"/>
				</routeCode>
				<doseQuantity value="1"/>
				<rateQuantity nullFlavor="NA"/>
				<consumable>
					<manufacturedProduct>
						<templateId root="2.16.840.1.113883.3.88.11.83.8.2" assigningAuthorityName="HITSP C83"/>
						<templateId root="2.16.840.1.113883.10.20.1.53" assigningAuthorityName="CCD"/>
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.2" assigningAuthorityName="IHE PCC"/>
						<!-- Product template -->
						<manufacturedMaterial>
							<code code="309362" codeSystem="2.16.840.1.113883.6.88" displayName="'.$medication->description.'">
								<originalText>'.$medication->description.'<reference/>
								</originalText>
								<translation code="174742" codeSystem="2.16.840.1.113883.6.88" displayName="'.$medication->description.'" codeSystemName="RxNorm"/>
							</code>
							<name>Plavix</name>
						</manufacturedMaterial>
					</manufacturedProduct>
				</consumable>
				<entryRelationship typeCode="REFR">
					<observation classCode="OBS" moodCode="EVN">
						<templateId root="2.16.840.1.113883.10.20.1.47"/>
						<code code="33999-4" displayName="Status" codeSystem="2.16.840.1.113883.6.1" codeSystemName="LOINC"/>
						<value xsi:type="CE" code="55561003" displayName="'.$status.'" codeSystem="2.16.840.1.113883.6.96" codeSystemName="SNOMED CT"/>
					</observation>
				</entryRelationship>
				<entryRelationship typeCode="REFR">
					<supply classCode="SPLY" moodCode="INT">
						<templateId root="2.16.840.1.113883.10.20.1.34" assigningAuthorityName="CCD"/>
						<templateId root="2.16.840.1.113883.3.88.11.83.8.3" assigningAuthorityName="HITSP C83"/>
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.7.3" assigningAuthorityName="IHE PCC"/>
						<id/>
						<statusCode code="completed"/>
						<effectiveTime xsi:type="IVL_TS">
							<low value="20020101"/>
							<high nullFlavor="UNK"/>
						</effectiveTime>
						<repeatNumber value="1"/>'.$quantity.'
					</supply>
				</entryRelationship>
				<entryRelationship typeCode="SUBJ">
					<observation classCode="OBS" moodCode="EVN">
						<templateId root="2.16.840.1.113883.3.88.11.83.8.1" assigningAuthorityName="HITSP C83"/>
						<code code="73639000" codeSystem="2.16.840.1.113883.6.96" displayName="Prescription Drug"/>
					</observation>
				</entryRelationship>
			</substanceAdministration>';
			$entry = $section->addChild('entry',$entry);
			$entry->addAttribute('typeCode','DRIV');
		}
	}

}
