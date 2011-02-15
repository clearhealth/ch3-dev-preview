<?php
/*****************************************************************************
*       CCDProblems.php
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


class CCDProblems {

	public static function populate(CCD $base,SimpleXMLELement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.103');
		$templateId->addAttribute('assigningAuthorityName','HITSP/C83');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.3.6');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.11');
		$templateId->addAttribute('assigningAuthorityName','HL7 CCD');

		// <!-- Problem section template -->
		$code = $section->addChild('code');
		$code->addAttribute('code','11450-4');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$code->addAttribute('codeSystemName','LOINC');
		$code->addAttribute('displayName','Problem list');
		$section->addChild('title','Problems');

		$icd9Rows = array();
		$snomedRows = array();
		foreach ($base->problemLists as $problem) {
			$code = htmlentities($problem->code);
			$tr = '<tr>
					<td>'.$code.'</td>
					<td>'.htmlentities($problem->codeTextShort).'</td>
					<td>'.date('M d, Y',strtotime($problem->dateOfOnset)).'</td>
					<td>'.htmlentities($problem->status).'</td>
				</tr>';
			if (strpos($code,'.') !== false) {
				$icd9Rows[] = $tr;
			}
			else {
				$snomedRows[] = $tr;
			}
		}
		$text = '';
		if (isset($icd9Rows[0])) $text .= '<table border="1" width="100%">
						<thead>
							<tr>
								<th>ICD-9 Code</th>
								<th>Problem</th>
								<th>Date Diagnosed</th>
								<th>Problem Status</th>
							</tr>
						</thead>
						<tbody>'.implode("\n",$icd9Rows).'</tbody>
					</table>';
		if (isset($snomedRows[0])) $text .= '<table border="1" width="100%">
						<thead>
							<tr>
								<th>SNOMED Code</th>
								<th>Problem</th>
								<th>Date Diagnosed</th>
								<th>Problem Status</th>
							</tr>
						</thead>
						<tbody>'.implode("\n",$snomedRows).'</tbody>
					</table>';
		$section->addChild('text',$text);

		foreach ($base->problemLists as $problem) {
			$entry = '<act classCode="ACT" moodCode="EVN">
				<templateId root="2.16.840.1.113883.3.88.11.83.7" assigningAuthorityName="HITSP C83"/>
				<templateId root="2.16.840.1.113883.10.20.1.27" assigningAuthorityName="CCD"/>
				<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.1" assigningAuthorityName="IHE PCC"/>
				<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5.2" assigningAuthorityName="IHE PCC"/>
				<!-- Problem act template -->
				<id root="'.NSDR::create_guid().'"/>
				<code nullFlavor="NA"/>
				<statusCode code="active"/>
				<effectiveTime>
					<low nullFlavor="UNK"/>
				</effectiveTime>
				<performer typeCode="PRF">
					<time>
						<low nullFlavor="UNK"/>
					</time>
					<assignedEntity>
						<id extension="PseudoMD-'.$problem->providerId.'" root="2.16.840.1.113883.3.72.5.2"/>
						<addr/>
						<telecom/>
					</assignedEntity>
				</performer>
				<entryRelationship typeCode="SUBJ" inversionInd="false">
					<observation classCode="OBS" moodCode="EVN">
						<templateId root="2.16.840.1.113883.10.20.1.28" assigningAuthorityName="CCD"/>
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.5" assigningAuthorityName="IHE PCC"/>
						<!--Problem observation template -->
						<id root="'.NSDR::create_guid().'"/>
						<code displayName="Condition" code="64572001" codeSystemName="SNOMED-CT" codeSystem="2.16.840.1.113883.6.96"/>
						<text>
							<reference value="#CondID-'.$problem->providerId.'"/>
						</text>
						<statusCode code="completed"/>
						<effectiveTime>
							<low nullFlavor="UNK"/>
							<high nullFlavor="UNK"/>
						</effectiveTime>
						<value xsi:type="CD" displayName="'.htmlentities($problem->codeTextShort).'" code="233604007" codeSystemName="SNOMED" codeSystem="2.16.840.1.113883.6.96"/>
						<entryRelationship typeCode="REFR">
							<observation classCode="OBS" moodCode="EVN">
								<templateId root="2.16.840.1.113883.10.20.1.50"/>
								<!-- Problem status observation template -->
								<code code="33999-4" codeSystem="2.16.840.1.113883.6.1" displayName="Status"/>
								<statusCode code="completed"/>
								<value xsi:type="CE" code="413322009" codeSystem="2.16.840.1.113883.6.96" displayName="'.htmlentities($problem->status).'"/>
							</observation>
						</entryRelationship>
					</observation>
				</entryRelationship>
			</act>';
			$entry = $section->addChild('entry',$entry);
			$entry->addAttribute('typeCode','DRIV');
		}

		/*$entry = $section->addChild('entry');
		$entry->addAttribute('typeCode','DRIV');
		$act = $entry->addChild('act');
		$act->addAttribute('classCode','ACT');
		$act->addAttribute('moodCode','EVN');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.27');
		$id = $act->addChild('id');
		$id->addAttribute('root',NSDR::create_guid());
		$code = $act->addChild('code');
		$code->addAttribute('nullFlavor','NA');

		$entry = $section->addChild('entry');
		$entry->addAttribute('typeCode','DRIV');
		$act = $entry->addChild('act');
		$act->addAttribute('classCode','ACT');
		$act->addAttribute('moodCode','EVN');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.7');
		$templateId->addAttribute('assigningAuthorityName','HITSP C83');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.27');
		$templateId->addAttribute('assigningAuthorityName','CCD');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.4.5.1');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');
		$templateId = $act->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.4.5.2');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');
		// <!-- Problem act template -->
		$id = $act->addChild('id');
		$id->addAttribute('root','ec8a6ff8-ed4b-4f7e-82c3-e98e58b45de7');
		$code = $act->addChild('code');
		$code->addAttribute('nullFlavor','NA');
		$statusCode = $act->addChild('statusCode');
		$statusCode->addAttribute('code','active');
		$effectiveTime = $act->addChild('effectiveTime');
		$low = $effectiveTime->addChild('effectiveTime');
		$low->addAttribute('nullFlavor','UNK');
		$performer = $act->addChild('performer');
		$performer->addAttribute('typeCode','PRF');
		$time = $performer->addChild('time');
		$low = $time->addChild('low');
		$low->addAttribute('value','2006');
		$assignedEntity = $performer->addChild('assignedEntity');
		$id = $assignedEntity->addChild('id');
		$id->addAttribute('extension','PseudoMD');
		$id->addAttribute('root','2.16.840.1.113883.3.72.5.2');
		$addr = $assignedEntity->addChild('addr');
		$telecom = $assignedEntity->addChild('telecom');
		$entryRelationship = $act->addChild('entryRelationship');
		$entryRelationship->addAttribute('typeCode','SUBJ');
		$entryRelationship->addAttribute('inversionInd','false');
		$observation = $entryRelationship->addChild('observation');
		$observation->addAttribute('classCode','OBS');
		$observation->addAttribute('moodCode','EVN');
		$templateId = $observation->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.28');
		$templateId->addAttribute('assigningAuthorityName','CCD');
		$templateId = $observation->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.4.5');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');
		// <!--Problem observation template -->
		$id = $observation->addChild('id');
		$id->addAttribute('root','ab1791b0-5c71-11db-b0de-0800200c9a66');
		$code = $observation->addChild('code');
		$code->addAttribute('displayName','Condition');
		$code->addAttribute('code','64572001');
		$code->addAttribute('codeSystemName','SNOMED-CT');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.96');
		$text = $observation->addChild('text');
		$reference = $text->addChild('reference');
		$reference->addAttribute('value','#CondID-2');
		$statusCode = $observation->addChild('statusCode');
		$statusCode->addAttribute('code','completed');
		$effectiveTime = $observation->addChild('effectiveTime');
		$low = $effectiveTime->addChild('low');
		$low->addAttribute('value','199701');
		$high = $effectiveTime->addChild('high');
		$high->addAttribute('nullFlavor','UNK');
		$value = $observation->addChild('value');
		$value->addAttribute('xsi:type','CD');
		$value->addAttribute('displayName','Pneumonia');
		$value->addAttribute('code','233604007');
		$value->addAttribute('codeSystemName','SNOMED');
		$value->addAttribute('codeSystem','2.16.840.1.113883.6.96');
		$entryRelationship = $observation->addChild('entryRelationship');
		$entryRelationship->addAttribute('typeCode','REFR');
		$observation = $entryRelationship->addChild('observation');
		$observation->addAttribute('classCode','OBS');
		$observation->addAttribute('moodCode','EVN');
		$templateId = $observation->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.10.20.1.50');
		// <!-- Problem status observation template -->
		$code = $observation->addChild('code');
		$code->addAttribute('code','33999-4');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1"');
		$code->addAttribute('displayName','Status');
		$statusCode = $observation->addChild('statusCode');
		$statusCode->addAttribute('code','completed');
		$value = $observation->addChild('value');
		$value->addAttribute('xsi:type','CE');
		$value->addAttribute('code','413322009');
		$value->addAttribute('codeSystem','2.16.840.1.113883.6.96');
		$value->addAttribute('displayName','Resolved');*/
	}

}
