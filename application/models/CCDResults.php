<?php
/*****************************************************************************
*       CCDResults.php
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


class CCDResults {

	public static function populate(CCD $base,SimpleXMLElement $xml) {
		$component = $xml->addChild('component');
		$section = $component->addChild('section');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','2.16.840.1.113883.3.88.11.83.122');
		$templateId->addAttribute('assigningAuthorityName','HITSP/C83');
		$templateId = $section->addChild('templateId');
		$templateId->addAttribute('root','1.3.6.1.4.1.19376.1.5.3.1.3.28');
		$templateId->addAttribute('assigningAuthorityName','IHE PCC');

		// <!--Diagnostic Results section template-->
		$code = $section->addChild('code');
		$code->addAttribute('code','30954-2');
		$code->addAttribute('codeSystem','2.16.840.1.113883.6.1');
		$code->addAttribute('codeSystemName','LOINC');
		$code->addAttribute('displayName','Results');
		$section->addChild('title','Diagnostic Results');

		$rows = array();
		foreach ($base->labResults as $orderId=>$value) {
			foreach ($value['results'] as $lab) {
				$tr = '<tr>
						<td>'.$lab->identifier.'</td>
						<td>'.$lab->description.'</td>
						<td>'.$lab->value.' '.$lab->units.'</td>
						<td>'.$lab->abnormalFlag.'</td>
						<td>'.date('M d, Y',strtotime($lab->observationTime)).'</td>
					</tr>';
				$rows[] = $tr;
			}
		}
		$text = '';
		if ($rows) $text = '<table border="1" width="100%">
				<thead>
					<tr>
						<th>LOINC Code</th>
						<th>Test</th>
						<th>Result</th>
						<th>Abnormal Flag</th>
						<th>Date Performed</th>
					</tr>
				</thead>
				<tbody>'.implode("\n",$rows).'</tbody>
			</table>';
		$section->addChild('text',$text);

		foreach ($base->labResults as $orderId=>$value) {
			$orderLabTest = $value['orderLabTest'];
			$labTest = $value['labTest'];
			$results = $value['results'];
			$entry = '<organizer classCode="BATTERY" moodCode="EVN">
				<templateId root="2.16.840.1.113883.10.20.1.32"/>
				<!--Result organizer template -->
				<id root="'.NSDR::create_guid().'"/>
				<code code="43789009" codeSystem="2.16.840.1.113883.6.96" displayName="'.$labTest->service.'"/>
				<statusCode code="completed"/>
				<effectiveTime value="'.date('YmdHi',strtotime($orderLabTest->dateCollection)).'"/>
				<component>
					<procedure classCode="PROC" moodCode="EVN">
						<templateId root="2.16.840.1.113883.3.88.11.83.17" assigningAuthorityName="HITSP C83"/>
						<templateId root="2.16.840.1.113883.10.20.1.29" assigningAuthorityName="CCD"/>
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.19" assigningAuthorityName="IHE PCC"/>
						<id/>
						<code code="43789009" codeSystem="2.16.840.1.113883.6.96" displayName="'.$labTest->service.'">
							<originalText>'.$orderLabTest->order->orderText.'<reference value="Ptr to text  in parent Section"/>
							</originalText>
						</code>
						<text>'.$orderLabTest->order->orderText.'<reference value="Ptr to text  in parent Section"/>
						</text>
						<statusCode code="completed"/>
						<effectiveTime value="'.date('YmdHi',strtotime($orderLabTest->dateCollection)).'"/>
						<performer>
							<assignedEntity>
								<id extension="PseudoMD-'.$orderLabTest->order->providerId.'" root="2.16.840.1.113883.3.72.5.2"/>
								<addr>See documentationOf in Header</addr>
								<telecom/>
							</assignedEntity>
						</performer>
					</procedure>
				</component>';
			foreach ($results as $result) {
				$referenceRange = '';
				if (strlen($result->referenceRange) > 0) {
					$referenceRange = '
						<referenceRange>
							<observationRange>
								<text>'.$result->referenceRange.'</text>
							</observationRange>
						</referenceRange>';
				}
				if (is_numeric($result->value)) {
					$resultValue = '<value xsi:type="PQ" value="'.$result->value.'" unit="'.$result->units.'"/>';
				}
				else {
					$resultValue = '<value xsi:type="ST">'.$result->value.'</value>';
				}
				$entry .= '
				<component>
					<observation classCode="OBS" moodCode="EVN">
						<templateId root="2.16.840.1.113883.3.88.11.83.15" assigningAuthorityName="HITSP C83"/>
						<templateId root="2.16.840.1.113883.10.20.1.31" assigningAuthorityName="CCD"/>
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.13" assigningAuthorityName="IHE PCC"/>
						<templateId root="2.16.840.1.113883.3.88.11.83.15.1"/>
						<!-- Result observation template -->
						<id root="'.NSDR::create_guid().'"/>
						<code code="'.$result->identifier.'" codeSystem="2.16.840.1.113883.6.1" displayName="'.$result->description.'"/>
						<text>
							<reference value="PtrToValueInsectionText"/>
						</text>
						<statusCode code="completed"/>
						<effectiveTime value="'.date('YmdHi',strtotime($result->observationTime)).'"/>
						'.$resultValue.'
						<interpretationCode code="N" codeSystem="2.16.840.1.113883.5.83"/>
						'.$referenceRange.'
					</observation>
				</component>';
			}
			$entry .= '
				<component>
					<act classCode="ACT" moodCode="EVN">
						<templateId root="1.3.6.1.4.1.19376.1.5.3.1.4.4" assigningAuthorityName="IHE PCC"/>
						<id/>
						<code nullFlavor="NA"/>
						<text>
							<reference value="PointerToTextinSection"/>
						</text>
						<reference typeCode="REFR">
							<externalDocument classCode="DOC" moodCode="EVN">
								<id root="REGISTRYOID" extension="SOMEID" assigningAuthorityName="NIST Registry"/>
								<text>http://nist.etc</text>
							</externalDocument>
						</reference>
					</act>
				</component>
			</organizer>';
			$entry = $section->addChild('entry',$entry);
			$entry->addAttribute('typeCode','DRIV');
		}
	}

}
