<?php
/*****************************************************************************
*       EnumGenerator.php
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


class EnumGenerator {

	public static function generateTestData($force = false) {
		self::generateContactPreferencesEnum($force);
		self::generateImmunizationPreferencesEnum($force);
		self::generateTeamPreferencesEnum($force);
		self::generateHSAPreferencesEnum($force);
		self::generateReasonPreferencesEnum($force);
		self::generateProcedurePreferencesEnum($force);
		self::generateEducationPreferencesEnum($force);
		self::generateExamResultPreferencesEnum($force);
		self::generateExamOtherPreferencesEnum($force);
		self::generateMedicationPreferencesEnum($force);
		self::generateColorPreferencesEnum($force);
		self::generateFacilitiesEnum($force);
		self::generateMenuEnum($force);
		self::generateDemographicsPreferencesEnum($force);
		self::generateGeographyPreferencesEnum($force);
		self::generateCalendarPreferencesEnum($force);
		self::generateClinicalPreferencesEnum($force);
		self::generatePaymentTypesEnum($force);
		self::generateCodingPreferencesEnum($force);
		self::generateFacilityCodesEnum($force);
		self::generateIdentifierTypesEnum($force);
	}

	public static function generateDemographicsPreferencesEnum($force = true) {
		$ret = false;
		do {
			$name = 'Demographics';
			$key = 'DEMOGRAPH';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'height' => array('key' => 'HT', 'name' => 'Height', 'active' => 1, 'guid' => '050cb1f7-9df7-4a2d-857c-86f614dbd70a', 'data' => array(
					'inch' => array('key' => 'IN', 'name' => 'Inches', 'active' => 1, 'guid' => '890f57df-90f6-4f09-bc84-247ef84e5c59'),
					'cm' => array('key' => 'CM', 'name' => 'Centimeter', 'active' => 0, 'guid' => '002b02c2-4301-4da7-80d0-c225886acb13'),
				)),
				'weight' => array('key' => 'WT', 'name' => 'Weight', 'active' => 1, 'guid' => 'ee1019a3-6f62-4eac-aaf3-29cad35458be', 'data' => array(
					'pound' => array('key' => 'LB', 'name' => 'Pounds', 'active' => 1, 'guid' => '24e12952-dcc4-48a4-810d-ea315a0a1da1'),
					'kg' => array('key' => 'KG', 'name' => 'Kilograms', 'active' => 0, 'guid' => '143b9af4-909a-41ea-a05f-cb242c4f4076'),
				)),
				'temperature' => array('key' => 'TEMP', 'name' => 'Temperature', 'active' => 1, 'guid' => '9ba7dcf4-6b81-4061-b8aa-65302c0b0ff7', 'data' => array(
					'fahrenheit' => array('key' => 'F', 'name' => 'Fahrenheit', 'active' => 1, 'guid' => '3205f735-ce62-4867-ae0d-ff42786b2f17'),
					'celcius' => array('key' => 'C', 'name' => 'Celcius', 'active' => 0, 'guid' => '69589e30-e848-4f15-8801-c9ff2c35ac7c'),
				)),
				'marital' => array('key' => 'MSTATUS', 'name' => 'Marital Status', 'active' => 1, 'guid' => '262041c5-a0f8-4665-b564-d821d48664b5', 'data' => array(
					'accompanied' => array('key' => 'ACCOMP', 'name' => 'Accompanied', 'active' => 1, 'guid' => 'f32d628e-569d-4fe7-a5c5-d187d76481ea'),
					'divorced' => array('key' => 'DIVORCED', 'name' => 'Divorced', 'active' => 1, 'guid' => '206b5628-9f2e-4793-ae09-1f5e3546ec4b'),
					'married' => array('key' => 'MARRIED', 'name' => 'Married', 'active' => 1, 'guid' => 'd99356b6-37f5-4189-a0b0-ce88fe4413b5'),
					'notspec' => array('key' => 'NOTSPEC', 'name' => 'Not Specified', 'active' => 1, 'guid' => '4d080eac-e381-4e18-ac5b-df0009dc7d19'),
					'separated' => array('key' => 'SEPARATED', 'name' => 'Separated', 'active' => 1, 'guid' => '2422888a-72dd-4bb7-ba80-7e04c3a2d6a1'),
					'single' => array('key' => 'SINGLE', 'name' => 'Single', 'active' => 1, 'guid' => 'cbc78468-ce05-4ed3-9b9f-841478ce898f'),
					'unknown' => array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => '6e00691e-fb77-42fa-922b-d627722d8ac7'),
					'widowed' => array('key' => 'WIDOWED', 'name' => 'Widowed', 'active' => 1, 'guid' => '92f8d824-8733-44f2-a4b7-6354ffe38bec'),
				)),
				'confidentiality' => array('key' => 'CONFIDENT', 'name' => 'Confidentiality', 'active' => 1, 'guid' => '6ee1982a-da8f-413d-acf9-28cd974413f8', 'data' => array(
					'nosr' => array('key' => 'NOSR', 'name' => 'No Special Restrictions', 'active' => 1, 'guid' => 'c9ec5e4f-3fc9-4c6c-ac71-e5d7dec06fd5'),
					'basiconfi' => array('key' => 'BASICCONFI', 'name' => 'Basic Confidentiality', 'active' => 1, 'guid' => 'f83448df-098a-423c-97f0-d5b857f04a22'),
					'familyPlanning' => array('key' => 'FAMILYPLAN', 'name' => 'Family Planning', 'active' => 1, 'guid' => '4a3d6137-7d49-44ec-903e-c4733e8fea5a'),
					'diseaseCon' => array('key' => 'DISEASECON', 'name' => 'Disease Confidentiality', 'active' => 1, 'guid' => '23847063-41bb-4bbb-8815-f2c85bdac6b9'),
					'diseaseFPC' => array('key' => 'DISEASEFPC', 'name' => 'Disease and Family Planning Confidentiality', 'active' => 1, 'guid' => 'a07ac157-f3f4-43a5-b174-f2afb30972ba'),
					'extremeCon' => array('key' => 'EXTREMECON', 'name' => 'Extreme Confidentiality', 'active' => 1, 'guid' => 'e6d61fbe-12b7-4168-bf20-40ad9b8ef779'),
				)),
				'gender' => array('key' => 'G', 'name' => 'Gender', 'active' => 1, 'guid' => '50defb03-238b-4368-8ec6-90443bec4116', 'data' => array(
					'male' => array('key' => 'M', 'name' => 'Male', 'active' => 1, 'guid' => '08c03472-ed8e-4abd-b39f-da55555d5a29'),
					'female' => array('key' => 'F', 'name' => 'Female', 'active' => 1, 'guid' => '45432165-571a-4ad7-bd27-6f294d9550b5'),
					'unknown' => array('key' => 'U', 'name' => 'Unknown', 'active' => 1, 'guid' => '0fedcdcf-7493-4cff-aaab-02970dae04e3'),
				)),
				'race' => array('key' => 'RACE', 'name' => 'Race', 'active' => 1, 'guid' => '5c0ef400-96c2-42c0-9001-12f1c6714c15', 'data' => array(
					array('key' => 'AMERICAN', 'name' => 'American Indian or Alaska Native', 'active' => 1, 'guid' => '15690b4c-01bc-4679-8bcc-7a4b4064fff3'),
					array('key' => 'ASIAN', 'name' => 'Asian', 'active' => 1, 'guid' => '62b81a48-e47c-48cd-acb2-0b75611b693b'),
					array('key' => 'AFRICAN', 'name' => 'Black or African American', 'active' => 1, 'guid' => '1468626a-5dc0-46f6-94e0-cbe79fd287b1'),
					array('key' => 'HAWAIIAN', 'name' => 'Native Hawaiian or other Pacific Islander', 'active' => 1, 'guid' => 'b9004826-c644-4575-a930-981aed03ecba'),
					array('key' => 'WHITE', 'name' => 'White', 'active' => 1, 'guid' => '01612b36-e8bd-4342-81cd-0f8a06d35dbc'),
					array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => '1c310cc4-03f6-4d8b-b8ac-608ef4018c97'),
					array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => '615e6bc0-45ad-4a9b-9c02-2dfff8de4f86'),
					array('key' => 'BLANK', 'name' => 'Blank', 'active' => 1, 'guid' => '7fce999f-6be1-4ce0-b968-2be6a92cd08d'),
				)),
				'ethnicity' => array('key' => 'ETHNICITY', 'name' => 'Ethnicity', 'active' => 1, 'guid' => 'f50951df-90ee-42ad-b08a-1a7c8c5122c4', 'data' => array(
					array('key' => 'LATINO', 'name' => 'Hispanic or Latino', 'active' => 1, 'guid' => '5b90ddd4-8794-4d65-9237-1c2eebcb3537'),
					array('key' => 'NOT_LATINO', 'name' => 'Not Hispanic or Latino', 'active' => 1, 'guid' => '6f993301-ec4d-4945-a320-ccdd6bb048f9'),
				)),
				'language' => array('key' => 'LANGUAGE', 'name' => 'Language', 'active' => 1, 'guid' => 'e452bcc4-a0f2-4f2f-bb95-66d670983748', 'data' => array(
					array('key' => 'ENGLISH', 'name' => 'English', 'active' => 1, 'guid' => '9512b3ea-1210-4b7b-a75a-f2047f3a2775'),
					array('key' => 'SPANISH', 'name' => 'Spanish', 'active' => 1, 'guid' => '20f08cf7-b573-49f8-b62b-0bad24d317d8'),
					array('key' => 'CHINESE', 'name' => 'Chinese', 'active' => 1, 'guid' => '45ce96c7-c241-4792-96e1-1434a4c74f27'),
					array('key' => 'JAPANESE', 'name' => 'Japanese', 'active' => 1, 'guid' => '8ef93020-0381-4ddc-a05e-ce44d95aa813'),
					array('key' => 'KOREAN', 'name' => 'Korean', 'active' => 1, 'guid' => '9848c766-295b-417e-9d02-c8e62e275880'),
					array('key' => 'PORTUGUESE', 'name' => 'Portuguese', 'active' => 1, 'guid' => '93a00654-a2e6-4a1f-80e9-5da28448981c'),
					array('key' => 'RUSSIAN', 'name' => 'Russian', 'active' => 1, 'guid' => 'f870f5a5-e54c-47a2-b9c6-cc7e631fb7a5'),
					array('key' => 'SIGN_LANG', 'name' => 'Sign Language', 'active' => 1, 'guid' => '0c77dd57-5c58-4595-b9b8-3795c010d956'),
					array('key' => 'VIETNAMESE', 'name' => 'Vietnamese', 'active' => 1, 'guid' => '0a79fe88-af3d-4ee8-8eb7-e8cb1c27c585'),
					array('key' => 'TAGALOG', 'name' => 'Tagalog', 'active' => 1, 'guid' => 'ae383284-facc-4b38-97d6-0d831664ddea'),
					array('key' => 'PUNJABI', 'name' => 'Punjabi', 'active' => 1, 'guid' => 'ac3ab282-231e-482d-8dce-2d04cc47f37f'),
					array('key' => 'HINDUSTANI', 'name' => 'Hindustani', 'active' => 1, 'guid' => '998ab60e-3f2e-4eda-b0cb-444a4ce284ca'),
					array('key' => 'ARMENIAN', 'name' => 'Armenian', 'active' => 1, 'guid' => 'a5024c20-7661-4404-9866-a4cfb8e32b5d'),
					array('key' => 'ARABIC', 'name' => 'Arabic', 'active' => 1, 'guid' => 'c1ef9422-2ac2-431a-b681-21e141f25db5'),
					array('key' => 'LAOTIAN', 'name' => 'Laotian', 'active' => 1, 'guid' => '00da3f22-67bc-4429-9a4c-806d5744b5f5'),
					array('key' => 'HMONG', 'name' => 'Hmong', 'active' => 1, 'guid' => '212d9021-afe6-4891-9812-d56843f01729'),
					array('key' => 'CAMBODIAN', 'name' => 'Cambodian', 'active' => 1, 'guid' => '53e39301-5998-4f09-914e-7c7421f255dd'),
					array('key' => 'FINNISH', 'name' => 'Finnish', 'active' => 1, 'guid' => 'e1d302bc-01f4-4bae-9c7f-80f31f017fac'),
					array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => 'a6661d4f-92cc-4b82-96b0-9f1689696a44'),
				)),
				'educationLevel' => array('key' => 'EDUC_LEVEL', 'name' => 'Education Level', 'active' => 1, 'guid' => 'a6661d4f-92cc-4b82-96b0-9f1689696a44', 'data' => array(
					array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => 'f737387e-599a-46dd-ab4c-7c0aadd13584'),
					array('key' => 'NONE-ILLIT', 'name' => 'None-illiterate', 'active' => 1, 'guid' => '92bfcada-ed34-4044-92ef-13d4159e9323'),
					array('key' => 'SOME_ELEM', 'name' => 'Some Elementary Education', 'active' => 1, 'guid' => '32551954-6c18-472d-8ecc-70e1d70f00a2'),
					array('key' => 'SOME_MID', 'name' => 'Some Middle School', 'active' => 1, 'guid' => '58fb8a74-4e2a-45dd-8b89-9b2dbf2b6f04'),
					array('key' => 'SOME_HIGH', 'name' => 'Some High School', 'active' => 1, 'guid' => 'eb5acb09-9ddf-4885-9364-e99e09cb3439'),
					array('key' => 'HIGHSCHOOL', 'name' => 'High School Degree', 'active' => 1, 'guid' => '538b0d13-53f3-4226-aa68-b2888f648dd2'),
					array('key' => 'TECHSCHOOL', 'name' => 'Vocational/Tech School', 'active' => 1, 'guid' => 'fd89f00c-bbc0-4eb4-8cb2-14da21fe551b'),
					array('key' => 'COLLEGE', 'name' => 'Some College', 'active' => 1, 'guid' => 'c1d862f3-d1e9-4bad-9023-c9a9d265a3fa'),
					array('key' => 'ASSOC_DEG', 'name' => 'Associates Degree', 'active' => 1, 'guid' => '52110bf3-91c6-4d3f-a264-425296ba9c38'),
					array('key' => 'BACHELORS', 'name' => 'Bachelors Degree', 'active' => 1, 'guid' => 'd34110cf-f939-4613-9f48-2dc287f57644'),
					array('key' => 'POST_GRAD', 'name' => 'Post Grad College', 'active' => 1, 'guid' => 'b38794fd-efca-4849-97f3-6d63f8ec49b4'),
					array('key' => 'MASTERS', 'name' => 'Masters Degree', 'active' => 1, 'guid' => '84bfe1c2-4886-4c18-8300-2efed7fbb985'),
					array('key' => 'ADVANCED', 'name' => 'Advanced Degree', 'active' => 1, 'guid' => 'cb052923-6500-47e0-ad86-2d789d271ebe'),
					array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => 'e280299d-3cc7-45c4-badd-02a0a47b625e'),
				)),
				'migrantStatus' => array('key' => 'MIG_STAT', 'name' => 'Migrant Status', 'active' => 1, 'guid' => '916a01eb-c140-4a64-a0a8-235a5e31d56e', 'data' => array(
					array('key' => 'MIGRANT', 'name' => 'Migrant Worker', 'active' => 1, 'guid' => 'b0f7fad4-30f4-41fb-b1f8-aec3fbf7a23a'),
					array('key' => 'SEASONAL', 'name' => 'Seasonal Worker', 'active' => 1, 'guid' => '7a1cfada-f7e4-4f17-a03f-b05f324eabd6'),
					array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => '797b624c-5faa-4a4e-95a6-ae7a0e5bb7da'),
				)),
				'income' => array('key' => 'INCOME', 'name' => 'Income', 'active' => 1, 'guid' => 'f98a8288-60c3-4c8d-a695-af4c1b183bf4', 'data' => array(
					array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => '81a19dc7-aef8-4baf-8b8c-a9186d9893dd'),
					array('key' => 'UNDER100', 'name' => 'Under 100% of Poverty', 'active' => 1, 'guid' => '43187d0a-b011-463c-bd23-63099426665d'),
					array('key' => 'BET100-200', 'name' => '100-200% of Poverty', 'active' => 1, 'guid' => 'd41e3f67-ff32-42a5-94e7-752009bffe41'),
					array('key' => 'ABOVE200', 'name' => 'Above 200% of Poverty', 'active' => 1, 'guid' => '271cd8a5-e98f-4d38-b2fe-235ebf48999b'),
				)),
				'employmentStatus' => array('key' => 'EMP_STATUS', 'name' => 'Employment Status', 'active' => 1, 'guid' => 'a14c35ce-3ac7-4283-9343-290f74da330d', 'data' => array(
					array('key' => 'EMPLOYED', 'name' => 'Employed', 'active' => 1, 'guid' => 'da7effc9-4ea2-49d1-973a-de2ef96cc39f'),
					array('key' => 'UNEMPLOYED', 'name' => 'Unemployed', 'active' => 1, 'guid' => '3ff2f365-d370-4097-b825-7c0f51bef1f3'),
					array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => '43bc63aa-79aa-48c3-a141-dded6bf62d3a'),
				)),
			);

			$level = array();
			$level['guid'] = '0ad33a7d-8b52-4cc3-bbe0-62a22dc5590a';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array();
			$data[] = $level;

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateGeographyPreferencesEnum($force = true) {
		$ret = false;
		do {
			$name = 'Geography';
			$key = 'GEOGRAPH';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'countries' => array('key' => 'COUNTRIES', 'name' => 'Countries', 'active' => 1, 'guid' => '26efee04-703b-470c-af73-d84dcb834d2c', 'data' => array(
					array('key' => 'AFG', 'name' => 'Afghanistan', 'active' => 1, 'guid' => '59079755-0cb5-4272-a23a-3c238388c427'),
					array('key' => 'ALB', 'name' => 'Albania', 'active' => 1, 'guid' => 'f441d4a3-aa89-4d1d-87f3-25700c4d1eaf'),
					array('key' => 'DZA', 'name' => 'Algeria', 'active' => 1, 'guid' => 'd570a0e4-578a-41af-90b0-aeff3383b193'),
					array('key' => 'ASM', 'name' => 'American Samoa', 'active' => 1, 'guid' => 'beec919e-d98a-441c-b953-07db61a9b91d'),
					array('key' => 'AND', 'name' => 'Andorra', 'active' => 1, 'guid' => '8e120ea1-f6fb-47d4-9801-30402a912a5f'),
					array('key' => 'AGO', 'name' => 'Angola', 'active' => 1, 'guid' => '4d999913-b94a-4aea-89b3-fa80d9c320cb'),
					array('key' => 'AIA', 'name' => 'Anguilla', 'active' => 1, 'guid' => '361e7264-b860-4d38-a3e7-a5c77bf124a1'),
					array('key' => 'ATA', 'name' => 'Antarctica', 'active' => 1, 'guid' => 'cec64974-3e91-4d95-bf8a-e6234ceaa182'),
					array('key' => 'ATG', 'name' => 'Antigua and Barbuda', 'active' => 1, 'guid' => 'bbed9bfc-931f-45ea-924a-46876a66aa90'),
					array('key' => 'ARG', 'name' => 'Argentina', 'active' => 1, 'guid' => '8fde6a26-6b8a-49c1-8644-2e2be1fa06e7'),
					array('key' => 'ARM', 'name' => 'Armenia', 'active' => 1, 'guid' => '50cd2232-ef12-463a-b907-4730a2537bee'),
					array('key' => 'ABW', 'name' => 'Aruba', 'active' => 1, 'guid' => '995a082e-d492-4d82-b0e9-081a2c1ad571'),
					array('key' => 'AUS', 'name' => 'Australia', 'active' => 1, 'guid' => '60127330-6f79-422f-bbf0-3a7b6e3bcd48'),
					array('key' => 'AUT', 'name' => 'Austria', 'active' => 1, 'guid' => '0a820d72-3598-4334-8af9-6899b24d7857'),
					array('key' => 'AZE', 'name' => 'Azerbaijan', 'active' => 1, 'guid' => 'a98fa643-3ade-4409-84ae-2cfda4698b74'),
					array('key' => 'BHS', 'name' => 'Bahamas', 'active' => 1, 'guid' => '71878d10-8746-49a0-a85e-d530e9cf79a6'),
					array('key' => 'BHR', 'name' => 'Bahrain', 'active' => 1, 'guid' => '0a687c35-d4ba-49ec-9c7c-e3c0c2e2a33b'),
					array('key' => 'BGD', 'name' => 'Bangladesh', 'active' => 1, 'guid' => '1e50ad80-cd51-429b-8df0-52674842878a'),
					array('key' => 'BRB', 'name' => 'Barbados', 'active' => 1, 'guid' => '98ef6373-8ef3-4694-9b94-1a9d6de7ef71'),
					array('key' => 'BLR', 'name' => 'Belarus', 'active' => 1, 'guid' => '61793a0d-35cc-4a4d-906c-1f85f47bdb3e'),
					array('key' => 'BEL', 'name' => 'Belgium', 'active' => 1, 'guid' => 'c13ff74b-5771-4303-9339-06c998e9157f'),
					array('key' => 'BLZ', 'name' => 'Belize', 'active' => 1, 'guid' => '1e1b5f88-84bc-443c-91c4-7578d2d41645'),
					array('key' => 'BEN', 'name' => 'Benin', 'active' => 1, 'guid' => '81ea7a21-1b1d-479b-9e45-f9455337c6d1'),
					array('key' => 'BMU', 'name' => 'Bermuda', 'active' => 1, 'guid' => 'e6c26c20-df0c-41a4-a4a5-d90b6ab018e3'),
					array('key' => 'BTN', 'name' => 'Bhutan', 'active' => 1, 'guid' => '3879739e-ac0e-488c-951c-a1ad4f22921c'),
					array('key' => 'BOL', 'name' => 'Bolivia', 'active' => 1, 'guid' => '141aa705-90c2-41c2-b92b-170c569b771c'),
					array('key' => 'BIH', 'name' => 'Bosnia and Herzegovina', 'active' => 1, 'guid' => 'f04afea9-285f-40d6-82c2-f88f069b682d'),
					array('key' => 'BWA', 'name' => 'Botswana', 'active' => 1, 'guid' => 'ddefacfc-c6a5-4962-9609-7e21aca058b9'),
					array('key' => 'BVT', 'name' => 'Bouvet Island', 'active' => 1, 'guid' => 'e31d667f-8cee-4aa9-b344-7f7247b12057'),
					array('key' => 'BRA', 'name' => 'Brazil', 'active' => 1, 'guid' => '18111feb-c002-42dc-9656-8777d15230d5'),
					array('key' => 'IOT', 'name' => 'British Indian Ocean Territory', 'active' => 1, 'guid' => '06337256-6669-4e45-b041-d5ffe4b9d694'),
					array('key' => 'BRN', 'name' => 'Brunei Darussalam', 'active' => 1, 'guid' => '8d9d0232-e22a-4222-9470-2981259e3104'),
					array('key' => 'BGR', 'name' => 'Bulgaria', 'active' => 1, 'guid' => '030a96e9-f771-443f-bfb1-c2886ad1d7e2'),
					array('key' => 'BFA', 'name' => 'Burkina Faso', 'active' => 1, 'guid' => 'f805d211-c37c-419f-bce0-c078e0067622'),
					array('key' => 'BDI', 'name' => 'Burundi', 'active' => 1, 'guid' => 'b379bf6b-324b-4aae-ac9b-71ea90d138dc'),
					array('key' => 'KHM', 'name' => 'Cambodia', 'active' => 1, 'guid' => '2209f775-ec5e-4732-aa03-84f051a3d01e'),
					array('key' => 'CMR', 'name' => 'Cameroon', 'active' => 1, 'guid' => '0764c7e2-112f-4911-805a-ae3d57180cb8'),
					array('key' => 'CAN', 'name' => 'Canada', 'active' => 1, 'guid' => '8b4b4df0-3032-485d-b4b3-c3499fe1b867'),
					array('key' => 'CPV', 'name' => 'Cape Verde', 'active' => 1, 'guid' => 'c0ddd36b-7635-461c-8865-3bd90c2326b5'),
					array('key' => 'CYM', 'name' => 'Cayman Islands', 'active' => 1, 'guid' => '474ff376-174f-4d0a-bb1d-17dacca4220c'),
					array('key' => 'CAF', 'name' => 'Central African Republic', 'active' => 1, 'guid' => 'b278f200-c589-47e1-8e4e-eb4c9061cb83'),
					array('key' => 'TCD', 'name' => 'Chad', 'active' => 1, 'guid' => '219c6ebf-14b2-41e2-85f7-fcde9830c3a4'),
					array('key' => 'CHL', 'name' => 'Chile', 'active' => 1, 'guid' => '1852306e-4cd6-44dd-9b42-261b7b10cc21'),
					array('key' => 'CHN', 'name' => 'China', 'active' => 1, 'guid' => 'e5718f62-4e6a-406e-b28f-9a288cd62e77'),
					array('key' => 'CXR', 'name' => 'Christmas Island', 'active' => 1, 'guid' => '255b84cf-bb3b-4f59-89a4-46d84eb82c22'),
					array('key' => 'CCK', 'name' => 'Cocos (Keeling) Islands', 'active' => 1, 'guid' => 'e1d679d0-cf2d-458e-ba1f-a65bfd2280d8'),
					array('key' => 'COL', 'name' => 'Colombia', 'active' => 1, 'guid' => 'e3cf9fb1-fd22-469f-bb98-1438caee921f'),
					array('key' => 'COM', 'name' => 'Comoros', 'active' => 1, 'guid' => '4d1f6b9a-da3d-4e29-ba7f-3c8e3f8c9d9b'),
					array('key' => 'COG', 'name' => 'Congo', 'active' => 1, 'guid' => '20e0b416-e05a-4c9a-aed3-4dbe16d018cd'),
					array('key' => 'COD', 'name' => 'Congo, the Democratic Republic of the', 'active' => 1, 'guid' => '0aae6f84-d23a-4c49-9526-9366ba92fe4e'),
					array('key' => 'COK', 'name' => 'Cook Islands', 'active' => 1, 'guid' => 'd3bd4805-69cf-4947-86e7-4e50d4cb792d'),
					array('key' => 'CRI', 'name' => 'Costa Rica', 'active' => 1, 'guid' => 'b650b1c8-cceb-4d29-b700-ec0114c8318a'),
					array('key' => 'CIV', 'name' => 'Cote D\'Ivoire', 'active' => 1, 'guid' => 'c513fa33-82aa-4ea9-997e-2f6a3ef847a3'),
					array('key' => 'HRV', 'name' => 'Croatia', 'active' => 1, 'guid' => 'a7abe37d-ff7c-44a5-93db-f327c1e2b0a2'),
					array('key' => 'CUB', 'name' => 'Cuba', 'active' => 1, 'guid' => '8e7d2b1e-a947-4ce0-bcbb-32f8ec0ac005'),
					array('key' => 'CYP', 'name' => 'Cyprus', 'active' => 1, 'guid' => '4d0ef80d-5924-4b4d-be7c-5b99c79c97e1'),
					array('key' => 'CZE', 'name' => 'Czech Republic', 'active' => 1, 'guid' => '8e590715-a55a-41a0-91ed-3a492b140a60'),
					array('key' => 'DNK', 'name' => 'Denmark', 'active' => 1, 'guid' => '60af0e28-32ae-411b-91c7-e363d2be50ac'),
					array('key' => 'DJI', 'name' => 'Djibouti', 'active' => 1, 'guid' => '89d38463-cdf7-4fc3-a7c2-a9a3e7081fbd'),
					array('key' => 'DMA', 'name' => 'Dominica', 'active' => 1, 'guid' => '90ddde4c-2c23-4a42-81ef-aac79d01f122'),
					array('key' => 'DOM', 'name' => 'Dominican Republic', 'active' => 1, 'guid' => '91e97f1f-03a4-413d-9e72-ae8cdc151256'),
					array('key' => 'ECU', 'name' => 'Ecuador', 'active' => 1, 'guid' => 'd255498a-d6a5-4a6c-840c-27f26e549e51'),
					array('key' => 'EGY', 'name' => 'Egypt', 'active' => 1, 'guid' => '11820701-d0aa-46b6-b599-de8ca6c6d36a'),
					array('key' => 'SLV', 'name' => 'El Salvador', 'active' => 1, 'guid' => 'aa9aa50a-9922-4a69-9a18-6776fd2d8197'),
					array('key' => 'GNQ', 'name' => 'Equatorial Guinea', 'active' => 1, 'guid' => 'd4c25883-0730-4d9f-8019-37a7ecdda403'),
					array('key' => 'ERI', 'name' => 'Eritrea', 'active' => 1, 'guid' => '7b379e45-4290-401d-a595-add28c719b2e'),
					array('key' => 'EST', 'name' => 'Estonia', 'active' => 1, 'guid' => '454ce628-c018-4101-a714-8bda06596a93'),
					array('key' => 'ETH', 'name' => 'Ethiopia', 'active' => 1, 'guid' => '030c26ac-cbb3-4ec0-8d1c-05267f4d987a'),
					array('key' => 'FLK', 'name' => 'Falkland Islands (Malvinas)', 'active' => 1, 'guid' => 'ad475921-80ca-4bec-9cd8-10f10bc1aecf'),
					array('key' => 'FRO', 'name' => 'Faroe Islands', 'active' => 1, 'guid' => '7138532c-b5f8-47de-9eae-3d078e56fc0d'),
					array('key' => 'FJI', 'name' => 'Fiji', 'active' => 1, 'guid' => 'ce65b2c7-8f76-44b2-9988-e75c86124bb0'),
					array('key' => 'FIN', 'name' => 'Finland', 'active' => 1, 'guid' => '699cf3c9-dcde-4c8e-9366-413f3a8f6018'),
					array('key' => 'FRA', 'name' => 'France', 'active' => 1, 'guid' => '21367e82-8de7-4852-a24a-a794b76e54e4'),
					array('key' => 'GUF', 'name' => 'French Guiana', 'active' => 1, 'guid' => '3ba60b80-e873-43cd-ba54-4737223b9369'),
					array('key' => 'PYF', 'name' => 'French Polynesia', 'active' => 1, 'guid' => '34e062db-f494-47cb-87ce-a62bd84d36a5'),
					array('key' => 'ATF', 'name' => 'French Southern Territories', 'active' => 1, 'guid' => 'f098f9e3-4204-4bf2-a281-0b1684fc3016'),
					array('key' => 'GAB', 'name' => 'Gabon', 'active' => 1, 'guid' => '13147b36-afb7-40a3-a875-4df2b3e3c87b'),
					array('key' => 'GMB', 'name' => 'Gambia', 'active' => 1, 'guid' => '461c5cef-2458-4a58-b497-61a5ce29423e'),
					array('key' => 'GEO', 'name' => 'Georgia', 'active' => 1, 'guid' => '1f5e929e-788a-4046-8712-b7a9a0f8df14'),
					array('key' => 'DEU', 'name' => 'Germany', 'active' => 1, 'guid' => '256c4d50-600d-4e15-b3dc-ad4a6a97fc4c'),
					array('key' => 'GHA', 'name' => 'Ghana', 'active' => 1, 'guid' => '5c0e1cc0-9148-4104-861a-0c25ba98e39f'),
					array('key' => 'GIB', 'name' => 'Gibraltar', 'active' => 1, 'guid' => '7bb6f5a3-4352-4a24-997f-d5e29a0bc929'),
					array('key' => 'GRC', 'name' => 'Greece', 'active' => 1, 'guid' => '29496d8c-6578-4c31-81fa-4b7a277904ae'),
					array('key' => 'GRL', 'name' => 'Greenland', 'active' => 1, 'guid' => '9960e854-698b-4ca4-b299-8796189034b8'),
					array('key' => 'GRD', 'name' => 'Grenada', 'active' => 1, 'guid' => '1aa0e3d4-d9ed-49b9-adea-112dc9f5f80d'),
					array('key' => 'GLP', 'name' => 'Guadeloupe', 'active' => 1, 'guid' => '1a7990f0-f4ca-4b74-bdee-64d130f157d7'),
					array('key' => 'GUM', 'name' => 'Guam', 'active' => 1, 'guid' => '5071075a-ad7b-4f10-b3cf-d16a8d437223'),
					array('key' => 'GTM', 'name' => 'Guatemala', 'active' => 1, 'guid' => '4a56c1ea-25a7-485a-88d6-54360b22f87a'),
					array('key' => 'GIN', 'name' => 'Guinea', 'active' => 1, 'guid' => 'a316a10e-053d-483b-a3e4-7ef746347cf8'),
					array('key' => 'GNB', 'name' => 'Guinea-Bissau', 'active' => 1, 'guid' => 'ec785d71-d628-4164-adc7-24b9af595ac9'),
					array('key' => 'GUY', 'name' => 'Guyana', 'active' => 1, 'guid' => 'b9951b6f-d6d0-4093-8601-7049500a6ea4'),
					array('key' => 'HTI', 'name' => 'Haiti', 'active' => 1, 'guid' => '406b24a4-e3a0-442c-807f-56e6d8ab5eb0'),
					array('key' => 'HMD', 'name' => 'Heard Island and Mcdonald Islands', 'active' => 1, 'guid' => 'a2d63cdb-5473-4f8b-8ece-f968d2af5552'),
					array('key' => 'VAT', 'name' => 'Holy See (Vatican City State)', 'active' => 1, 'guid' => 'd128ad6f-c3e1-4bc2-b7ae-93d736f521e0'),
					array('key' => 'HND', 'name' => 'Honduras', 'active' => 1, 'guid' => '987eaf69-2243-4ba0-9d6c-9d634191a7e9'),
					array('key' => 'HKG', 'name' => 'Hong Kong', 'active' => 1, 'guid' => 'd9e94401-358d-4b07-a21b-9b5b175436b4'),
					array('key' => 'HUN', 'name' => 'Hungary', 'active' => 1, 'guid' => '75f5f7a4-f86a-4b5c-a5a5-2601aaa9bbf2'),
					array('key' => 'ISL', 'name' => 'Iceland', 'active' => 1, 'guid' => 'a760af6a-0a0b-49e2-bd63-ee454f7c93af'),
					array('key' => 'IND', 'name' => 'India', 'active' => 1, 'guid' => '72c50461-b8f6-48d8-a80d-5081d127e1a4'),
					array('key' => 'IDN', 'name' => 'Indonesia', 'active' => 1, 'guid' => 'b637b9bb-b66e-4db1-9636-899140baabd6'),
					array('key' => 'IRN', 'name' => 'Iran, Islamic Republic of', 'active' => 1, 'guid' => '6b2ea029-e97a-4ba2-a94d-a02e074986e8'),
					array('key' => 'IRQ', 'name' => 'Iraq', 'active' => 1, 'guid' => '68ed3cc1-1a70-449d-9382-1a6b3d91e687'),
					array('key' => 'IRL', 'name' => 'Ireland', 'active' => 1, 'guid' => '0aee7705-d0e1-4f52-9717-129f3b13b8de'),
					array('key' => 'ISR', 'name' => 'Israel', 'active' => 1, 'guid' => 'a0f00f25-f05a-4f44-997c-a63bd501a033'),
					array('key' => 'ITA', 'name' => 'Italy', 'active' => 1, 'guid' => '74f3bb3a-c20f-42fc-910a-ad37e37b26ab'),
					array('key' => 'JAM', 'name' => 'Jamaica', 'active' => 1, 'guid' => '5ae3293f-874a-4236-b02c-4f936dcbd5da'),
					array('key' => 'JPN', 'name' => 'Japan', 'active' => 1, 'guid' => 'b3463578-aba7-4398-8d56-d9f489cd57a2'),
					array('key' => 'JOR', 'name' => 'Jordan', 'active' => 1, 'guid' => 'a2c7b192-7458-470f-86b1-6276d763bc91'),
					array('key' => 'KAZ', 'name' => 'Kazakhstan', 'active' => 1, 'guid' => 'efcb3f83-6682-4252-b1f6-4904242d49db'),
					array('key' => 'KEN', 'name' => 'Kenya', 'active' => 1, 'guid' => 'a781e5e6-61f4-4e74-9f1f-d1afca1f3b9b'),
					array('key' => 'KIR', 'name' => 'Kiribati', 'active' => 1, 'guid' => 'b27ac789-f5f8-43d2-afbc-90615c373807'),
					array('key' => 'PRK', 'name' => 'Korea, Democratic People\'s Republic of', 'active' => 1, 'guid' => 'bb2ec30a-6ce5-4eec-9140-b1fee7dc4ede'),
					array('key' => 'KOR', 'name' => 'Korea, Republic of', 'active' => 1, 'guid' => '5d46e5cb-334b-4320-a5fd-41ab72ecdba5'),
					array('key' => 'KWT', 'name' => 'Kuwait', 'active' => 1, 'guid' => '2f1de267-142c-4b44-8456-de5bd5be4e0a'),
					array('key' => 'KGZ', 'name' => 'Kyrgyzstan', 'active' => 1, 'guid' => '9306f965-dab1-4d49-909b-83ce44beec16'),
					array('key' => 'LAO', 'name' => 'Lao People\'s Democratic Republic', 'active' => 1, 'guid' => '5fb27190-a139-4f76-8391-4d5335549051'),
					array('key' => 'LVA', 'name' => 'Latvia', 'active' => 1, 'guid' => '8e94bdf2-edb9-4bb8-b2e6-c760466706a6'),
					array('key' => 'LBN', 'name' => 'Lebanon', 'active' => 1, 'guid' => 'c9b3e7ee-e3a7-45ab-a42d-b4958fc73a47'),
					array('key' => 'LSO', 'name' => 'Lesotho', 'active' => 1, 'guid' => 'b79277cd-c9cf-4b93-ac12-0ea2c314a7d7'),
					array('key' => 'LBR', 'name' => 'Liberia', 'active' => 1, 'guid' => 'd3adc442-064f-40da-8b81-efd7857493ec'),
					array('key' => 'LBY', 'name' => 'Libyan Arab Jamahiriya', 'active' => 1, 'guid' => 'ef0e69f7-d57c-4bd3-943c-12aa20cfd4a2'),
					array('key' => 'LIE', 'name' => 'Liechtenstein', 'active' => 1, 'guid' => 'bf0936e4-472b-4914-8838-54475a089b69'),
					array('key' => 'LTU', 'name' => 'Lithuania', 'active' => 1, 'guid' => 'b211166d-f926-44a2-bcf6-10fb1f2ce61e'),
					array('key' => 'LUX', 'name' => 'Luxembourg', 'active' => 1, 'guid' => 'ad36240f-d856-424d-919a-c1bc20683587'),
					array('key' => 'MAC', 'name' => 'Macao', 'active' => 1, 'guid' => 'f4596815-e19e-4b7b-9084-c82ea7aa9db3'),
					array('key' => 'MKD', 'name' => 'Macedonia, the Former Yugoslav Republic of', 'active' => 1, 'guid' => 'bf85d7c8-5f7c-4b02-9428-be20b731aaff'),
					array('key' => 'MDG', 'name' => 'Madagascar', 'active' => 1, 'guid' => '54f82771-2189-4ee2-a7d4-469830d64b70'),
					array('key' => 'MWI', 'name' => 'Malawi', 'active' => 1, 'guid' => '58a0f404-af02-49c2-a4d8-af14395c08e4'),
					array('key' => 'MYS', 'name' => 'Malaysia', 'active' => 1, 'guid' => '25ca855d-f809-40a9-9f7c-f434c36aac2c'),
					array('key' => 'MDV', 'name' => 'Maldives', 'active' => 1, 'guid' => 'f750d787-db0e-45b5-83ac-9586d1cc2bfd'),
					array('key' => 'MLI', 'name' => 'Mali', 'active' => 1, 'guid' => 'a897911f-e844-4dbc-b6b2-b627465bddc0'),
					array('key' => 'MLT', 'name' => 'Malta', 'active' => 1, 'guid' => '6ebccdfb-0b2b-4d47-b257-2578cbcf2051'),
					array('key' => 'MHL', 'name' => 'Marshall Islands', 'active' => 1, 'guid' => '8acbcd15-6d93-4184-aa4e-18123a5b4095'),
					array('key' => 'MTQ', 'name' => 'Martinique', 'active' => 1, 'guid' => 'ad2fad19-ab4e-4f29-ab2f-49e87adbac87'),
					array('key' => 'MRT', 'name' => 'Mauritania', 'active' => 1, 'guid' => '552a99de-28bb-49c0-a69f-60c9c779184a'),
					array('key' => 'MUS', 'name' => 'Mauritius', 'active' => 1, 'guid' => 'e1d5d72c-b369-48cc-bc12-b86d79c65db8'),
					array('key' => 'MYT', 'name' => 'Mayotte', 'active' => 1, 'guid' => 'a7847483-2ac9-4b04-8388-67dfefd8944b'),
					array('key' => 'MEX', 'name' => 'Mexico', 'active' => 1, 'guid' => '70ad5611-56a8-4969-8b6f-356cc6907887'),
					array('key' => 'FSM', 'name' => 'Micronesia, Federated States of', 'active' => 1, 'guid' => 'c35c7284-ecf4-417f-a8c9-ceea9d5594b8'),
					array('key' => 'MDA', 'name' => 'Moldova, Republic of', 'active' => 1, 'guid' => '7dd318cb-6dcc-43b1-a8df-d8ed8a74c988'),
					array('key' => 'MCO', 'name' => 'Monaco', 'active' => 1, 'guid' => 'b9570610-6ebe-46c7-8c4b-8115820005ec'),
					array('key' => 'MNG', 'name' => 'Mongolia', 'active' => 1, 'guid' => '5de2ada1-90ed-42ce-a06d-60cfec8f8cad'),
					array('key' => 'MSR', 'name' => 'Montserrat', 'active' => 1, 'guid' => '5095c252-f88e-46d5-a71c-ddc60d8618d5'),
					array('key' => 'MAR', 'name' => 'Morocco', 'active' => 1, 'guid' => 'c197ed91-1dfc-4a69-bdc8-8b50c094c2b0'),
					array('key' => 'MOZ', 'name' => 'Mozambique', 'active' => 1, 'guid' => 'bacf3b99-1731-4671-8d6b-659b389ee8fc'),
					array('key' => 'MMR', 'name' => 'Myanmar', 'active' => 1, 'guid' => '9e16a796-e4d0-40c5-955a-38c940c2cf35'),
					array('key' => 'NAM', 'name' => 'Namibia', 'active' => 1, 'guid' => '5eeab97a-c385-4875-9e63-de087e8d6e56'),
					array('key' => 'NRU', 'name' => 'Nauru', 'active' => 1, 'guid' => '90477e7c-984e-4f3c-b7fb-8aa054b2ab59'),
					array('key' => 'NPL', 'name' => 'Nepal', 'active' => 1, 'guid' => 'cf182319-5ebd-416d-aaa2-30b72ce49404'),
					array('key' => 'NLD', 'name' => 'Netherlands', 'active' => 1, 'guid' => 'e8a94c68-be33-456d-a7cd-d305e2febf50'),
					array('key' => 'ANT', 'name' => 'Netherlands Antilles', 'active' => 1, 'guid' => '6df8407a-2f71-4b62-a2e4-fae401e9e256'),
					array('key' => 'NCL', 'name' => 'New Caledonia', 'active' => 1, 'guid' => '2b984304-a5a1-4bb1-8fbd-4427abb1389a'),
					array('key' => 'NZL', 'name' => 'New Zealand', 'active' => 1, 'guid' => '1c56836b-f902-4a98-8c98-9ad4d660757f'),
					array('key' => 'NIC', 'name' => 'Nicaragua', 'active' => 1, 'guid' => 'c60d12b3-e550-4f4e-ae4c-7354fccc51d9'),
					array('key' => 'NER', 'name' => 'Niger', 'active' => 1, 'guid' => '76a1169e-7fe7-4d89-a3ef-9d409e2d9157'),
					array('key' => 'NGA', 'name' => 'Nigeria', 'active' => 1, 'guid' => '5b53f414-aea5-4059-bc5e-a13678686a61'),
					array('key' => 'NIU', 'name' => 'Niue', 'active' => 1, 'guid' => '9c6450df-31ff-4bd6-959b-94ae49188318'),
					array('key' => 'NFK', 'name' => 'Norfolk Island', 'active' => 1, 'guid' => '7beada37-6ee5-47d0-b8ae-b1bd8386b3d1'),
					array('key' => 'MNP', 'name' => 'Northern Mariana Islands', 'active' => 1, 'guid' => '688ee0fa-f600-4981-b2db-4ab8601e45cf'),
					array('key' => 'NOR', 'name' => 'Norway', 'active' => 1, 'guid' => 'e60e596d-fddc-4878-b486-094bc408e26e'),
					array('key' => 'OMN', 'name' => 'Oman', 'active' => 1, 'guid' => 'dc5b36e9-ba65-4aa1-91d1-c00fc70fad5f'),
					array('key' => 'PAK', 'name' => 'Pakistan', 'active' => 1, 'guid' => '2a9e9d26-6189-410f-855e-fa295262f9dd'),
					array('key' => 'PLW', 'name' => 'Palau', 'active' => 1, 'guid' => 'ec135921-b5cc-470d-a1f2-eb8c92774756'),
					array('key' => 'PSE', 'name' => 'Palestinian Territory, Occupied', 'active' => 1, 'guid' => '14cbeb6b-81af-494c-8679-6347bedb1e2a'),
					array('key' => 'PAN', 'name' => 'Panama', 'active' => 1, 'guid' => '4781047a-72ce-4600-80e9-c0590ffc3bfc'),
					array('key' => 'PNG', 'name' => 'Papua New Guinea', 'active' => 1, 'guid' => '1dbd8e4c-d28e-4f67-9142-5c4f8ffc150b'),
					array('key' => 'PRY', 'name' => 'Paraguay', 'active' => 1, 'guid' => 'aabfb14b-aaf4-4384-8146-0aaa9d3f6312'),
					array('key' => 'PER', 'name' => 'Peru', 'active' => 1, 'guid' => 'fb4995c9-c8ff-4a42-9239-32908f1e1b12'),
					array('key' => 'PHL', 'name' => 'Philippines', 'active' => 1, 'guid' => '0a6acf26-de60-4152-babb-3751f2d63327'),
					array('key' => 'PCN', 'name' => 'Pitcairn', 'active' => 1, 'guid' => '13941a18-ff98-4cec-b9c0-236fd797fa35'),
					array('key' => 'POL', 'name' => 'Poland', 'active' => 1, 'guid' => '7e1a8a20-f9e3-4d67-8887-602f420f6172'),
					array('key' => 'PRT', 'name' => 'Portugal', 'active' => 1, 'guid' => '29cb3aa5-ac95-410f-a2ae-5af4e1e582f6'),
					array('key' => 'PRI', 'name' => 'Puerto Rico', 'active' => 1, 'guid' => 'feaae5a2-f927-4410-ae35-9dbbdd4c4e08'),
					array('key' => 'QAT', 'name' => 'Qatar', 'active' => 1, 'guid' => '6598ca07-ca58-4486-ad33-36ab007bbc0b'),
					array('key' => 'REU', 'name' => 'Reunion', 'active' => 1, 'guid' => '4cad5120-2f41-4736-92d9-04928a3cf34d'),
					array('key' => 'ROM', 'name' => 'Romania', 'active' => 1, 'guid' => '19afa441-f620-48a7-a1c2-78adfdafd884'),
					array('key' => 'RUS', 'name' => 'Russian Federation', 'active' => 1, 'guid' => '3552db8b-f83f-4ebd-af2c-ced58c46c00d'),
					array('key' => 'RWA', 'name' => 'Rwanda', 'active' => 1, 'guid' => 'cfe94f13-4db8-4340-9686-30d67cb114de'),
					array('key' => 'SHN', 'name' => 'Saint Helena', 'active' => 1, 'guid' => '62e7a732-8fb5-4cca-a632-d11f34b918fb'),
					array('key' => 'KNA', 'name' => 'Saint Kitts and Nevis', 'active' => 1, 'guid' => 'eb15399c-21eb-44cd-a9f7-22ad8892c424'),
					array('key' => 'LCA', 'name' => 'Saint Lucia', 'active' => 1, 'guid' => '0d0ee797-85af-4f5d-af8a-4b78e2c0e05b'),
					array('key' => 'SPM', 'name' => 'Saint Pierre and Miquelon', 'active' => 1, 'guid' => '7bf1bf8d-4fda-477b-9550-75edac6acdbf'),
					array('key' => 'VCT', 'name' => 'Saint Vincent and the Grenadines', 'active' => 1, 'guid' => 'b3d581a1-65bc-4c24-8126-2c11b7673fd9'),
					array('key' => 'WSM', 'name' => 'Samoa', 'active' => 1, 'guid' => '0b6d7503-0718-4fd3-a439-b1394a159df7'),
					array('key' => 'SMR', 'name' => 'San Marino', 'active' => 1, 'guid' => '28b778ba-f543-44b2-8f77-88f92ccbc7d6'),
					array('key' => 'STP', 'name' => 'Sao Tome and Principe', 'active' => 1, 'guid' => '7e5b8fb4-40fa-45a9-9196-9d7770a5ec5b'),
					array('key' => 'SAU', 'name' => 'Saudi Arabia', 'active' => 1, 'guid' => '7a59b7fd-2aca-4761-89c4-1ec363486d7e'),
					array('key' => 'SEN', 'name' => 'Senegal', 'active' => 1, 'guid' => 'ad6acbad-3043-4c06-82df-6fb765463fb3'),
					array('key' => 'SCG', 'name' => 'Serbia and Montenegro', 'active' => 1, 'guid' => 'b92bb4cf-931d-4dc3-a947-d43fb6271366'),
					array('key' => 'SYC', 'name' => 'Seychelles', 'active' => 1, 'guid' => '1024ace5-9192-4c04-8a72-fd0a37667877'),
					array('key' => 'SLE', 'name' => 'Sierra Leone', 'active' => 1, 'guid' => '85f982d5-f899-4707-8d2e-66359e2e6383'),
					array('key' => 'SGP', 'name' => 'Singapore', 'active' => 1, 'guid' => 'cd9c4592-e49f-4ba6-9b9a-a1fa6d3fdfd8'),
					array('key' => 'SVK', 'name' => 'Slovakia', 'active' => 1, 'guid' => '4580e435-5b2c-47c0-874e-9cf60a495be1'),
					array('key' => 'SVN', 'name' => 'Slovenia', 'active' => 1, 'guid' => '0e1a1c1a-f66e-4ea8-8dfd-cb62a015e66c'),
					array('key' => 'SLB', 'name' => 'Solomon Islands', 'active' => 1, 'guid' => '1b279a86-7e78-4d51-bf91-167382a18d4b'),
					array('key' => 'SOM', 'name' => 'Somalia', 'active' => 1, 'guid' => '6ac35ee4-92c6-4c8c-97b3-84f18041128a'),
					array('key' => 'ZAF', 'name' => 'South Africa', 'active' => 1, 'guid' => '5018fcc0-a6c3-46f3-b79b-7b4a20665436'),
					array('key' => 'SGS', 'name' => 'South Georgia and the South Sandwich Islands', 'active' => 1, 'guid' => '30055ebf-d266-4010-bed0-9267de111e75'),
					array('key' => 'ESP', 'name' => 'Spain', 'active' => 1, 'guid' => 'bf9fb9e4-c468-4308-b77d-39c7b9d743fc'),
					array('key' => 'LKA', 'name' => 'Sri Lanka', 'active' => 1, 'guid' => '5a8b7704-2867-4f64-a596-e38fcbe22f9c'),
					array('key' => 'SDN', 'name' => 'Sudan', 'active' => 1, 'guid' => '74521351-b11e-4be1-b02e-5c419d6bb960'),
					array('key' => 'SUR', 'name' => 'Suriname', 'active' => 1, 'guid' => '9ae29198-561b-4221-9d43-570bce26b986'),
					array('key' => 'SJM', 'name' => 'Svalbard and Jan Mayen', 'active' => 1, 'guid' => '1018e0d9-9217-47fb-b910-da30f9352327'),
					array('key' => 'SWZ', 'name' => 'Swaziland', 'active' => 1, 'guid' => 'b6ac0a07-b198-4bed-b01a-f40b9156b691'),
					array('key' => 'SWE', 'name' => 'Sweden', 'active' => 1, 'guid' => '9a48b2ae-31a6-4a01-834c-ba21b092ebcf'),
					array('key' => 'CHE', 'name' => 'Switzerland', 'active' => 1, 'guid' => '42e0cab8-ba23-492b-9e74-ae7f037704c2'),
					array('key' => 'SYR', 'name' => 'Syrian Arab Republic', 'active' => 1, 'guid' => 'd740e179-2cbe-4254-a9af-5729561e4b65'),
					array('key' => 'TWN', 'name' => 'Taiwan, Province of China', 'active' => 1, 'guid' => '770d4e58-219e-4ac4-a249-186d4b6e2b7c'),
					array('key' => 'TJK', 'name' => 'Tajikistan', 'active' => 1, 'guid' => '4c73aa4d-641f-4420-a6cb-1f5a6a0d537d'),
					array('key' => 'TZA', 'name' => 'Tanzania, United Republic of', 'active' => 1, 'guid' => 'bc99aaaf-78ac-4a74-ade1-863ce2c9d579'),
					array('key' => 'THA', 'name' => 'Thailand', 'active' => 1, 'guid' => '4d4be004-22e8-420b-9636-5d3cb3a02d85'),
					array('key' => 'TLS', 'name' => 'Timor-Leste', 'active' => 1, 'guid' => 'b7f238ba-1c63-4d93-98f0-df2963742135'),
					array('key' => 'TGO', 'name' => 'Togo', 'active' => 1, 'guid' => '6d702890-967d-48b8-898d-8b3d68b87e17'),
					array('key' => 'TKL', 'name' => 'Tokelau', 'active' => 1, 'guid' => '0d449d29-9a54-4a37-b07e-e3ec3a5a2aae'),
					array('key' => 'TON', 'name' => 'Tonga', 'active' => 1, 'guid' => 'e74574b0-444d-449a-9a71-e9d4ba8001ba'),
					array('key' => 'TTO', 'name' => 'Trinidad and Tobago', 'active' => 1, 'guid' => 'dc28ddf8-9745-477f-a256-9d6a349bdfc6'),
					array('key' => 'TUN', 'name' => 'Tunisia', 'active' => 1, 'guid' => '8cbeb09a-6156-4283-a8ac-bf4e1558bc7a'),
					array('key' => 'TUR', 'name' => 'Turkey', 'active' => 1, 'guid' => 'c493dfd8-546f-4730-b21f-5aa677cd9a31'),
					array('key' => 'TKM', 'name' => 'Turkmenistan', 'active' => 1, 'guid' => 'af2a3b89-0b70-4e6d-9891-c8cdceedd901'),
					array('key' => 'TCA', 'name' => 'Turks and Caicos Islands', 'active' => 1, 'guid' => '248773c9-afd0-4ce0-99c1-288e6aa1704b'),
					array('key' => 'TUV', 'name' => 'Tuvalu', 'active' => 1, 'guid' => 'bd8e43f4-e95e-4434-91fa-6893f6ddfe88'),
					array('key' => 'UGA', 'name' => 'Uganda', 'active' => 1, 'guid' => '26762ca6-a3fb-49c0-8d8e-c3eb7cf9b6ca'),
					array('key' => 'UKR', 'name' => 'Ukraine', 'active' => 1, 'guid' => '75a96410-804b-4f86-bed1-81fecbbcffa4'),
					array('key' => 'ARE', 'name' => 'United Arab Emirates', 'active' => 1, 'guid' => '0ff7cf53-0e2e-4ef9-9c57-74c53aacc0e4'),
					array('key' => 'GBR', 'name' => 'United Kingdom', 'active' => 1, 'guid' => '74534910-11d2-4baa-b18e-bc8bf955efb7'),
					array('key' => 'USA', 'name' => 'United States', 'active' => 1, 'guid' => 'abfb9a5e-4aec-4427-9aa2-32024ccf45fb'),
					array('key' => 'UMI', 'name' => 'United States Minor Outlying Islands', 'active' => 1, 'guid' => '0e34b404-9c95-4b11-ba82-90f41cdd8879'),
					array('key' => 'URY', 'name' => 'Uruguay', 'active' => 1, 'guid' => '412eb4f6-f245-45f2-be4a-bc31ba5d3286'),
					array('key' => 'UZB', 'name' => 'Uzbekistan', 'active' => 1, 'guid' => 'd6ba8567-72d2-4ced-9d33-8c21b588db30'),
					array('key' => 'VUT', 'name' => 'Vanuatu', 'active' => 1, 'guid' => '3bc4061d-4e57-4ca1-9382-5aaa041dc8e4'),
					array('key' => 'VEN', 'name' => 'Venezuela', 'active' => 1, 'guid' => '231b6ac9-50ef-460f-a39c-b97b4163e06a'),
					array('key' => 'VNM', 'name' => 'Viet Nam', 'active' => 1, 'guid' => '313dc28f-d2d7-454f-a6d7-5cc4d62c7cc1'),
					array('key' => 'VGB', 'name' => 'Virgin Islands, British', 'active' => 1, 'guid' => '680d1aec-5468-4f43-a339-a5553b4b7226'),
					array('key' => 'VIR', 'name' => 'Virgin Islands, U.s.', 'active' => 1, 'guid' => '8f4c1448-ff3b-4424-aa6e-c455c65d330c'),
					array('key' => 'WLF', 'name' => 'Wallis and Futuna', 'active' => 1, 'guid' => '2a0d077f-3cec-4e43-bf5a-aa5733046948'),
					array('key' => 'ESH', 'name' => 'Western Sahara', 'active' => 1, 'guid' => 'd41a099a-2254-44b2-b927-be095d4081fe'),
					array('key' => 'YEM', 'name' => 'Yemen', 'active' => 1, 'guid' => 'e6d71581-c8b0-4963-a8da-7358f0f99844'),
					array('key' => 'ZMB', 'name' => 'Zambia', 'active' => 1, 'guid' => 'b67897b3-759d-414c-ad14-3b85f8def51c'),
					array('key' => 'ZWE', 'name' => 'Zimbabwe', 'active' => 1, 'guid' => '8ad65878-a163-497f-b76b-5f60eba8f703'),
				)),
				'states' => array('key' => 'STATES', 'name' => 'States', 'active' => 1, 'guid' => '0c73c914-fa06-49e2-bee4-bfc401c7eb7d', 'data' => array(
					array('key' => 'AA', 'name' => 'Armed Forces Americas (except Canada)', 'active' => 1, 'guid' => 'ecd227a6-1638-4f07-90f8-1929a9a6003e'),
					array('key' => 'AE', 'name' => 'Armed Forces Africa', 'active' => 1, 'guid' => '1df59871-bf1d-47fa-b090-f274d07b15fb'),
					array('key' => 'AE', 'name' => 'Armed Forces Canada', 'active' => 1, 'guid' => '596f01e1-6e06-4eb2-9947-d8b55f0538af'),
					array('key' => 'AE', 'name' => 'Armed Forces Europe', 'active' => 1, 'guid' => '1901a4db-6503-43e7-8e67-b5259fef2287'),
					array('key' => 'AE', 'name' => 'Armed Forces Middle East', 'active' => 1, 'guid' => 'dc2bb09f-3794-49af-a5f1-c1fbf664a9ba'),
					array('key' => 'AK', 'name' => 'Alaska', 'active' => 1, 'guid' => '8555a23f-6ff4-4e25-a865-92b2c5e4d54b'),
					array('key' => 'AL', 'name' => 'Alabama', 'active' => 1, 'guid' => '75c7e26d-9574-42d9-a6cd-5042a8df4a24'),
					array('key' => 'AP', 'name' => 'Armed Forces Pacific', 'active' => 1, 'guid' => 'ca253f55-c68f-4e4d-9433-ff3bbc783492'),
					array('key' => 'AR', 'name' => 'Arkansas', 'active' => 1, 'guid' => '2668d703-fb3a-42e1-8316-196e749643e3'),
					array('key' => 'AS', 'name' => 'American Samoa', 'active' => 1, 'guid' => 'b976a76e-d770-4328-8e5f-8f7572df25ad'),
					array('key' => 'AZ', 'name' => 'Arizona', 'active' => 1, 'guid' => 'dc76211c-ba63-4cc1-adb5-2531048f09ac'),
					array('key' => 'CA', 'name' => 'California', 'active' => 1, 'guid' => '9839ba0b-f83c-4ac1-9074-66de738db898'),
					array('key' => 'CO', 'name' => 'Colorado', 'active' => 1, 'guid' => 'a5b6ba9c-830e-4c1b-857a-c95ac2f6e513'),
					array('key' => 'CT', 'name' => 'Connecticut', 'active' => 1, 'guid' => '7d1d9e6b-44e3-4f34-a1bd-de49226256cc'),
					array('key' => 'DC', 'name' => 'District of Columbia', 'active' => 1, 'guid' => 'ca8143ed-df49-4b46-a945-4648800f708a'),
					array('key' => 'DE', 'name' => 'Delaware', 'active' => 1, 'guid' => '6d1495f2-69ff-4132-bda6-a52832232fef'),
					array('key' => 'FL', 'name' => 'Florida', 'active' => 1, 'guid' => '266c9390-ed5e-4d4d-b5cd-2e8b0c0fe7ef'),
					array('key' => 'FM', 'name' => 'Federated States of Micronesia', 'active' => 1, 'guid' => '27ff4766-e719-45ba-8c7c-740049da927b'),
					array('key' => 'GA', 'name' => 'Georgia', 'active' => 1, 'guid' => '80dcf632-1722-4c61-8ad6-91476d47afe9'),
					array('key' => 'GU', 'name' => 'Guam', 'active' => 1, 'guid' => 'dd85bb58-137d-49d6-9f71-f218c9aaa757'),
					array('key' => 'HI', 'name' => 'Hawaii', 'active' => 1, 'guid' => '2b196db5-9475-47b1-bf60-5d932379e802'),
					array('key' => 'IA', 'name' => 'Iowa', 'active' => 1, 'guid' => '01dff972-d03d-4639-a12f-650870061039'),
					array('key' => 'ID', 'name' => 'Idaho', 'active' => 1, 'guid' => 'bba2640d-8986-4340-904e-c9e35e672a4c'),
					array('key' => 'IL', 'name' => 'Illinois', 'active' => 1, 'guid' => 'a84b0832-f0e7-450b-b7f8-f7ed906e3c2c'),
					array('key' => 'IN', 'name' => 'Indiana', 'active' => 1, 'guid' => '046bcc91-d4f6-4e38-9287-44267342b5dc'),
					array('key' => 'KS', 'name' => 'Kansas', 'active' => 1, 'guid' => 'a3af4c8e-47a1-4ae9-841d-ba85b669896b'),
					array('key' => 'KY', 'name' => 'Kentucky', 'active' => 1, 'guid' => '0e7dea81-34c3-4c11-b7a8-bb489a2f9b6b'),
					array('key' => 'LA', 'name' => 'Louisiana', 'active' => 1, 'guid' => 'c16dafd0-60b3-445b-a28f-ebf4ba1192bc'),
					array('key' => 'MA', 'name' => 'Massachusetts', 'active' => 1, 'guid' => '59108687-e09b-4091-9b26-9cbbee31c4d7'),
					array('key' => 'MD', 'name' => 'Maryland', 'active' => 1, 'guid' => '6f66ee30-d42d-4d1c-b7a2-e3facfd3c022'),
					array('key' => 'ME', 'name' => 'Maine', 'active' => 1, 'guid' => 'c707581f-50b5-4c58-bf8d-4635d4bbdae8'),
					array('key' => 'MH', 'name' => 'Marshall Islands', 'active' => 1, 'guid' => '4ff754cc-edf0-4320-aaec-29baa45fe427'),
					array('key' => 'MI', 'name' => 'Michigan', 'active' => 1, 'guid' => '189f978f-77d0-41c5-a839-00f1aa400eb2'),
					array('key' => 'MN', 'name' => 'Minnesota', 'active' => 1, 'guid' => '8d0895cf-eb35-42dd-86fa-06c2cb560857'),
					array('key' => 'MO', 'name' => 'Missouri', 'active' => 1, 'guid' => 'a7f06fe9-b508-407e-b0bb-12c00981118d'),
					array('key' => 'MP', 'name' => 'Northern Mariana Islands', 'active' => 1, 'guid' => '4335fbf2-1fef-4eaa-b512-fc93b00193fa'),
					array('key' => 'MS', 'name' => 'Mississippi', 'active' => 1, 'guid' => 'cc0c52a8-0058-45ec-98c4-9294a5b779b5'),
					array('key' => 'MT', 'name' => 'Montana', 'active' => 1, 'guid' => '6bf42f31-e470-4e24-af1c-40c957ca5994'),
					array('key' => 'NC', 'name' => 'North Carolina', 'active' => 1, 'guid' => '030f318b-fb1a-470e-962e-11ca14ece4c7'),
					array('key' => 'ND', 'name' => 'North Dakota', 'active' => 1, 'guid' => '190c7ab6-4611-4b6b-a49f-6088a62c7f6b'),
					array('key' => 'NE', 'name' => 'Nebraska', 'active' => 1, 'guid' => '74d0f5da-442b-499e-bd5d-3579bb6cab4a'),
					array('key' => 'NH', 'name' => 'New Hampshire', 'active' => 1, 'guid' => '1ec4a525-2db9-484f-81c1-fa46c9b8260d'),
					array('key' => 'NJ', 'name' => 'New Jersey', 'active' => 1, 'guid' => '7a692a3a-c4fd-4160-8a74-24648d6f678f'),
					array('key' => 'NM', 'name' => 'New Mexico', 'active' => 1, 'guid' => '018a67b4-dbbd-4479-9b8f-af519ce60cd8'),
					array('key' => 'NV', 'name' => 'Nevada', 'active' => 1, 'guid' => '0b099cdb-3609-4752-9172-5dd79c770228'),
					array('key' => 'NY', 'name' => 'New York', 'active' => 1, 'guid' => 'e537e863-d78c-45d4-89dc-d1cde90dac62'),
					array('key' => 'OH', 'name' => 'Ohio', 'active' => 1, 'guid' => '8de11b34-a77e-41a0-8bc1-aff9acb902fc'),
					array('key' => 'OK', 'name' => 'Oklahoma', 'active' => 1, 'guid' => '4dbf9dec-8ccd-4933-832d-80e3274b161b'),
					array('key' => 'OR', 'name' => 'Oregon', 'active' => 1, 'guid' => '521bc7c8-e43c-4fd1-bd81-ef06ae12e27d'),
					array('key' => 'PA', 'name' => 'Pennsylvania', 'active' => 1, 'guid' => '9807feba-1e90-484d-b98c-f33c14065ce4'),
					array('key' => 'PR', 'name' => 'Puerto Rico', 'active' => 1, 'guid' => 'b957391c-3aba-49a8-b938-2f1f350ec1ff'),
					array('key' => 'PW', 'name' => 'Palau', 'active' => 1, 'guid' => '34734f7e-8ef3-44af-ae9c-ee9480aa06d3'),
					array('key' => 'RI', 'name' => 'Rhode Island', 'active' => 1, 'guid' => '2c9354c3-0956-4e1e-b83e-0008bdc19c14'),
					array('key' => 'SC', 'name' => 'South Carolina', 'active' => 1, 'guid' => '9960b0f7-e10b-4f5d-8f64-80d3f31a53b1'),
					array('key' => 'SD', 'name' => 'South Dakota', 'active' => 1, 'guid' => '23a1960b-146e-473b-8167-886c5fa5850b'),
					array('key' => 'TN', 'name' => 'Tennessee', 'active' => 1, 'guid' => '524b08fa-c3a1-473a-8b00-043b7c5915bc'),
					array('key' => 'TX', 'name' => 'Texas', 'active' => 1, 'guid' => '6b38c039-62a2-4adf-86ce-f4f58e4f900e'),
					array('key' => 'UT', 'name' => 'Utah', 'active' => 1, 'guid' => 'dbd7a7d0-15e5-4f71-9762-679753d03733'),
					array('key' => 'VA', 'name' => 'Virginia', 'active' => 1, 'guid' => '069dd1ad-fa70-4828-944b-9a257ad0ed5c'),
					array('key' => 'VI', 'name' => 'Virgin Islands', 'active' => 1, 'guid' => 'a0e6f9eb-2183-47b0-b14a-861bf4b87027'),
					array('key' => 'VT', 'name' => 'Vermont', 'active' => 1, 'guid' => 'efbae062-3be8-4b45-807c-92e7ee39631b'),
					array('key' => 'WA', 'name' => 'Washington', 'active' => 1, 'guid' => '2ac1cb96-c23d-43f5-b984-c4d340a06ded'),
					array('key' => 'WI', 'name' => 'Wisconsin', 'active' => 1, 'guid' => '8aae244a-c391-4cb3-9d14-0624edb061e6'),
					array('key' => 'WV', 'name' => 'West Virginia', 'active' => 1, 'guid' => '6232af34-b958-401b-b304-37c3972db138'),
					array('key' => 'WY', 'name' => 'Wyoming', 'active' => 1, 'guid' => '8b8a26bf-94e7-494e-a4c0-a3246c85f080'),
				)),
			);

			// top level
			$level = array();
			$level['guid'] = '4d9de535-d18d-4948-a714-787b848524fa';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array();
			$data[] = $level;

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateCalendarPreferencesEnum($force = true) {
		$ret = false;
		do {
			$name = 'Calendar';
			$key = 'CALENDAR';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'appointment' => array('key' => 'APP_REASON', 'name' => AppointmentTemplate::ENUM_PARENT_NAME, 'active' => 1, 'guid' => 'cb4390f6-a334-4dbd-9edc-5637265b776b', 'data' => array(
					'provider' => array('key' => 'PROVIDER', 'name' => 'Provider', 'active' => 1, 'guid' => '82084f77-65a1-466a-b5f8-63e3eb38af9a'),
					'specialist' => array('key' => 'SPECIALIST', 'name' => 'Specialist', 'active' => 1, 'guid' => 'ca0e5f81-7105-4250-abed-8ee45c51b5e3'),
					'medicalPhone' => array('key' => 'MEDPHONE', 'name' => 'Medical Phone', 'active' => 1, 'guid' => '8dd85952-3be8-4b7e-b153-01678f8b571f'),
					'medicalPU' => array('key' => 'MEDPU', 'name' => 'Medication PU', 'active' => 1, 'guid' => '27cf00da-f8c0-4859-9205-63b9e056edf9'),
					'education' => array('key' => 'EDUCATION', 'name' => 'Education', 'active' => 1, 'guid' => '23190974-896c-4dfa-b6db-3a8072aa6ca0'),
					'eligibility' => array('key' => 'ELIG', 'name' => 'Eligibility', 'active' => 1, 'guid' => 'b9c4fb2f-5ddd-48e1-b733-44f7be127069'),
					'blockedTime' => array('key' => 'BLOCKTIME', 'name' => 'Blocked Time', 'active' => 1, 'guid' => '7d6486a3-9655-44a3-b5ed-ad95da0cea7c'),
				)),
			);

			$appointmentTemplate = new AppointmentTemplate();
			foreach ($enums['appointment']['data'] as $k=>$item) {
				$appointmentTemplate->appointmentTemplateId = 0;
				$appointmentTemplate->name = $item['name'];
				$appointmentTemplate->persist();

				$enums['appointment']['data'][$k]['ormClass'] = 'AppointmentTemplate';
				$enums['appointment']['data'][$k]['ormEditMethod'] = 'ormEditMethod';
				$enums['appointment']['data'][$k]['ormId'] = $appointmentTemplate->appointmentTemplateId;
			}

			// top level
			$level = array();
			$level['guid'] = 'e46d5343-18de-459a-9fa4-0dc46ab0c41c';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateClinicalPreferencesEnum($force = true) {
		$ret = false;
		do {
			$name = 'Clinical';
			$key = 'CLINICAL';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'allergies' => array('key' => 'ALLERGIES', 'name' => 'Allergies', 'active' => 1, 'guid' => 'a08abbd2-1a70-491a-9f3f-eb6b41146872', 'data' => array(
					'symptom' => array('key' => 'SYMPTOM', 'name' => PatientAllergy::ENUM_SYMPTOM_PARENT_NAME, 'active' => 1, 'guid' => '5e4efa33-5d27-4caf-bc1c-b36d533fd97f', 'data' => array(
						array('key' => 'AGITATION', 'name' => 'AGITATION', 'active' => 1, 'guid' => 'e33e04fa-7159-4342-b332-0be5f01d46c1'),
						array('key' => 'AGRANULOC', 'name' => 'AGRANULOCYTOSIS', 'active' => 1, 'guid' => 'd3387a01-e7df-4005-957b-91461e128cfa'),
						array('key' => 'ALOPECIA', 'name' => 'ALOPECIA', 'active' => 1, 'guid' => '51d13556-27d8-47cd-bc77-573ca5a14f52'),
						array('key' => 'ANAPHYL', 'name' => 'ANAPHYLAXIS', 'active' => 1, 'guid' => '9769b199-6b56-4260-8d14-5d6c91b81751'),
						array('key' => 'ANEMIA', 'name' => 'ANEMIA', 'active' => 1, 'guid' => '63328e89-432a-473e-8715-097741317be4'),
						array('key' => 'ANOREXIA', 'name' => 'ANOREXIA', 'active' => 1, 'guid' => 'a683f225-1fe1-4281-8268-554e4cd3d7c4'),
						array('key' => 'ANXIETY', 'name' => 'ANXIETY', 'active' => 1, 'guid' => 'c54a24d2-d7db-4872-b569-cc9b528188a3'),
						array('key' => 'APNEA', 'name' => 'APNEA', 'active' => 1, 'guid' => 'edb5a487-da23-4341-99b3-8d403d1f7567'),
						array('key' => 'APPETITE', 'name' => 'APPETITE,INCREASED', 'active' => 1, 'guid' => '4e406011-d648-4b4f-ae91-ec0866f7f016'),
						array('key' => 'ARRHYTHMIA', 'name' => 'ARRHYTHMIA', 'active' => 1, 'guid' => '3c7ade80-e180-4051-a528-256f1c8d7a5b'),
						array('key' => 'ASTHENIA', 'name' => 'ASTHENIA', 'active' => 1, 'guid' => 'a677e528-bafa-494b-a842-78845b535ebc'),
						array('key' => 'ASTHMA', 'name' => 'ASTHMA', 'active' => 1, 'guid' => '5d61c25a-2189-4404-ae15-ba123106de6c'),
						array('key' => 'ATAXIA', 'name' => 'ATAXIA', 'active' => 1, 'guid' => '358661be-39bd-4927-8d1a-50b0cf4ffef8'),
						array('key' => 'ATHETOSIS', 'name' => 'ATHETOSIS', 'active' => 1, 'guid' => '49de7b67-fe12-4833-91aa-ea9f0d285f6a'),
						array('key' => 'BRACHY', 'name' => 'BRACHYCARDIA', 'active' => 1, 'guid' => 'f0f021e0-cc62-4270-88da-1f0838a1b266'),
						array('key' => 'BREASTENG', 'name' => 'BREAST ENGORGEMENT', 'active' => 1, 'guid' => '27bc1849-a51d-4495-bda6-4b0f8e39b0be'),
						array('key' => 'BRONCHO', 'name' => 'BRONCHOSPASM', 'active' => 1, 'guid' => 'd0b04376-b5d7-4fab-b88e-c0579ec33d39'),
						array('key' => 'CARDIAC', 'name' => 'CARDIAC ARREST', 'active' => 1, 'guid' => '558ff42b-7407-4c14-a289-cc1a5d66b5e0'),
						array('key' => 'CHESTPAIN', 'name' => 'CHEST PAIN', 'active' => 1, 'guid' => '81448a0f-8f49-4481-a052-389187667f54'),
						array('key' => 'CHILLS', 'name' => 'CHILLS', 'active' => 1, 'guid' => '85761984-3571-4526-af11-876d93b3f859'),
						array('key' => 'COMA', 'name' => 'COMA', 'active' => 1, 'guid' => 'f75d8aa7-180e-49dc-b8ba-fc37c2a47d2a'),
						array('key' => 'CONFUSION', 'name' => 'CONFUSION', 'active' => 1, 'guid' => 'b9a00422-7457-481d-99a2-55a4e4532234'),
						array('key' => 'CONGESTION', 'name' => 'CONGESTION,NASAL', 'active' => 1, 'guid' => '1cf3a038-6eeb-43c5-b2bf-92647f67c41f'),
						array('key' => 'CONJUNCT', 'name' => 'CONJUNCTIVAL CONGESTION', 'active' => 1, 'guid' => 'e496ab41-8fa7-4b26-a272-4830ad0e7ff9'),
						array('key' => 'CONSTI', 'name' => 'CONSTIPATION', 'active' => 1, 'guid' => '0c633c18-97dd-4978-868e-df31d8a87fd0'),
						array('key' => 'COUGHING', 'name' => 'COUGHING', 'active' => 1, 'guid' => '52f5b451-8619-4428-9b29-914f734a966a'),
						array('key' => 'DEAFNESS', 'name' => 'DEAFNESS', 'active' => 1, 'guid' => 'd869ff05-86b2-4fc8-84ee-b80b6819db46'),
						array('key' => 'DELERIUM', 'name' => 'DELERIUM', 'active' => 1, 'guid' => '7f46d30c-5a55-47bf-a31b-6240c7daabe5'),
						array('key' => 'DELUSION', 'name' => 'DELUSION', 'active' => 1, 'guid' => 'ac5142a5-7afa-4d58-87bf-9c65cb0c88b5'),
						array('key' => 'DEPRESSION', 'name' => 'DEPRESSION', 'active' => 1, 'guid' => '5c96c0fc-22e8-47d3-8150-eed7f3e5488d'),
						array('key' => 'MENTALDEP', 'name' => 'DEPRESSION,MENTAL', 'active' => 1, 'guid' => 'd3e79ee1-bd45-4b52-931b-95dd20f92a50'),
						array('key' => 'POSTICTAL', 'name' => 'DEPRESSION,POSTICTAL', 'active' => 1, 'guid' => 'a44de67a-0687-479a-a86a-387a779c4607'),
						array('key' => 'DERMATITIS', 'name' => 'DERMATITIS', 'active' => 1, 'guid' => '68663a80-b468-49de-802a-863b62b1f819'),
						array('key' => 'CONTACTDER', 'name' => 'DERMATITIS,CONTACT', 'active' => 1, 'guid' => '769aae3c-a24e-4b97-8e1f-fe9a3a8d36f0'),
						array('key' => 'PHOTOALLER', 'name' => 'DERMATITIS,PHOTOALLERGENIC', 'active' => 1, 'guid' => '809701ae-1008-4fb2-a73f-e809551e1712'),
						array('key' => 'DIAPHORES', 'name' => 'DIAPHORESIS', 'active' => 1, 'guid' => '3fe196a3-011e-448e-b470-9d1a30ca7bb8'),
						array('key' => 'DIARRHEA', 'name' => 'DIARRHEA', 'active' => 1, 'guid' => 'cb5c9662-b84e-471e-95b9-f86531c8cd53'),
						array('key' => 'DIPLOPIA', 'name' => 'DIPLOPIA', 'active' => 1, 'guid' => '8dbabab1-7cdd-43cd-a540-953509602d74'),
						array('key' => 'DISTURB', 'name' => 'DISTURBED COORDINATION', 'active' => 1, 'guid' => '2319f319-23b4-47e6-9fbb-b0bf29c36d35'),
						array('key' => 'DIZZINESS', 'name' => 'DIZZINESS', 'active' => 1, 'guid' => '28813ec3-07fb-4424-a0e1-f45ec2d1581c'),
						array('key' => 'DREAMING', 'name' => 'DREAMING,INCREASED', 'active' => 1, 'guid' => 'f1af66fc-16b7-4196-9882-5d06d8e255af'),
						array('key' => 'DROWSINESS', 'name' => 'DROWSINESS', 'active' => 1, 'guid' => 'a7af6e1e-55eb-4a02-8259-808f45d8ac27'),
						array('key' => 'DRYMOUTH', 'name' => 'DRY MOUTH', 'active' => 1, 'guid' => 'cf1b8d8d-e807-4d49-94f6-25c8f8e934bd'),
						array('key' => 'DRYNOSE', 'name' => 'DRY NOSE', 'active' => 1, 'guid' => '39fce240-43d2-4f39-a592-5974840a42d9'),
						array('key' => 'DRYTHROAT', 'name' => 'DRY THROAT', 'active' => 1, 'guid' => '37eafc95-454a-4aa4-81be-cfcb619123d0'),
						array('key' => 'DYSPNEA', 'name' => 'DYSPNEA', 'active' => 1, 'guid' => 'f105129e-7eeb-4709-9c86-ba3512607c03'),
						array('key' => 'DYSURIA', 'name' => 'DYSURIA', 'active' => 1, 'guid' => 'b0622d48-035d-49a5-8c36-38df17438500'),
						array('key' => 'ECCHYMOSIS', 'name' => 'ECCHYMOSIS', 'active' => 1, 'guid' => '49d2dce3-0fd8-4a20-8811-e0f7bc4489dc'),
						array('key' => 'ECGCHANGES', 'name' => 'ECG CHANGES', 'active' => 1, 'guid' => '58bf888e-31d6-4112-989e-c32ed80caf47'),
						array('key' => 'ECZEMA', 'name' => 'ECZEMA', 'active' => 1, 'guid' => '9ea40421-96be-4017-ab4a-ee63fe278259'),
						array('key' => 'EDEMA', 'name' => 'EDEMA', 'active' => 1, 'guid' => '2773e96e-0446-44be-98ec-2d81ab3f2802'),
						array('key' => 'EPIGASTRIC', 'name' => 'EPIGASTRIC DISTRESS', 'active' => 1, 'guid' => 'a41a3909-0c33-4e6a-866e-3100a6142f5f'),
						array('key' => 'EPISTAXIS', 'name' => 'EPISTAXIS', 'active' => 1, 'guid' => '7efbc6f5-e593-4642-92b6-6e7ed8c5eb32'),
						array('key' => 'ERYTHEMA', 'name' => 'ERYTHEMA', 'active' => 1, 'guid' => 'c6ff3e26-9f58-4adc-bf94-519563a22f37'),
						array('key' => 'EUPHORIA', 'name' => 'EUPHORIA', 'active' => 1, 'guid' => 'a73e1a19-e8a7-441a-b74d-712f44276b6b'),
						array('key' => 'EXCITATION', 'name' => 'EXCITATION', 'active' => 1, 'guid' => '9e02208f-e9c2-4983-8742-832c15fe9d20'),
						array('key' => 'EXTRASYS', 'name' => 'EXTRASYSTOLE', 'active' => 1, 'guid' => '9d089876-ce86-4665-8002-6ca5194e1d6b'),
						array('key' => 'FACEFLUSH', 'name' => 'FACE FLUSHED', 'active' => 1, 'guid' => '5655c678-3517-4e5a-bebe-0165c38912fb'),
						array('key' => 'DYSKINESIA', 'name' => 'FACIAL DYSKINESIA', 'active' => 1, 'guid' => 'df06eec5-05b7-4ecd-9197-1bbd42c78e60'),
						array('key' => 'FAINTNESS', 'name' => 'FAINTNESS', 'active' => 1, 'guid' => 'a59a0e2f-6eea-4620-98e8-68a5590d0c2c'),
						array('key' => 'FATIGUE', 'name' => 'FATIGUE', 'active' => 1, 'guid' => 'ad32805f-14db-43b1-8cf2-d0408901eea1'),
						array('key' => 'FEELWARMTH', 'name' => 'FEELING OF WARMTH', 'active' => 1, 'guid' => 'ad33e3c9-4e77-451f-9a6f-c923d92701a6'),
						array('key' => 'FEVER', 'name' => 'FEVER', 'active' => 1, 'guid' => '5015b71e-f9d9-48af-a79e-b0e36b682d40'),
						array('key' => 'GALACTOR', 'name' => 'GALACTORRHEA', 'active' => 1, 'guid' => 'e0c02fb1-7cf2-42a6-8fca-da40c3e9510a'),
						array('key' => 'GENRASH', 'name' => 'GENERALIZED RASH', 'active' => 1, 'guid' => '6eae9369-e651-4235-9ee6-c5e2bd1f0390'),
						array('key' => 'GIREACTION', 'name' => 'GI REACTION', 'active' => 1, 'guid' => '68f1223d-1fb8-4d3e-8e0d-7bc264257119'),
						array('key' => 'GLAUCOMA', 'name' => 'GLAUCOMA', 'active' => 1, 'guid' => '0566d19c-623a-44f4-aa13-aa75531c6cb4'),
						array('key' => 'GYNECOMA', 'name' => 'GYNECOMASTIA', 'active' => 1, 'guid' => '0bb3456c-4efd-41d6-825b-927709ce43eb'),
						array('key' => 'HALLUCIN', 'name' => 'HALLUCINATIONS', 'active' => 1, 'guid' => '4425c166-488f-43b3-8c53-028ae00b5d68'),
						array('key' => 'HEADACHE', 'name' => 'HEADACHE', 'active' => 1, 'guid' => '1dc774b9-8a6e-4ad4-9120-357145d1e6fa'),
						array('key' => 'HEARTBLOCK', 'name' => 'HEART BLOCK', 'active' => 1, 'guid' => 'b58e5f18-81cf-4ac1-b0db-27f98b984c9b'),
						array('key' => 'HEMATURIA', 'name' => 'HEMATURIA', 'active' => 1, 'guid' => '4c7c0865-09fa-474f-8af4-ccdf7318e8c8'),
						array('key' => 'HEMOGLOBIN', 'name' => 'HEMOGLOBIN,INCREASED', 'active' => 1, 'guid' => 'a3f6c207-37fd-4a8c-ae49-93561ecdb365'),
						array('key' => 'HIVES', 'name' => 'HIVES', 'active' => 1, 'guid' => '85ab9163-b6d3-4397-9503-7e6c60c1c8bb'),
						array('key' => 'HYPERSENSE', 'name' => 'HYPERSENSITIVITY', 'active' => 1, 'guid' => 'b3c7d804-9cd3-4f78-b199-6b6a2051c5d0'),
						array('key' => 'HYPERTENSE', 'name' => 'HYPERTENSION', 'active' => 1, 'guid' => '756e3d6b-3c26-457d-bab3-a0f7627edc81'),
						array('key' => 'HYPOTENSE', 'name' => 'HYPOTENSION', 'active' => 1, 'guid' => '84c4f714-cb12-4ca9-bd0e-20e20ea6abd7'),
						array('key' => 'IMPAIREREC', 'name' => 'IMPAIRMENT OF ERECTION', 'active' => 1, 'guid' => '3c14cb5b-6b83-4b22-95ac-5146fc68f8f3'),
						array('key' => 'IMPOTENCE', 'name' => 'IMPOTENCE', 'active' => 1, 'guid' => '812c866b-72b9-41d7-87cf-89f2f6f3f771'),
						array('key' => 'PENILEEREC', 'name' => 'INAPPROPRIATE PENILE ERECTION', 'active' => 1, 'guid' => '42617f7b-7d58-42e4-a468-760dec0de23e'),
						array('key' => 'INSOMNIA', 'name' => 'INSOMNIA', 'active' => 1, 'guid' => '29113a01-7fd9-4e2e-8ccc-1174cd8cfc7f'),
						array('key' => 'IRRITABILI', 'name' => 'IRRITABILITY', 'active' => 1, 'guid' => '7283ee74-6c04-4ae9-9894-509b4580aba5'),
						array('key' => 'ITCHING', 'name' => 'ITCHING,WATERING EYES', 'active' => 1, 'guid' => '58d79e15-f311-494a-9c94-db2fbdf06fcb'),
						array('key' => 'JUNCRHYTHM', 'name' => 'JUNCTIONAL RHYTHM', 'active' => 1, 'guid' => '695d132a-5e28-4ac9-bd6b-b8a58944c1cf'),
						array('key' => 'LABYRINTH', 'name' => 'LABYRINTHITIS,ACUTE', 'active' => 1, 'guid' => '67bdf8d5-b89b-4b57-aee6-e2747cd5305a'),
						array('key' => 'LACRIM', 'name' => 'LACRIMATION', 'active' => 1, 'guid' => '8fe4aa68-7ee3-4436-a026-cf420a54a11a'),
						array('key' => 'LDHINC', 'name' => 'LDH,INCREASED', 'active' => 1, 'guid' => '151165f8-c26f-4219-861b-79f2cb483a2d'),
						array('key' => 'LETHARGY', 'name' => 'LETHARGY', 'active' => 1, 'guid' => '3846f391-95b1-4e05-a0bc-838e0f08d5ef'),
						array('key' => 'LEUKOCYTE', 'name' => 'LEUKOCYTE COUNT,DECREASED', 'active' => 1, 'guid' => '6dc47ca3-8b0f-496a-af74-705e730ac6d1'),
						array('key' => 'LIBIDODEC', 'name' => 'LIBIDO,DECREASED', 'active' => 1, 'guid' => '87b11bb0-8aef-4670-a820-b0268e3b5a4f'),
						array('key' => 'LIBIDOINC', 'name' => 'LIBIDO,INCREASED', 'active' => 1, 'guid' => '45bf23a2-3fab-401c-b8b1-eddc2a0a74c5'),
						array('key' => 'MIOSIS', 'name' => 'MIOSIS', 'active' => 1, 'guid' => '6b223e21-45be-4f9c-91cc-931451325c28'),
						array('key' => 'MYOCARDIAL', 'name' => 'MYOCARDIAL INFARCTION', 'active' => 1, 'guid' => 'd33e3402-74a5-4e08-b63b-02d5670ef305'),
						array('key' => 'NAUSEA', 'name' => 'NAUSEA,VOMITING', 'active' => 1, 'guid' => '26b4f217-8f69-4198-926f-4334a7e5429d'),
						array('key' => 'NERVOUSNES', 'name' => 'NERVOUSNESS,AGITATION', 'active' => 1, 'guid' => '325c2470-d076-456f-ac42-8b5d64a48abd'),
						array('key' => 'NEUTROPHIL', 'name' => 'NEUTROPHIL COUNT,DECREASED', 'active' => 1, 'guid' => 'd593f1b6-db2d-42bf-b6ca-98a81cb7e0ae'),
						array('key' => 'NIGHTMARES', 'name' => 'NIGHTMARES', 'active' => 1, 'guid' => 'baf19a22-fa18-4bd0-aeef-a11d6acdba9b'),
						array('key' => 'OPTICATROP', 'name' => 'OPTIC ATROPHY', 'active' => 1, 'guid' => 'ab0eb531-74be-4698-87d4-0eb2914816b7'),
						array('key' => 'ORGASMINH', 'name' => 'ORGASM,INHIBITED', 'active' => 1, 'guid' => '83f28d1e-ef4a-4509-b646-80e6aa6c1cb5'),
						array('key' => 'ORONASAL', 'name' => 'ORONASALPHARYNGEAL IRRITATION', 'active' => 1, 'guid' => '1a71f784-3d5e-4b2d-8d42-9fc972dfc0bd'),
						array('key' => 'PAINJOINT', 'name' => 'PAIN,JOINT', 'active' => 1, 'guid' => '5f94d154-de53-4e7c-a818-daf3c41aad65'),
						array('key' => 'PALPITATE', 'name' => 'PALPITATIONS', 'active' => 1, 'guid' => 'b54e506b-eef9-46d5-9b66-07681d2c3226'),
						array('key' => 'PANCYTOPEN', 'name' => 'PANCYTOPENIA', 'active' => 1, 'guid' => 'a57ec518-088b-43ca-b7b3-894cc5cabb26'),
						array('key' => 'PARESTHES', 'name' => 'PARESTHESIA', 'active' => 1, 'guid' => '55442aad-f868-4b30-b5cf-20138982cb34'),
						array('key' => 'PARKINSON', 'name' => 'PARKINSONIAN-LIKE SYNDROME', 'active' => 1, 'guid' => '6d63be7a-6a2a-48f7-a448-8eb9ee5376c7'),
						array('key' => 'PHOTOSEN', 'name' => 'PHOTOSENSITIVITY', 'active' => 1, 'guid' => '41c6a3c2-b75d-4d65-a608-f1487f0e77ac'),
						array('key' => 'POSSREACT', 'name' => 'POSSIBLE REACTION', 'active' => 1, 'guid' => 'bae64648-7109-44fd-94d6-0a817fbe51fa'),
						array('key' => 'PRIAPISM', 'name' => 'PRIAPISM', 'active' => 1, 'guid' => 'c98aa887-91c4-4645-8ee6-da4490150ca6'),
						array('key' => 'PROPENEREC', 'name' => 'PROLONGED PENILE ERECTION', 'active' => 1, 'guid' => '34c5a1e8-6102-4dc3-ab95-c85e70dd2e8d'),
						array('key' => 'PRURITIS', 'name' => 'PRURITIS', 'active' => 1, 'guid' => '6047faba-2911-421b-b548-98da61087dfc'),
						array('key' => 'PTOSIS', 'name' => 'PTOSIS', 'active' => 1, 'guid' => '4448ea08-7985-4297-a030-875424dd8644'),
						array('key' => 'PURPURA', 'name' => 'PURPURA', 'active' => 1, 'guid' => 'd3155510-fa2f-4bab-b3aa-95a12a44cb9f'),
						array('key' => 'RALES', 'name' => 'RALES', 'active' => 1, 'guid' => 'e3af50e8-1c31-45d0-a89d-d50713225d03'),
						array('key' => 'RASH', 'name' => 'RASH', 'active' => 1, 'guid' => 'a6152b88-73cf-4f77-827c-f2b2f256a872'),
						array('key' => 'RASHPAPULA', 'name' => 'RASH,PAPULAR', 'active' => 1, 'guid' => '2e9fbb64-4076-4fc7-8a5a-a8323223289f'),
						array('key' => 'RESPIDIST', 'name' => 'RESPIRATORY DISTRESS', 'active' => 1, 'guid' => 'b82f1ffd-39e8-4840-afe3-979cc0a96b67'),
						array('key' => 'RETROEJAC', 'name' => 'RETROGRADE EJACULATION', 'active' => 1, 'guid' => 'b1aa64f5-35a0-4de6-a6de-35ce539ab9ec'),
						array('key' => 'RHINITIS', 'name' => 'RHINITIS', 'active' => 1, 'guid' => '569b9907-477d-4728-b46d-ff2ca536baf7'),
						array('key' => 'RHINORRHEA', 'name' => 'RHINORRHEA', 'active' => 1, 'guid' => 'b21d97a6-61fa-4dbd-bc46-923419669f2a'),
						array('key' => 'RHONCHUS', 'name' => 'RHONCHUS', 'active' => 1, 'guid' => 'db6905e6-717b-4385-9d41-aad07765aef2'),
						array('key' => 'STCHANGES', 'name' => 'S-T CHANGES,TRANSIENT', 'active' => 1, 'guid' => 'a74931ab-0eeb-456f-84ae-1e6561e4c63a'),
						array('key' => 'SEIZURES', 'name' => 'SEIZURES', 'active' => 1, 'guid' => 'd7928c4f-a009-4998-8335-61b3ff1882a5'),
						array('key' => 'SEIZURESTC', 'name' => 'SEIZURES,TONIC-CLONIC', 'active' => 1, 'guid' => '36831f0f-7cda-4431-8c0c-e7ff7ccd4f2f'),
						array('key' => 'SELFDEPRE', 'name' => 'SELF-DEPRECATION', 'active' => 1, 'guid' => '77ed1474-f5b0-4554-a944-94244b457e73'),
						array('key' => 'SEVERERASH', 'name' => 'SEVERE RASH', 'active' => 1, 'guid' => 'a4e56fe2-a0ec-43dc-b338-203c780692fd'),
						array('key' => 'SHORTBREAT', 'name' => 'SHORTNESS OF BREATH', 'active' => 1, 'guid' => '375c6a4a-855e-4d05-a3bc-2de663964fa8'),
						array('key' => 'SINUS', 'name' => 'SINUS BRACHYCARDIA', 'active' => 1, 'guid' => 'bc05bb94-1022-4fea-8bed-786191857ec3'),
						array('key' => 'SNEEZING', 'name' => 'SNEEZING', 'active' => 1, 'guid' => '126cd5ea-417c-4a98-a44a-96bf1a1ce25d'),
						array('key' => 'SOMNOLENCE', 'name' => 'SOMNOLENCE', 'active' => 1, 'guid' => '13ac9593-7f11-478b-896e-02a40c2b81bc'),
						array('key' => 'SPEECHDIS', 'name' => 'SPEECH DISORDER', 'active' => 1, 'guid' => '40149a1a-cf03-4233-ad05-aefc3db129cf'),
						array('key' => 'SWELLING', 'name' => 'SWELLING (NON-SPECIFIC)', 'active' => 1, 'guid' => 'c3407d51-2e0d-41c2-81cd-050b6029fb81'),
						array('key' => 'SWELLEYES', 'name' => 'SWELLING-EYES', 'active' => 1, 'guid' => '302d54c6-ab23-4e90-99a5-7f101752deff'),
						array('key' => 'SWELLLIPS', 'name' => 'SWELLING-LIPS', 'active' => 1, 'guid' => 'd7cb0fcf-c106-4047-beae-762a522f226f'),
						array('key' => 'SWELLTHRO', 'name' => 'SWELLING-THROAT', 'active' => 1, 'guid' => '464c43b9-a092-45c8-b814-da92a11e3280'),
						array('key' => 'SYNCOPE', 'name' => 'SYNCOPE', 'active' => 1, 'guid' => '98d1d8b5-eb00-4311-95d6-914eea5cfbe9'),
						array('key' => 'TACHYCARD', 'name' => 'TACHYCARDIA', 'active' => 1, 'guid' => '64693c8d-19e4-44d8-a1ed-0cfcadb7f450'),
						array('key' => 'THROMBOCYT', 'name' => 'THROMBOCYTOPENIA', 'active' => 1, 'guid' => 'f2ce7236-2dae-4616-a808-47bbf999cb48'),
						array('key' => 'TREMORS', 'name' => 'TREMORS', 'active' => 1, 'guid' => '2c72d4d9-770a-473b-bd00-ae480dfc1ef2'),
						array('key' => 'URINARYFLO', 'name' => 'URINARY FLOW,DELAYED', 'active' => 1, 'guid' => 'bc898ac0-ed29-434d-83b8-9ac1ca948af1'),
						array('key' => 'URINARYFRE', 'name' => 'URINARY FREQUENCY', 'active' => 1, 'guid' => '6be1ee6a-27ae-4048-a03e-3405ce593b4c'),
						array('key' => 'URINARYFI', 'name' => 'URINARY FREQUENCY,INCREASED', 'active' => 1, 'guid' => 'e758771d-9462-4e2a-acdb-b627ea245f06'),
						array('key' => 'URINARYRET', 'name' => 'URINARY RETENTION', 'active' => 1, 'guid' => '4971fe4b-f4b5-47a3-94c6-a04d7bc85047'),
						array('key' => 'URTICARIA', 'name' => 'URTICARIA', 'active' => 1, 'guid' => '5b9d3c61-6817-484c-8866-1d297ae98703'),
						array('key' => 'UVEITIS', 'name' => 'UVEITIS', 'active' => 1, 'guid' => 'bee7a122-7ab2-4a7b-91c2-ada764d5d38d'),
						array('key' => 'VERTIGO', 'name' => 'VERTIGO', 'active' => 1, 'guid' => '79255840-4814-4992-af89-7d3e74669d8f'),
						array('key' => 'VISIONBLUR', 'name' => 'VISION,BLURRED', 'active' => 1, 'guid' => 'a4afab67-c5be-4c35-bb87-29530b0fe9d5'),
						array('key' => 'VISUALDIST', 'name' => 'VISUAL DISTURBANCES', 'active' => 1, 'guid' => '912f78c0-c5cf-43bf-8633-28118d54c44b'),
						array('key' => 'VOMITING', 'name' => 'VOMITING', 'active' => 1, 'guid' => '40d9207f-dea7-4af3-8566-31cf1011caeb'),
						array('key' => 'WEAKNESS', 'name' => 'WEAKNESS', 'active' => 1, 'guid' => 'af97f76d-5b4b-4571-a6a2-98e0abb7df1c'),
						array('key' => 'WEIGHTGAIN', 'name' => 'WEIGHT GAIN', 'active' => 1, 'guid' => '47b2ac4c-8cc4-4efa-8faa-018c0bdb053c'),
						array('key' => 'WHEEZING', 'name' => 'WHEEZING', 'active' => 1, 'guid' => '15e6c847-4d7c-4fe2-a00c-20f109d28248'),
					)),
					'severity' => array('key' => 'SEVERITY', 'name' => PatientAllergy::ENUM_SEVERITY_PARENT_NAME, 'active' => 1, 'guid' => 'a5b080cb-2bac-4dfe-a87e-4805abb2b353', 'data' => array(
						'mild' => array('key' => 'MILD', 'name' => 'Mild', 'active' => 1, 'guid' => '74cc3b3f-04e8-4252-9f8a-63701d0eb106'),
						'moderate' => array('key' => 'MODERATE', 'name' => 'Moderate', 'active' => 1, 'guid' => '2ef8862e-ecf9-4280-8ee7-118918d5a35c'),
					)),
					'reactionType' => array('key' => 'REACTYPE', 'name' => PatientAllergy::ENUM_REACTION_TYPE_PARENT_NAME, 'active' => 1, 'guid' => '05ba0f9e-dc5d-49bc-972d-fc0b0d5509f6', 'data' => array(
						'allergy' => array('key' => 'ALLERGY', 'name' => 'Allergy', 'active' => 1, 'guid' => '6d0c6924-1a5d-45bf-8b41-6ae21cfdf3b2'),
						'pharma' => array('key' => 'PHARMA', 'name' => 'Pharmacological', 'active' => 1, 'guid' => '2297aad6-43b6-44e2-ae45-bb02743789c8'),
						'unknown' => array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1, 'guid' => '5b211c88-1c9c-4721-a27e-d53acb82c213'),
						'drugClass' => array('key' => 'DRUGCLASS', 'name' => 'Drug Class Allergy', 'active' => 1, 'guid' => '8fa5215c-268b-4a77-be32-9f0479d6e17b'),
						'specDrug' => array('key' => 'SPECDRUG', 'name' => 'Specific Drug Allergy', 'active' => 1, 'guid' => '7ba4f8dd-64d0-432f-8f14-b136c82cc55b'),
					)),
				)),
			);

			$level = array();
			$level['guid'] = '4db7079d-5c31-4f3a-8280-470bd6918329';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateContactPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Contact Preferences';
			$key = 'CONTACT';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'phoneTypes' => array('key' => 'PHONETYPES', 'name' => 'Phone Types', 'active' => 1, 'guid' => 'dd7ee20b-8faa-4518-9120-600ab7e2f331', 'data' => array(
					array('key' => 'HOME', 'name' => 'Home', 'active' => 1, 'guid' => 'f675419e-8e61-422e-917a-80a05cb92270'),
					array('key' => 'WORK', 'name' => 'Work', 'active' => 1, 'guid' => '7a3fc33f-f103-4754-81e3-8e07e19bd10d'),
					array('key' => 'BILL', 'name' => 'Billing', 'active' => 1, 'guid' => '94a1d7c1-e59c-4597-8423-e379f88fd3ef'),
					array('key' => 'MOB', 'name' => 'Mobile', 'active' => 1, 'guid' => 'a2ad6a92-55c5-4506-87e1-b323640dcb26'),
					array('key' => 'EMER', 'name' => 'Emergency', 'active' => 1, 'guid' => '47b2ec07-d740-40d3-b78d-6635a81c8464'),
					array('key' => 'FAX', 'name' => 'Fax', 'active' => 1, 'guid' => 'c3bc4d7d-4d64-42b6-b402-99aafadef65b'),
					array('key' => 'EMPL', 'name' => 'Employer', 'active' => 1, 'guid' => '380575c1-9a42-4c4c-ba7d-9f507fc04d55'),
				)),
				'addrTypes' => array('key' => 'ADDRTYPES', 'name' => 'Address Types', 'active' => 1, 'guid' => 'e0e29c42-abd1-4dae-bc17-3f8b8213d9e7', 'data' => array(
					array('key' => 'HOME', 'name' => 'Home', 'active' => 1, 'guid' => '4f30ace1-b14b-470b-b4a0-edb940a755a0'),
					array('key' => 'EMPL', 'name' => 'Employer', 'active' => 1, 'guid' => '2218c87d-3e16-4dfa-a3e8-872590c692de'),
					array('key' => 'BILL', 'name' => 'Billing', 'active' => 1, 'guid' => '5a77bac0-38ad-43a0-971b-9dc5f3dab18c'),
					array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => '7e46573a-d16a-4d58-9e7d-9bab0d634dba'),
					array('key' => 'MAIN', 'name' => 'Main', 'active' => 1, 'guid' => '69b9f7ca-c17c-4487-954c-efdb0b4e1eb5'),
					array('key' => 'SEC', 'name' => 'Secondary', 'active' => 1, 'guid' => 'fc1fe90f-a0f2-4006-a034-34f070512664'),
				)),
			);

			$level = array();
			$level['guid'] = '5923eb68-bdf9-4556-ab30-87c06f1abde9';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateMenuEnum($force = false) {
		$ret = false;
		do {
			$name = 'Menu';
			$key = 'MENU';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'file' => array('key' => 'FILE', 'name' => 'File', 'active' => 1, 'guid' => '2fa74ea6-27f3-402d-a236-88b0405f0ae6', 'data' => array(
					'addPatient' => array('key' => 'ADDPATIENT', 'name' => 'Add Patient', 'active' => 1, 'guid' => 'eed5c430-b4b2-4f73-9e05-e5a619af711e'),
					'selectPatient' => array('key' => 'SELPATIENT', 'name' => 'Select Patient', 'active' => 1, 'guid' => '56fb669b-e235-4ef2-9068-117f0b9adf8c'),
					'reviewSignChanges' => array('key' => 'RSC', 'name' => 'Review / Sign Changes', 'active' => 1, 'guid' => '8d994911-30f2-42aa-afe0-21740a1b32c2'),
					'changePassword' => array('key' => 'CHANGEPW', 'name' => 'Change Password', 'active' => 1, 'guid' => 'd47b2b1a-916b-40ee-9a99-392e695a3819'),
					'editSigningKey' => array('key' => 'SIGNINGKEY', 'name' => 'Edit Signing Key', 'active' => 1, 'guid' => '8a5f6413-fdac-450f-841e-15ef5aceb2eb'),
					'myPreferences' => array('key' => 'MYPREF', 'name' => 'My Preferences', 'active' => 1, 'guid' => '33a1cd9e-18d2-4e04-86c9-26b189811a01'),
					'quit' => array('key' => 'QUIT', 'name' => 'Quit', 'active' => 1, 'guid' => 'c4bd0198-671b-4dd2-b3ac-dfce2fa31cc1'),
				)),
				'action' => array('key' => 'ACTION', 'name' => 'Action', 'active' => 1, 'guid' => '377126cf-02b9-4697-a65d-7c4236bf55d8', 'data' => array(
					'addVitals' => array('key' => 'ADDVITALS', 'name' => 'Add Vitals', 'active' => 1, 'guid' => '7f04b6ba-9f16-44f6-bc75-85808310dadb'),
					'print' => array('key' => 'PRINT', 'name' => 'Print', 'active' => 1, 'guid' => '90de6df2-2916-4851-8447-9447fcb11c13', 'data' => array(
						'flowSheet' => array('key' => 'FLOWSHEET', 'name' => 'Flow Sheet', 'active' => 1, 'guid' => '9decd97b-8462-4b89-89fc-991f53765e38'),
					)),
					'manageSchedule' => array('key' => 'MANSCHED', 'name' => 'Manage Schedules', 'active' => 1, 'guid' => '78dd7937-c0c6-407f-848f-192ebac2ac86'),
				)),
			);

			$level = array();
			$level['guid'] = '33fb13cb-577f-4a00-8765-b4a5334434c0';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['ormClass'] = 'MenuItem';
			$level['ormEditMethod'] = 'ormEditMethod';
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);

			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);

			$menu = new MenuItem();
			$menu->siteSection = 'All';
			$menu->type = 'freeform';
			$menu->active = 1;
			$menu->title = $enumeration->name;
			$menu->displayOrder = 0;
			$menu->parentId = 0;
			$menu->persist();

			$enumeration->ormId = $menu->menuId;
			$enumeration->persist();
			self::_generateMenuEnumerationTree($enumeration);

			$ret = true;
		} while(false);
		return $ret;
	}

	protected static function _generateMenuEnumerationTree(Enumeration $enumeration) {
		static $enumerationIds = array();
		$enumerationId = $enumeration->enumerationId;
		$enumerationsClosure = new EnumerationsClosure();
		$descendants = $enumerationsClosure->getEnumerationTreeById($enumerationId);
		$displayOrder = 0;
		foreach ($descendants as $enum) {
			if (isset($enumerationIds[$enum->enumerationId])) {
				continue;
			}
			$enumerationIds[$enum->enumerationId] = true;
			$displayOrder += 10;
			$menu = new MenuItem();
			$menu->siteSection = 'All';
			$menu->type = 'freeform';
			$menu->active = 1;
			$menu->title = $enum->name;
			//$menu->displayOrder = $displayOrder;
			$menu->displayOrder = $enum->enumerationId; // temporarily set displayOrder using the enumerationId
			$menu->parentId = $enumerationId;
			$menu->persist();

			$enum->ormId = $menu->menuId;
			$enum->persist();

			if ($enumerationId != $enum->enumerationId) { // prevents infinite loop
				self::_generateMenuEnumerationTree($enum);
			}
		}
	}

	public static function generateImmunizationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientImmunization::ENUM_PARENT_NAME;
			$key = 'IP';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'series' => array('key' => 'series', 'name' => PatientImmunization::ENUM_SERIES_NAME, 'active' => 1, 'guid' => '36f25558-15e0-48a1-beb0-e3f697e79db0', 'data' => array(
					array('key' => 'partComp', 'name' => 'Partially complete', 'active' => 1, 'guid' => '218de21f-8abd-4516-a3e0-264cddbb7cea'),
					array('key' => 'complete', 'name' => 'Complete', 'active' => 1, 'guid' => '4e780c3b-b0ba-4f5d-b808-fafc378077ca'),
					array('key' => 'booster', 'name' => 'Booster', 'active' => 1, 'guid' => '454bddfc-ee88-4508-ba74-55f78b31c786'),
					array('key' => 'series1', 'name' => 'Series 1', 'active' => 1, 'guid' => '77e36a5a-ab90-4f5a-be6e-207d9fc9a67c'),
					array('key' => 'series2', 'name' => 'Series 2', 'active' => 1, 'guid' => 'f42770b1-c660-4886-a8d6-6dad658b78d0'),
					array('key' => 'series3', 'name' => 'Series 3', 'active' => 1, 'guid' => '16497c75-b137-4ab6-a1ae-d489ff90770f'),
					array('key' => 'series4', 'name' => 'Series 4', 'active' => 1, 'guid' => '84a4e29b-3d4c-42a2-b00c-1c25c63b1270'),
					array('key' => 'series5', 'name' => 'Series 5', 'active' => 1, 'guid' => 'a3a8b989-f55a-4195-9aaf-ca3fc2966115'),
					array('key' => 'series6', 'name' => 'Series 6', 'active' => 1, 'guid' => '79804260-160c-42b3-b87c-f58e33a368d3'),
					array('key' => 'series7', 'name' => 'Series 7', 'active' => 1, 'guid' => '63ccf298-e609-48d9-afda-3853e315e867'),
					array('key' => 'series8', 'name' => 'Series 8', 'active' => 1, 'guid' => '86e2e28d-0ec4-4d8c-ba75-16a39236915f'),
				)),
				'section' => array('key' => 'section', 'name' => PatientImmunization::ENUM_SECTION_NAME, 'active' => 1, 'guid' => '17d2351c-ef39-4f8a-9b8a-896b116a5c14', 'data' => array(
					'other' => array('key' => 'other', 'name' => PatientImmunization::ENUM_SECTION_OTHER_NAME, 'active' => 1, 'guid' => '0a212a50-d9f8-412a-8109-3bc981461f3e', 'data' => array(
						array('key' => 'BCG', 'name' => 'BCG', 'active' => 1, 'guid' => '64d6049a-b82d-448e-9cba-a29c5fa2dc5c'),
						array('key' => 'DT', 'name' => 'DT (pediatric)', 'active' => 1, 'guid' => '1f572d3d-9835-43c2-8116-9721af6c7058'),
						array('key' => 'DTAP', 'name' => 'DTaP', 'active' => 1, 'guid' => '7d906e0c-ce02-4753-8e99-d0e5d87591f7'),
						array('key' => 'DTAPHEP', 'name' => 'DTaP-Hep B-IPV', 'active' => 1, 'guid' => '74b271bf-1209-4e4c-889e-722eb4e7ce68'),
						array('key' => 'DTAPHIB', 'name' => 'DTaP-Hib', 'active' => 1, 'guid' => 'ab9f7e5e-2edd-4669-becb-e0144f17608c'),
						array('key' => 'DTAPHIBIPV', 'name' => 'DTaP-Hib-IPV', 'active' => 1, 'guid' => 'd62e7b40-55f2-48b6-9506-56f8d047c6af'),
						array('key' => 'DTP', 'name' => 'DTP', 'active' => 1, 'guid' => '6a2c27ae-413f-4733-ba7e-876c9e2bdd9b'),
						array('key' => 'DTPHIB', 'name' => 'DTP-Hib', 'active' => 1, 'guid' => '9b8d12c5-4b22-4f00-9400-fb090b4375d9'),
						array('key' => 'DTPHIBHEPB', 'name' => 'DTP-Hib-Hep B', 'active' => 1, 'guid' => '6206e1c9-a37e-4035-8fbc-ba12a9792676'),
						array('key' => 'HEPAHEPB', 'name' => 'Hep A-Hep B', 'active' => 1, 'guid' => 'a6a164d0-942c-470f-ba47-df32428e08e3'),
						array('key' => 'HEPAADULT', 'name' => 'Hep A, adult', 'active' => 1, 'guid' => 'eb544526-1df8-4205-b9b7-f5f97da7175c'),
						array('key' => 'HEPANOS', 'name' => 'Hep A, NOS', 'active' => 1, 'guid' => '3f9a47f2-977f-45bd-aca9-ccce0ba8b655'),
						array('key' => 'HEPAPEDNOS', 'name' => 'Hep A, pediatric, NOS', 'active' => 1, 'guid' => 'fe089882-de7f-49ab-b3c9-4179b6e63787', 'data' => array(
							array('key' => 'HEPA2DOSE', 'name' => 'Hep A, ped/adol, 2 dose', 'active' => 1, 'guid' => '8b6a843c-9963-4f98-a784-320ef03dd588'),
							array('key' => 'HEPA3DOSE', 'name' => 'Hep A, ped/adol, 3 dose', 'active' => 1, 'guid' => '46f7460a-b267-42f1-942f-ee558ad679d9'),
						)),
						array('key' => 'HEPBADOPED', 'name' => 'Hep B, adolescent or pediatric', 'active' => 1, 'guid' => '27167a48-2d54-4ce5-8484-8254070c56f8'),
						array('key' => 'HEPBADOHRI', 'name' => 'Hep B, adolescent/high risk infant', 'active' => 1, 'guid' => 'f48874f6-9dc0-487f-8175-eb29ce505df7'),
						array('key' => 'HEPBADULT4', 'name' => 'Hep B, adult4', 'active' => 1, 'guid' => '64dd4c21-7622-4b75-a75e-8a79fb7549f8'),
						array('key' => 'HEPBDIAL', 'name' => 'Hep B, dialysis', 'active' => 1, 'guid' => 'ca7bd4ab-bb7d-426d-9401-5cdeec8d3c1b'),
						array('key' => 'HIBPRPOMP', 'name' => 'Hib (PRP-OMP)', 'active' => 1, 'guid' => '76bf65ac-1b54-43ec-a673-a90417b7be94'),
						array('key' => 'HIBHEPB', 'name' => 'Hib-Hep B', 'active' => 1, 'guid' => '26737169-185c-4028-af0c-bbd53d832330'),
						array('key' => 'HIBNOS', 'name' => 'Hib, NOS', 'active' => 1, 'guid' => '58b34ec3-1da1-4948-bc93-b0ffa63eac27'),
						array('key' => 'IG', 'name' => 'IG', 'active' => 1, 'guid' => '62cd7074-f8b6-4152-9442-7a50ae2d2aeb'),
						array('key' => 'ILI', 'name' => 'influenza, live, intranasal', 'active' => 1, 'guid' => '5d8b26d3-6cb6-4db6-a37c-c2e0932d7211'),
						array('key' => 'INFLUNOS', 'name' => 'influenza, NOS', 'active' => 1, 'guid' => '510ea00b-6424-417b-8512-9d5de35f897b'),
						array('key' => 'INFLUSPLIT', 'name' => 'influenza, split (incl. purified surface antigen)', 'active' => 1, 'guid' => '57739f4d-dbd0-40b2-aa29-c990b657e8fa'),
						array('key' => 'IPV', 'name' => 'IPV', 'active' => 1, 'guid' => '7de7bf5d-473c-4fe4-8cea-c17bd476583d'),
						array('key' => 'JAPENCEPHA', 'name' => 'Japanese encephalitis', 'active' => 1, 'guid' => '93f36dab-0df5-460b-b3b6-15c9da2c2c0c'),
						array('key' => 'MR', 'name' => 'M/R', 'active' => 1, 'guid' => '6849bf56-a264-468a-bbb3-3573507959c3'),
						array('key' => 'MEASLES', 'name' => 'measles', 'active' => 1, 'guid' => 'cf20d29c-16b3-4c48-a487-74158f420c1d'),
						array('key' => 'MENINGO', 'name' => 'meningococcal', 'active' => 1, 'guid' => '30c594a4-3b54-452f-b98e-1a671e8e1616'),
						array('key' => 'MENINGOACY', 'name' => 'meningococcal A,C,Y,W-135 diphtheria conjugate', 'active' => 1, 'guid' => 'e0f6799f-72b9-4e61-a9cc-3ed29ea103e6'),
						array('key' => 'MMR', 'name' => 'MMR', 'active' => 1, 'guid' => 'abba6d7e-013f-4dcd-8e6b-91d7f69de552'),
						array('key' => 'MMRV', 'name' => 'MMRV', 'active' => 1, 'guid' => '6138ba95-3d34-42a5-8c0c-8ed75d07a82e'),
						array('key' => 'MUMPS', 'name' => 'mumps', 'active' => 1, 'guid' => '89814b26-e018-4ab0-8554-23706af10b52'),
						array('key' => 'OPV', 'name' => 'OPV', 'active' => 1, 'guid' => '633439bd-72ad-4fdc-b691-74e86df1d604'),
						array('key' => 'PNEUMOCOCC', 'name' => 'pneumococcal', 'active' => 1, 'guid' => 'f8de5282-486e-416f-b800-416d2ad89f43'),
						array('key' => 'PNEUMOCONJ', 'name' => 'pneumococcal conjugate', 'active' => 1, 'guid' => '33af758a-25a7-4fd5-8486-a739fa99b4df'),
						array('key' => 'PNEUMONOS', 'name' => 'pneumococcal, NOS', 'active' => 1, 'guid' => 'd20c4699-8d8f-49f6-878a-94843367db3c'),
						array('key' => 'POLIONOS', 'name' => 'polio, NOS', 'active' => 1, 'guid' => '7da1050b-13da-4c53-800b-3d6e6e2f45af'),
						array('key' => 'RABIESNOS', 'name' => 'rabies, NOS', 'active' => 1, 'guid' => '8c5bf681-736f-4da4-ae45-258f28321e6f'),
						array('key' => 'RIG', 'name' => 'RIG', 'active' => 1, 'guid' => 'c2842699-7783-4faa-a646-632378741176'),
						array('key' => 'ROTAMONO', 'name' => 'rotavirus, monovalent', 'active' => 1, 'guid' => '0314a884-7aae-4b1a-8833-adfb530816d6'),
						array('key' => 'ROTANOS', 'name' => 'rotavirus, NOS', 'active' => 1, 'guid' => 'cacba082-7ee6-46df-8fe1-46628f1a4a91'),
						array('key' => 'ROTAPENT', 'name' => 'rotavirus, pentavalent', 'active' => 1, 'guid' => 'fedcd09b-42a7-4644-aeab-c7f2caa83212'),
						array('key' => 'ROTATETRA', 'name' => 'rotavirus, tetravalent', 'active' => 1, 'guid' => '2180a272-3d29-4e1f-b375-d7f8ea583cfe'),
						array('key' => 'RUBELLA', 'name' => 'rubella', 'active' => 1, 'guid' => '6e2bacfd-9f04-40ee-9741-21a2f0cd81b1'),
						array('key' => 'RUBELLAMUM', 'name' => 'rubella/mumps', 'active' => 1, 'guid' => '7a15629b-8025-49fc-b7c2-f67e096348c2'),
						array('key' => 'TD', 'name' => 'Td (adult)', 'active' => 1, 'guid' => '45de53ee-ee5f-420e-bffa-800b5605fb9d'),
						array('key' => 'TDAP', 'name' => 'Tdap', 'active' => 1, 'guid' => 'dc7d7b23-036d-4603-a632-69b1b20504eb'),
						array('key' => 'TYPHOIDORA', 'name' => 'typhoid, oral', 'active' => 1, 'guid' => '2eeffb22-901a-41c7-8eeb-b124bf6189a5'),
						array('key' => 'TYPHOIDVCP', 'name' => 'typhoid, ViCPs', 'active' => 1, 'guid' => '9f618282-2ed0-4d1d-ab8e-b628c39b3cb7'),
						array('key' => 'VARICELLA', 'name' => 'varicella', 'active' => 1, 'guid' => '3fa4dc62-5cbe-41d4-9182-e0aa88363803'),
						array('key' => 'YELLOWFEV', 'name' => 'yellow fever', 'active' => 1, 'guid' => '7b06a6a2-806a-456d-8775-7af6fc8c04c4'),
						array('key' => 'ZOSTER', 'name' => 'zoster', 'active' => 1, 'guid' => 'b417b6c6-95df-4a6d-8848-9af7015f226c'),
					)),
					'common' => array('key' => 'common', 'name' => PatientImmunization::ENUM_SECTION_COMMON_NAME, 'active' => 1, 'guid' => 'd5e910d0-8ebe-4f14-aa95-e8be0d1689aa'),
				)),
				'reaction' => array('key' => 'reaction', 'name' => PatientImmunization::ENUM_REACTION_NAME, 'active' => 1, 'guid' => 'f2f28fce-59e8-404e-8900-d44ec0f433a6', 'data' => array(
					array('key' => 'FV', 'name' => 'Fever', 'active' => 1, 'guid' => '271766fc-d188-4963-bffe-6796b08b464b'),
					array('key' => 'IR', 'name' => 'Irritability', 'active' => 1, 'guid' => 'af646175-01d9-4edb-989e-856e7b691ec2'),
					array('key' => 'LRS', 'name' => 'Local reaction or swelling', 'active' => 1, 'guid' => 'c1960596-6264-4541-a505-20ba90babb6a'),
					array('key' => 'VM', 'name' => 'Vomiting', 'active' => 1, 'guid' => '8f8b75ab-f9bf-4669-b7a9-2b94c9ed6939'),
				)),
				'bodySite' => array('key' => 'bodySite', 'name' => PatientImmunization::ENUM_BODY_SITE_NAME, 'active' => 1, 'guid' => '2bd9a4a9-c44b-4581-9190-abd4521a3eef', 'data' => array(
					array('key' => 'BE', 'name' => 'Bilateral Ears', 'active' => 1, 'guid' => '817fad17-9204-4605-84ba-ee5bdaf397c8'),
					array('key' => 'LVL', 'name' => 'Left Vastus Lateralis', 'active' => 1, 'guid' => '992e7649-6325-4d3f-a1c5-4e2e6017cdd2'),
					array('key' => 'OU', 'name' => 'Bilateral Eyes', 'active' => 1, 'guid' => '2a7c3566-8154-4d05-bc38-d28009152cdf'),
					array('key' => 'NB', 'name' => 'Nebulized', 'active' => 1, 'guid' => '0fe768a5-47bc-486b-b18d-9a538eef4601'),
					array('key' => 'BN', 'name' => 'Bilateral Nares', 'active' => 1, 'guid' => '0846a5ae-56e8-49bd-b2b8-e7c91ff076a8'),
					array('key' => 'PA', 'name' => 'Perianal', 'active' => 1, 'guid' => 'a5d57924-bd8c-4288-a665-4758790542d8'),
					array('key' => 'BU', 'name' => 'Buttock', 'active' => 1, 'guid' => '744b3b4d-f0a0-452b-bd41-342bbed9c9b5'),
					array('key' => 'PERIN', 'name' => 'Perineal', 'active' => 1, 'guid' => '26829293-4e39-49f0-9e67-726f482e03d6'),
					array('key' => 'CT', 'name' => 'Chest Tube', 'active' => 1, 'guid' => '8165c43b-e4f8-4d67-bf6f-498151ca4555'),
					array('key' => 'RA', 'name' => 'Right Arm', 'active' => 1, 'guid' => '41788bf0-0168-4a15-9f1a-21958c0feb4b'),
					array('key' => 'LA', 'name' => 'Left Arm', 'active' => 1, 'guid' => 'b6d86eea-14e8-4217-a7d8-a35303413fb4'),
					array('key' => 'RAC', 'name' => 'Right Anterior Chest', 'active' => 1, 'guid' => 'c1b4bc0c-6053-4c20-aab4-dc9a12543e84'),
					array('key' => 'LAC', 'name' => 'Left Anterior Chest', 'active' => 1, 'guid' => 'a9c43a58-9793-4c71-bad8-01e28a8740e6'),
					array('key' => 'RACF', 'name' => 'Right Antecubital Fossa', 'active' => 1, 'guid' => '2e316cb7-7014-4ae0-acfc-9d52ed053d86'),
					array('key' => 'LACF', 'name' => 'Left Antecubital Fossa', 'active' => 1, 'guid' => '1036690d-3109-4058-b139-fa38795ee6f0'),
					array('key' => 'RD', 'name' => 'Right Deltoid', 'active' => 1, 'guid' => '95d95cef-942d-40c2-8909-80937939b5b1'),
					array('key' => 'LD', 'name' => 'Left Deltoid', 'active' => 1, 'guid' => '61e6108c-3dc5-4a3c-8d2e-02a31cc1338c'),
					array('key' => 'RE', 'name' => 'Right Ear', 'active' => 1, 'guid' => 'e99ee63e-e44e-4470-a0e2-daf562e00aa5'),
					array('key' => 'LE', 'name' => 'Left Ear', 'active' => 1, 'guid' => '513260f6-34f2-4fa5-a3ba-293651fc7117'),
					array('key' => 'REJ', 'name' => 'Right External Jugular', 'active' => 1, 'guid' => 'f551439b-d638-40ee-8803-73f896e8b35e'),
					array('key' => 'LEJ', 'name' => 'Left External Jugular', 'active' => 1, 'guid' => 'b39abab8-6c86-409a-90b3-4e87729b33a7'),
					array('key' => 'OD', 'name' => 'Right Eye', 'active' => 1, 'guid' => '73024d8a-75d7-479d-bdbd-a7f9f61e8582'),
					array('key' => 'OS', 'name' => 'Left Eye', 'active' => 1, 'guid' => 'e8fad4ac-b3a2-4dbe-aa62-a182c511e5d2'),
					array('key' => 'RF', 'name' => 'Right Foot', 'active' => 1, 'guid' => '1b9dc027-6038-4245-ab4d-b84166a7e851'),
					array('key' => 'LF', 'name' => 'Left Foot', 'active' => 1, 'guid' => 'a6dc3419-ee2c-4e4d-a6a2-ca5bdcded4a0'),
					array('key' => 'RG', 'name' => 'Right Gluteus Medius', 'active' => 1, 'guid' => 'd8791777-6219-4eda-a9d7-e1171eb4aa5a'),
					array('key' => 'LG', 'name' => 'Left Gluteus Medius', 'active' => 1, 'guid' => 'f51c256a-7cfb-4815-a68c-b3baf9186de6'),
					array('key' => 'RH', 'name' => 'Right Hand', 'active' => 1, 'guid' => 'b1e9ed45-815a-4c8f-85af-16ed40b7ae74'),
					array('key' => 'LH', 'name' => 'Left Hand', 'active' => 1, 'guid' => 'ab69504d-f2b2-4f22-bb08-20b2d5108d39'),
					array('key' => 'RIJ', 'name' => 'Right Internal Jugular', 'active' => 1, 'guid' => '34ceb997-22bd-47c4-a6ae-a072ec573e1b'),
					array('key' => 'LIJ', 'name' => 'Left Internal Jugular', 'active' => 1, 'guid' => 'f18f9da5-c38a-4393-8f3d-39215772f1b1'),
					array('key' => 'RLAQ', 'name' => 'Rt Lower Abd Quadrant', 'active' => 1, 'guid' => 'bf61a5bb-0ed4-4699-b6b2-aea0a209d1c2'),
					array('key' => 'LLAQ', 'name' => 'Left Lower Abd Quadrant', 'active' => 1, 'guid' => 'b22540f3-3577-4a69-aa59-7559294cb24d'),
					array('key' => 'RLFA', 'name' => 'Right Lower Forearm', 'active' => 1, 'guid' => '9cd3e2de-221c-480e-b0e5-a8c7cde4cb47'),
					array('key' => 'LLFA', 'name' => 'Left Lower Forearm', 'active' => 1, 'guid' => '306b597f-fb0c-497f-98b8-edfa9d12d5a8'),
					array('key' => 'RMFA', 'name' => 'Right Mid Forearm', 'active' => 1, 'guid' => 'e0bc5e05-a634-4071-8413-bbdc41dfbcb4'),
					array('key' => 'LMFA', 'name' => 'Left Mid Forearm', 'active' => 1, 'guid' => '1437a1b4-ce58-4960-af4b-817612bfe996'),
					array('key' => 'RN', 'name' => 'Right Naris', 'active' => 1, 'guid' => '40a6af4e-f6a7-457f-92ff-c08228492fe9'),
					array('key' => 'LN', 'name' => 'Left Naris', 'active' => 1, 'guid' => 'f72a9489-3451-418c-8e1a-6d333acde0fc'),
					array('key' => 'RPC', 'name' => 'Right Posterior Chest', 'active' => 1, 'guid' => 'da0ddc8d-f5e6-44f7-b92d-bf2faed31461'),
					array('key' => 'LPC', 'name' => 'Left Posterior Chest', 'active' => 1, 'guid' => '7f12beb0-24a7-4ced-ba88-50df1e6026dd'),
					array('key' => 'RSC', 'name' => 'Right Subclavian', 'active' => 1, 'guid' => '3842f3ea-adb2-4f56-b92e-fbf5012e27d7'),
					array('key' => 'LSC', 'name' => 'Left Subclavian', 'active' => 1, 'guid' => '704f3b7c-1b61-43fa-a990-81aeea5808eb'),
					array('key' => 'RT', 'name' => 'Right Thigh', 'active' => 1, 'guid' => '5bc22828-6d87-44c0-be70-ae489cf14ca8'),
					array('key' => 'LT', 'name' => 'Left Thigh', 'active' => 1, 'guid' => 'eec46a09-a81a-416a-b506-76f0ca7b170e'),
					array('key' => 'RUA', 'name' => 'Right Upper Arm', 'active' => 1, 'guid' => '3ce2d4ec-a5c7-4994-88c8-8fef7ac78381'),
					array('key' => 'LUA', 'name' => 'Left Upper Arm', 'active' => 1, 'guid' => 'fd91391a-3c4a-4cda-8f2f-c6a5dae78a1f'),
					array('key' => 'RUAQ', 'name' => 'Right Upper Abd Quadrant', 'active' => 1, 'guid' => '5f1bb656-6bcb-463e-b617-cf2fe3b9e875'),
					array('key' => 'LUAQ', 'name' => 'Left Upper Abd Quadrant', 'active' => 1, 'guid' => 'f3357ad4-1d32-4a3a-842b-00aba0f5fbb6'),
					array('key' => 'RUFA', 'name' => 'Right Upper Forearm', 'active' => 1, 'guid' => '9562c0a3-a780-472d-9bf7-26601f416056'),
					array('key' => 'LUFA', 'name' => 'Left Upper Forearm', 'active' => 1, 'guid' => 'eba8c5ca-d69b-48f3-95c2-c3d488e7bc32'),
					array('key' => 'RVL', 'name' => 'Right Vastus Lateralis', 'active' => 1, 'guid' => '514ddda0-acaf-472c-8ca3-f609b7f3e088'),
					array('key' => 'LVG', 'name' => 'Left Ventragluteal', 'active' => 1, 'guid' => '5f4c075e-d331-45cf-8ce5-91b2467473b6'),
					array('key' => 'RVG', 'name' => 'Right Ventragluteal', 'active' => 1, 'guid' => 'ff8c68c4-a595-4ce0-aadf-d44b82fc83cb'),
				)),
				'adminRoute' => array('key' => 'adminRoute', 'name' => PatientImmunization::ENUM_ADMINISTRATION_ROUTE_NAME, 'active' => 1, 'guid' => '81f906be-7ec4-4be3-926c-119878b772f8', 'data' => array(
					array('key' => 'AP', 'name' => 'Apply Externally', 'active' => 1, 'guid' => 'ac394c80-9f00-4fe1-a249-8037994bcf35'),
					array('key' => 'MM', 'name' => 'Mucous Membrane', 'active' => 1, 'guid' => 'a8b0f062-dcd2-409a-963d-0e426c5b427f'),
					array('key' => 'B', 'name' => 'Buccal', 'active' => 1, 'guid' => '01357f20-2afe-4ce6-8f00-bfb1e8cd6c07'),
					array('key' => 'NS', 'name' => 'Nasal', 'active' => 1, 'guid' => '6c583497-a24b-49e7-9261-202592e158b4'),
					array('key' => 'DT', 'name' => 'Dental', 'active' => 1, 'guid' => '529b0ffa-53f7-4f8f-be80-2b0e2418fc15'),
					array('key' => 'NG', 'name' => 'Nasogastric', 'active' => 1, 'guid' => '7ad9d50c-900b-473c-a8cf-7bee228f5218'),
					array('key' => 'EP', 'name' => 'Epidural', 'active' => 1, 'guid' => '54b2cbe3-debb-408c-a07a-ebb886f37458'),
					array('key' => 'NP', 'name' => 'Nasal Prongs', 'active' => 1, 'guid' => 'cbe5b44d-0883-4837-b8f0-e5d1567642d8'),
					array('key' => 'ET', 'name' => 'Endotrachial Tube', 'active' => 1, 'guid' => '65b31340-19f9-4dcb-b650-49e28e346c22'),
					array('key' => 'NT', 'name' => 'Nasotrachial Tube', 'active' => 1, 'guid' => 'cd65c684-9a09-445e-86f5-a77d102f45f5'),
					array('key' => 'GTT', 'name' => 'Gastrostomy Tube', 'active' => 1, 'guid' => '240272c3-605b-471d-bf04-41b5b3ca4785'),
					array('key' => 'OP', 'name' => 'Ophthalmic', 'active' => 1, 'guid' => '0774ea5c-a480-497c-ad01-67479ab4f307'),
					array('key' => 'GU', 'name' => 'GU Irrigant', 'active' => 1, 'guid' => '9027f986-baa1-4475-bfa7-566bb76b24ec'),
					array('key' => 'OT', 'name' => 'Otic', 'active' => 1, 'guid' => '2a83bf73-8246-4b9f-acb2-9fb161f06b01'),
					array('key' => 'IMR', 'name' => 'Immerse (Soak) Body Part', 'active' => 1, 'guid' => '402ad1f5-3938-4f54-881c-5b01afaaf983'),
					array('key' => 'OTH', 'name' => 'Other/Miscellaneous', 'active' => 1, 'guid' => 'f2b5b92d-69de-461c-9ad8-9baa146d4985'),
					array('key' => 'IA', 'name' => 'Intra-arterial', 'active' => 1, 'guid' => '8b8ed442-2c10-4939-9f23-9105a1eb514f'),
					array('key' => 'PF', 'name' => 'Perfusion', 'active' => 1, 'guid' => 'ca1b1f65-b991-499d-bad3-8e28f8bf8044'),
					array('key' => 'IB', 'name' => 'Intrabursal', 'active' => 1, 'guid' => '7efa6d17-a554-4701-b4e0-ba7e0b676fd0'),
					array('key' => 'PO', 'name' => 'Oral', 'active' => 1, 'guid' => '0919bf99-1809-452f-8e96-0d65c9a6a1ff'),
					array('key' => 'IC', 'name' => 'Intracardiac', 'active' => 1, 'guid' => '006799c8-a683-44ba-b1f5-d6c526d11873'),
					array('key' => 'PR', 'name' => 'Rectal', 'active' => 1, 'guid' => '5c5a4bf1-7f6c-4d3d-a190-ee5e354bdbb9'),
					array('key' => 'ICV', 'name' => 'Intracervical (uterus)', 'active' => 1, 'guid' => '5694bdf5-f905-4196-b3a2-80e6e58e4cdd'),
					array('key' => 'RM', 'name' => 'Rebreather Mask', 'active' => 1, 'guid' => '6e896602-2d9a-41bc-9de5-147aa2e85d7c'),
					array('key' => 'ID', 'name' => 'Intradermal', 'active' => 1, 'guid' => 'adb5593c-84a2-43a7-accd-e073d51a6cd8'),
					array('key' => 'SD', 'name' => 'Soaked Dressing', 'active' => 1, 'guid' => '881e62bd-702a-4e6e-9221-d683acf70a04'),
					array('key' => 'IH', 'name' => 'Inhalation', 'active' => 1, 'guid' => '6ecf7fb1-7bce-4b84-83d6-5c5fad224640'),
					array('key' => 'SC', 'name' => 'Subcutaneous', 'active' => 1, 'guid' => 'd68d4a12-df09-4993-91f0-b73f028cdeb0'),
					array('key' => 'IHA', 'name' => 'Intrahepatic Artery', 'active' => 1, 'guid' => '74e1dceb-5a23-47a0-95dc-3cfeb5b7f9df'),
					array('key' => 'SL', 'name' => 'Sublingual', 'active' => 1, 'guid' => '0a527e0a-76bb-41eb-8782-3017fb328775'),
					array('key' => 'IM', 'name' => 'Intramuscular', 'active' => 1, 'guid' => '85ad9ae0-ef7c-4edc-8faf-c891444cce75'),
					array('key' => 'TP', 'name' => 'Topical', 'active' => 1, 'guid' => '0dc5e45a-8718-42d7-b5b0-c5d1e9608eb6'),
					array('key' => 'IN', 'name' => 'Intranasal', 'active' => 1, 'guid' => '8241722b-7ef2-4f57-b749-58cab1b8b0eb'),
					array('key' => 'TRA', 'name' => 'Tracheostomy', 'active' => 1, 'guid' => 'e7b7ce60-3758-45a5-8b7b-d3e48da9616f'),
					array('key' => 'IO', 'name' => 'Intraocular', 'active' => 1, 'guid' => '62fda0e6-6373-4a24-8a59-6df4317789f5'),
					array('key' => 'TD', 'name' => 'Transdermal', 'active' => 1, 'guid' => '683d6ea4-f752-45db-a65d-b5eff1570a23'),
					array('key' => 'IP', 'name' => 'Intraperitoneal', 'active' => 1, 'guid' => '4cab7879-e4d8-4e84-bb1f-5d4cf132e1ea'),
					array('key' => 'TL', 'name' => 'Translingual', 'active' => 1, 'guid' => '73dfdc99-bdc4-4aa9-bfe5-37ec606f3dc9'),
					array('key' => 'IS', 'name' => 'Intrasynovial', 'active' => 1, 'guid' => '8d84567b-ee17-4733-81d5-6f1130eb69aa'),
					array('key' => 'UR', 'name' => 'Urethral', 'active' => 1, 'guid' => '253fc295-82a5-412f-b78e-b71409e8705e'),
					array('key' => 'IT', 'name' => 'Intrathecal', 'active' => 1, 'guid' => 'e858045d-89ee-4c04-b636-ca8aaba495eb'),
					array('key' => 'VG', 'name' => 'Vaginal', 'active' => 1, 'guid' => 'f3de7a2c-84d4-4f47-9b05-935d39795816'),
					array('key' => 'IU', 'name' => 'Intrauterine', 'active' => 1, 'guid' => 'ccd31159-c27e-4922-a38c-1d95a5a7975a'),
					array('key' => 'VM', 'name' => 'Ventimask', 'active' => 1, 'guid' => '5e966808-6d1c-4529-8b96-81f1903c51ec'),
					array('key' => 'IV', 'name' => 'Intravenous', 'active' => 1, 'guid' => '6c382cec-1cd9-441a-aaea-eb38e101bc9c'),
					array('key' => 'WND', 'name' => 'Wound', 'active' => 1, 'guid' => 'b446b8d1-ea0f-4934-9585-ca624e562693'),
					array('key' => 'MTH', 'name' => 'Mouth/Throat', 'active' => 1, 'guid' => '43516e51-bfdf-480b-b155-c351d4771552'),
				)),
			);

			$level = array();
			$level['guid'] = 'bde63462-e977-491c-8fd1-a7773a8ce890';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateTeamPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = TeamMember::ENUM_PARENT_NAME;
			$key = 'TP';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'blueTeam' => array('key' => 'BLUE', 'name' => 'Blue', 'active' => 1, 'guid' => '26d1a11f-0edf-4a4e-82f8-db04f8317beb', 'data' => array(
					array('key' => 'ATTENDING', 'name' => 'Attending', 'active' => 1, 'guid' => '04d8f888-1187-4887-b44d-9e49abad78b7', 'data' => array(
						array('key' => 'NURSE', 'name' => 'Nurse', 'active' => 1, 'guid' => '5a786290-dd79-40a7-8025-7354d876a1f8'),
						array('key' => 'PA', 'name' => 'Physician Assistant', 'active' => 1, 'guid' => 'aae532ad-f47e-441a-ad6a-fc358ef43bec'),
						array('key' => 'NP1', 'name' => 'Nurse Practitioner', 'active' => 1, 'guid' => 'f7d6ceb6-349c-40ac-ad0b-e9c5f08b36e5'),
						array('key' => 'NP2', 'name' => 'Nurse Practitioner', 'active' => 1, 'guid' => 'd924972e-dc79-4922-ae96-50fcb22a5cae'),
					)),
				)),
			);

			$level = array();
			$level['guid'] = 'bf3a3c1e-f1a6-4af0-a734-f03234e0eeb1';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['ormClass'] = 'TeamMember';
			$level['ormEditMethod'] = 'ormEditMethod';
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateHSAPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = HealthStatusAlert::ENUM_PARENT_NAME;
			$key = 'HP';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'LSA', 'name' => 'Lab Status Alerts', 'active' => 1, 'guid' => '085a0163-9c6a-4af4-9b01-24bd135c0088'),
				array('key' => 'VSA', 'name' => 'Vitals Status Alerts', 'active' => 1, 'guid' => '2094150c-83b1-498f-a058-c8f2af382262'),
				array('key' => 'NSA', 'name' => 'Note Status Alerts', 'active' => 1, 'guid' => 'e49ad008-36c6-45ff-92cf-8715c867840a'),
			);

			$level = array();
			$level['guid'] = 'e4c12dae-e1f7-4c7a-a6e5-175ce4fb3412';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateReasonPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientNote::ENUM_REASON_PARENT_NAME;
			$key = 'RP';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'RPCB', 'name' => 'Call Back', 'active' => 1, 'guid' => '5f223291-2456-4f6f-9d2a-a3762bf6b654'),
				array('key' => 'RPCP', 'name' => 'Check Progress', 'active' => 1, 'guid' => '763e2c00-0e78-4d41-a9d9-db9770fc8a2a'),
				array('key' => 'RPC', 'name' => 'Converted', 'active' => 1, 'guid' => '37dff69c-bc41-4743-a19e-d5e65ce47bb8'),
				array('key' => 'RPRT', 'name' => 'Repeat Test', 'active' => 1, 'guid' => '5c356d89-f9c4-48f0-9b11-b97f5b51bb21'),
				array('key' => 'RPO', 'name' => 'Other', 'active' => 1, 'guid' => '8f882780-4b00-49b2-ba69-5fefd6904ec7'),
				array('key' => 'RPNA', 'name' => 'N/A', 'active' => 1, 'guid' => '892bc671-0361-4996-af6a-8d65d5b209d0'),
			);

			$level = array();
			$level['guid'] = 'b1ff20ff-bd6d-41f1-b4f2-d1e8ce4299f0';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateProcedurePreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientProcedure::ENUM_PARENT_NAME;
			$key = 'ProcPref';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'GIPROC', 'name' => 'GI PROCEDURES', 'active' => 1, 'guid' => '9cd715cc-a030-4440-a305-d08f47899cfb'),
				array('key' => 'COLONOSCOP', 'name' => 'COLONOSCOPY', 'active' => 1, 'guid' => 'd7c1831e-768a-4728-b3e0-02650238bc32'),
			);

			$level = array();
			$level['guid'] = '2f017585-a889-49a2-8f52-e01e58e540ca';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateEducationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_EDUC_PARENT_NAME;
			$key = 'EduPref';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'section' => array('key' => 'SECTION', 'name' => PatientEducation::ENUM_EDUC_SECTION_NAME, 'active' => 1, 'guid' => '09d0f1e5-715d-4c87-9ead-8169cf8ebf13', 'data' => array(
					'other' => array('key' => 'OTHER', 'name' => PatientEducation::ENUM_EDUC_SECTION_OTHER_NAME, 'active' => 1, 'guid' => 'ad3cfda0-b93b-4bec-ab7b-d69b4caaa1fd', 'data' => array(
						array('key' => 'HFA', 'name' => 'HF ACTIVITY', 'active' => 1, 'guid' => 'a6ad54bf-0b62-4f27-a723-d85968dfa899'),
						array('key' => 'HFD', 'name' => 'HF DIET', 'active' => 1, 'guid' => 'f6e870af-a874-43fe-890b-f00908558d34'),
						array('key' => 'HFDM', 'name' => 'HF DISCHARGE MEDS', 'active' => 1, 'guid' => 'de847c7c-efa8-4fe5-aa74-5677b05ec199'),
						array('key' => 'HFF', 'name' => 'HF FOLLOWUP', 'active' => 1, 'guid' => '261dd2c8-d476-48a8-8b2c-478f6756ea27'),
						array('key' => 'HFS', 'name' => 'HF SYMPTOMS', 'active' => 1, 'guid' => '8e110ae8-c302-43be-852a-1c1d319e04d4'),
					)),
					'common' => array('key' => 'COMMON', 'name' => PatientEducation::ENUM_EDUC_SECTION_COMMON_NAME, 'active' => 1, 'guid' => 'c58d0def-0dfc-4e64-9765-aa3962c2f7f8', 'data' => array(
						array('key' => 'HYPER', 'name' => 'Hypertension', 'active' => 1, 'guid' => '24c2c962-86e5-462d-ae4d-7d78d6d2ca64'),
					)),
				)),
				'level' => array('key' => 'LEVEL', 'name' => PatientEducation::ENUM_EDUC_LEVEL_NAME, 'active' => 1, 'guid' => 'e23beb46-4534-4a1d-88d7-175c3c55171e', 'data' => array(
					array('key' => 'POOR', 'name' => 'Poor', 'active' => 1, 'guid' => 'f5a283fe-e617-4aa1-b422-0abfedd2bf89'),
					array('key' => 'FAIR', 'name' => 'Fair', 'active' => 1, 'guid' => '35b9d1ca-f40e-4968-bb5b-b15b8b481ff8'),
					array('key' => 'GOOD', 'name' => 'Good', 'active' => 1, 'guid' => 'c4f1b8a8-49ba-4212-892b-4418b62f7dfc'),
					array('key' => 'GNA', 'name' => 'Group-no assessment', 'active' => 1, 'guid' => 'b5a37abd-69cb-480e-9c92-3b4110620cd1'),
					array('key' => 'REFUSED', 'name' => 'Refused', 'active' => 1, 'guid' => 'cf97b57c-506f-44dd-8770-00bbf76f5128'),
				)),
			);

			$level = array();
			$level['guid'] = '3cef009a-9562-4355-91ef-bfec93244027';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateExamResultPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_RESULT_PARENT_NAME;
			$key = 'Exam_Res';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'ABNORMAL', 'name' => 'Abnormal', 'active' => 1, 'guid' => '9bf75109-4660-4b5f-8209-e3227bce347f'),
				array('key' => 'NORMAL', 'name' => 'Normal', 'active' => 1, 'guid' => '3960a4b6-1b2f-4284-bf86-9d24aa6c67d1'),
			);

			$level = array();
			$level['guid'] = '18422748-862b-428e-91e3-145ec3d57f5c';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateExamOtherPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_OTHER_PARENT_NAME;
			$key = 'Exam_Other';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'EXAMABD', 'name' => 'ABDOMEN EXAM', 'active' => 1, 'guid' => '7ad89c31-b522-4c8b-8bb6-d6ce74d214fc'),
				array('key' => 'EXAMAMS', 'name' => 'AUDIOMETRIC SCREENING', 'active' => 1, 'guid' => 'e4374dce-22d4-4745-b9f6-7b1779b6290d'),
				array('key' => 'EXAMAMT', 'name' => 'AUDIOMETRIC THRESHOLD', 'active' => 1, 'guid' => 'df9c19a4-dafe-49fb-8170-60fe404958cb'),
				array('key' => 'EXAMBREAST', 'name' => 'BREAST EXAM', 'active' => 1, 'guid' => '10996c24-5a76-4573-9450-7ea6a277901c'),
				array('key' => 'EXAMCHEST', 'name' => 'CHEST EXAM', 'active' => 1, 'guid' => '1b096999-31c0-40b6-a5ce-b7985df4ec30'),
			);

			$level = array();
			$level['guid'] = '770f3bad-f41d-492a-9dcf-db8631c9f471';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateMedicationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = Medication::ENUM_PARENT_NAME;
			$key = 'MED_PREF';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'ADM_SCHED', 'name' => Medication::ENUM_ADMIN_SCHED, 'active' => 1, 'guid' => 'd9c17d1b-8826-4f16-8cfc-fdca53a28c56', 'data' => array(
					array('key' => 'BID', 'name' => 'twice per day', 'active' => 1, 'guid' => '568fc969-9d30-4080-b073-dfa251a2f59f'),
					array('key' => 'TID', 'name' => 'three times per day', 'active' => 1, 'guid' => 'b9f9ae03-4f9c-472f-8145-cf3b0ea71a47'),
					array('key' => 'MO-WE-FR', 'name' => 'once on monday, once on wednesday, once on friday', 'active' => 1, 'guid' => 'c7390092-7e8a-46c3-b91a-ce0a994ae328'),
					array('key' => 'NOW', 'name' => 'right now', 'active' => 1, 'guid' => 'cd25d187-2803-4ebb-ba61-7e52f6e10a13'),
					array('key' => 'ONCE', 'name' => 'one time', 'active' => 1, 'guid' => '2d0fbb67-c7b1-4b7c-afd8-5e062c90dedc'),
					array('key' => 'Q12H', 'name' => 'every 12 hours', 'active' => 1, 'guid' => 'f85053cd-c7be-4620-b838-ebd95b59525c'),
					array('key' => 'Q24H', 'name' => 'every 24 hours', 'active' => 1, 'guid' => 'df7ac22c-f17e-4409-afd6-e220f8b2ac5f'),
					array('key' => 'Q2H', 'name' => 'every 2 hours', 'active' => 1, 'guid' => 'b2ee68e0-8b3d-4a23-afe7-93ad6492273f'),
					array('key' => 'Q3H', 'name' => 'every 3 hours', 'active' => 1, 'guid' => '69f90ed8-5551-4000-a4fa-0e7fc1a8ae5e'),
					array('key' => 'Q4H', 'name' => 'every 4 hours', 'active' => 1, 'guid' => '6297f4e0-96e0-4869-870a-c03ec5a738aa'),
					array('key' => 'Q6H', 'name' => 'every 6 hours', 'active' => 1, 'guid' => '0444e2a2-ebdc-4eb5-8d17-a3009ab7e1fa'),
					array('key' => 'Q8H', 'name' => 'every 8 hours', 'active' => 1, 'guid' => '73b036d5-9641-4a4a-8005-70b048cde9a8'),
					array('key' => 'Q5MIN', 'name' => 'every 5 minutes', 'active' => 1, 'guid' => '31f3d346-0f43-4a6f-bf74-5546f74e1e5f'),
					array('key' => 'QDAY', 'name' => 'once per day', 'active' => 1, 'guid' => 'f342105e-eb93-4057-8120-660b58c086df'),
				)),
			);

			$level = array();
			$level['guid'] = '29e219fb-95ff-4c5b-8c5b-691f2ee065df';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateColorPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = Room::ENUM_COLORS_NAME;
			$key = 'colors';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => '#FFF8DC', 'name' => 'Cornsilk', 'active' => 1, 'guid' => '6252df95-129a-4c61-9670-1b8eb362a40d'),
				array('key' => '#FAEBD7', 'name' => 'Antiquewhite', 'active' => 1, 'guid' => 'b6941bbc-e61a-4d47-866e-f5ce46d6cf43'),
				array('key' => '#FFF5EE', 'name' => 'Seashell', 'active' => 1, 'guid' => '42afd25f-20a4-4db1-82be-79ef2dece84b'),
				array('key' => '#FAF0E6', 'name' => 'Linen', 'active' => 1, 'guid' => '4abe6e8c-4e23-4794-92c0-4807e66135b8'),
				array('key' => '#FFFFF0', 'name' => 'Ivory', 'active' => 1, 'guid' => '034273ff-47fb-437b-a8a3-c8a2773fca1d'),
				array('key' => '#FFFAF0', 'name' => 'Floralwhite', 'active' => 1, 'guid' => 'd418d4cf-d98a-47a1-975a-10f0903250d0'),
				array('key' => '#FFFAFA', 'name' => 'Snow', 'active' => 1, 'guid' => '746c41e7-fd4d-40e2-84df-91dd2eaf9c35'),
				array('key' => '#F0FFFF', 'name' => 'Azure', 'active' => 1, 'guid' => '681e73e8-c939-4ae3-8bfd-45882cd5f2b9'),
				array('key' => '#F5FFFA', 'name' => 'Mintcream', 'active' => 1, 'guid' => '08994040-0d8b-48fe-a4ab-5dbd638d9fa2'),
				array('key' => '#F8F8FF', 'name' => 'Ghostwhite', 'active' => 1, 'guid' => '12938a56-611b-459a-a3dc-c6771ff58053'),
				array('key' => '#F0FFF0', 'name' => 'Honeydew', 'active' => 1, 'guid' => '62dc90bd-c268-41cd-a8a0-670e6f13a3ea'),
				array('key' => '#F0F8FF', 'name' => 'Aliceblue', 'active' => 1, 'guid' => '79905de0-20cc-49ae-b743-31f15ee9f343'),
				array('key' => '#F5F5DC', 'name' => 'Beige', 'active' => 1, 'guid' => '05a1d654-c7ab-4724-8e0a-0bbc873904b0'),
				array('key' => '#FDF5E6', 'name' => 'Oldlace', 'active' => 1, 'guid' => '7315e6ce-cc8c-43d8-899c-56074ded2fd6'),
				array('key' => '#FFE4C4', 'name' => 'Bisque', 'active' => 1, 'guid' => '725a860b-b06b-4f8f-a7a0-b5db71c8e820'),
				array('key' => '#FFE4B5', 'name' => 'Moccasin', 'active' => 1, 'guid' => '89fa0884-cfff-40ea-830a-4ac81284211e'),
				array('key' => '#F5DEB3', 'name' => 'Wheat', 'active' => 1, 'guid' => 'f4dcca5e-c701-4bcd-9223-540263c6b63d'),
				array('key' => '#FFDEAD', 'name' => 'Navajowhite', 'active' => 1, 'guid' => '0665a199-01d4-4948-9919-60db6cfc26d3'),
				array('key' => '#FFEBCD', 'name' => 'Blanchedalmond', 'active' => 1, 'guid' => '2dc5794d-ca86-4c72-84bf-4d6b8b38a128'),
				array('key' => '#D2B48C', 'name' => 'Tan', 'active' => 1, 'guid' => 'a16b64d1-a8f8-4b95-816d-24b9b8705400'),
				array('key' => '#FFE4E1', 'name' => 'Mistyrose', 'active' => 1, 'guid' => '6fc0603d-9019-4598-a753-0fed2646c0f3'),
				array('key' => '#FFF0F5', 'name' => 'Lavenderblush', 'active' => 1, 'guid' => 'ed3a9496-9694-4094-944a-c69ffffe577c'),
				array('key' => '#E6E6FA', 'name' => 'Lavender', 'active' => 1, 'guid' => '5483230b-eec3-4598-a384-742c1271eec7'),
				array('key' => '#87CEFA', 'name' => 'Lightskyblue', 'active' => 1, 'guid' => '15b1c781-233b-4fdf-ad0e-ca0b48dfaf1f'),
				array('key' => '#87CEEB', 'name' => 'Skyblue', 'active' => 1, 'guid' => 'd3343152-c318-4a3d-879c-edddedeb4648'),
				array('key' => '#00BFFF', 'name' => 'Deepskyblue', 'active' => 1, 'guid' => 'e487f64d-173d-441a-a968-4f8997e4eca6'),
				array('key' => '#7FFFD4', 'name' => 'Aquamarine', 'active' => 1, 'guid' => '5447f87c-1fd4-43a1-8f68-5e558dd95e7a'),
				array('key' => '#6495ED', 'name' => 'Cornflowerblue', 'active' => 1, 'guid' => '6f4ef15a-831d-4e23-ab43-fcc8cadac22d'),
				array('key' => '#E9967A', 'name' => 'Darksalmon', 'active' => 1, 'guid' => '43f00373-53b2-4282-9115-9b14f88738c7'),
				array('key' => '#FFA07A', 'name' => 'Lightsalmon', 'active' => 1, 'guid' => 'bcc57daf-4b9d-4b32-bd3a-780c77f259ff'),
				array('key' => '#B0E0E6', 'name' => 'Powderblue', 'active' => 1, 'guid' => 'f1bb8afb-eb16-49c8-a29d-b79783bb5a9d'),
			);

			$level = array();
			$level['guid'] = 'f45a4d8e-ea55-40ba-9f48-07ac00acca43';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateFacilitiesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Facilities';
			$key = 'FACILITIES';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level = array();
			$level['guid'] = '7bf4739b-0d15-455b-85a8-cdeb886daff6';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generatePaymentTypesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Payment Types';
			$key = 'PAY_TYPES';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'VISA', 'name' => 'Visa', 'active' => 1, 'guid' => 'cfc36cc6-76b0-4ea0-a6de-7e8d5217f06a'),
				array('key' => 'MASTERCARD', 'name' => 'MasterCard', 'active' => 1, 'guid' => '1665c4b5-0f91-4c86-b7e6-317ad95efe07'),
				array('key' => 'AMEX', 'name' => 'AMEX', 'active' => 1, 'guid' => '7c29ff7b-966d-428a-8ce5-037a36e73576'),
				array('key' => 'CHECK', 'name' => 'Check', 'active' => 1, 'guid' => '2c7dbe2b-0f24-420f-b950-ba6a024367c4'),
				array('key' => 'CASH', 'name' => 'Cash', 'active' => 1, 'guid' => 'b9eb5d9f-0c95-4ad3-9e51-62a0452083da'),
				array('key' => 'REMITTANCE', 'name' => 'Remittance', 'active' => 1, 'guid' => 'f353bced-e140-4af4-bfd2-deedf575b244'),
				array('key' => 'CORRECTION', 'name' => 'Correction Payment', 'active' => 1, 'guid' => 'fd36d634-6e0b-41c4-bac5-b650a1d5b6d8'),
				array('key' => 'LABPAYMENT', 'name' => 'Labs Payment', 'active' => 1, 'guid' => '9ab537c4-198d-44c1-b5d6-7f60d49bc5c4'),
				array('key' => 'MEDPAYMENT', 'name' => 'Medication Payment', 'active' => 1, 'guid' => '02232de4-014f-4d26-b4eb-717c0e09e607'),
				array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => '6b5a30ba-3f8b-4c00-9a6a-be5f72694c51'),
				array('key' => 'VISITPAY', 'name' => 'Visit Payment', 'active' => 1, 'guid' => 'af075c09-a5dc-4ad4-8ddf-9dd7312472e2'),
				array('key' => 'DISCOUNT', 'name' => 'Discount', 'active' => 1, 'guid' => '704a29e4-029a-4c69-a441-a7c32ba74ee4'),
			);

			$level = array();
			$level['guid'] = 'd1d9039a-a21b-4dfb-b6fa-ec5f41331682';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateCodingPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Coding Preferences';
			$key = 'CODINGPREF';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'VISITYPE', 'name' => 'Visit Type Sections', 'active' => 1, 'guid' => '9eb793f8-1d5d-4ed5-959d-1e238361e00a', 'ormClass' => 'Visit', 'ormEditMethod' => 'ormVisitTypeEditMethod', 'data' => array(
					array('key' => 'NEWPATIENT', 'name' => 'New Patient', 'active' => 1, 'guid' => 'ebc41ebe-dd6b-4b78-97a7-63298ddef675', 'ormClass' => 'Visit', 'ormEditMethod' => 'ormVisitTypeEditMethod'),
					array('key' => 'ESTPATIENT', 'name' => 'Established Patient', 'active' => 1, 'guid' => '519b2620-b893-4bac-8d46-7daefd69aa1e', 'ormClass' => 'Visit', 'ormEditMethod' => 'ormVisitTypeEditMethod'),
					array('key' => 'CONSULT', 'name' => 'Consultations', 'active' => 1, 'guid' => 'd2ba49ec-f2b6-4183-8495-c9c1f8386414', 'ormClass' => 'Visit', 'ormEditMethod' => 'ormVisitTypeEditMethod'),
				)),
				array('key' => 'PROCEDURE', 'name' => 'Procedure Sections', 'active' => 1, 'guid' => '8e6a2456-1710-46be-a018-2afb0ec2829f', 'ormClass' => 'ProcedureCodesCPT', 'ormEditMethod' => 'ormEditMethod'),
				array('key' => 'DIAGNOSIS', 'name' => 'Diagnosis Sections', 'active' => 1, 'guid' => 'fac51e51-95fd-485e-a8f3-62e1228057ad', 'ormClass' => 'DiagnosisCodesICD', 'ormEditMethod' => 'ormEditMethod'),
			);

			$level = array();
			$level['guid'] = 'ab377de7-8ea7-4912-a27b-2f9749499204';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['ormClass'] = 'Visit';
			$level['ormEditMethod'] = 'ormEditMethod';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateFacilityCodesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Facility Codes';
			$key = 'FACIL_CODES';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => '11', 'name' => 'Office', 'active' => 1, 'guid' => '97e73bc4-eb06-4f66-8fb9-67e6974cc2f6'),
				array('key' => '12', 'name' => 'Home', 'active' => 1, 'guid' => '6f87512e-05bc-4144-bf2a-6be063d3e6b8'),
				array('key' => '21', 'name' => 'Inpatient Hospital', 'active' => 1, 'guid' => '1b46d32b-6383-4359-bc37-2bac4d258529'),
				array('key' => '22', 'name' => 'Outpatient Hospital', 'active' => 1, 'guid' => '2506384a-50ae-410b-9cc5-876f26226b7c'),
				array('key' => '23', 'name' => 'Emergency Room - Hospital', 'active' => 1, 'guid' => '64b119ab-495f-4629-87f2-33aa7e000578'),
				array('key' => '24', 'name' => 'Ambulatory Surgical Center', 'active' => 1, 'guid' => '32363b83-4119-4db5-82e4-af3468983419'),
				array('key' => '25', 'name' => 'Birthing Center', 'active' => 1, 'guid' => 'c1c92a9c-14a1-4b90-ad14-b710b13ace85'),
				array('key' => '26', 'name' => 'Military Treatment Facility', 'active' => 1, 'guid' => 'bf869590-b482-47d7-8669-33c7bd3caea6'),
				array('key' => '31', 'name' => 'Skilled Nursing Facility', 'active' => 1, 'guid' => '477762b4-148b-44c7-a869-6ace256a248c'),
				array('key' => '32', 'name' => 'Nursing Facility', 'active' => 1, 'guid' => 'c5c3df6c-4c72-4638-9702-e686322c0a18'),
				array('key' => '33', 'name' => 'Custodial Care Facility', 'active' => 1, 'guid' => '074acacf-3340-483e-ae57-ad65776b5a5e'),
				array('key' => '34', 'name' => 'Hospice', 'active' => 1, 'guid' => '815d94b4-0407-4c78-9168-8f9b266a75cf'),
				array('key' => '41', 'name' => 'Ambulance - Land', 'active' => 1, 'guid' => '59e87b4b-4975-4110-9436-a751cfa31f0b'),
				array('key' => '42', 'name' => 'Ambulance - Air or Water', 'active' => 1, 'guid' => '78c196b9-c2c1-4281-84f7-1e0da0dc6a67'),
				array('key' => '50', 'name' => 'Federally Qualified Health Center', 'active' => 1, 'guid' => '8305dfc2-941d-45dd-a929-a1d84f25bcf6'),
				array('key' => '51', 'name' => 'Inpatient Psychiatric Facility', 'active' => 1, 'guid' => '3fea6917-c5ed-41ed-b2f9-c743bc504cc4'),
				array('key' => '52', 'name' => 'Psychiatric Facility Partial Hospitalization', 'active' => 1, 'guid' => '3f18955f-a3bd-4f22-a86c-6d5539070258'),
				array('key' => '53', 'name' => 'Community Mental Health Center', 'active' => 1, 'guid' => '90d55f5e-d81d-442d-a925-d58467bdb6c7'),
				array('key' => '54', 'name' => 'Intermediate Care Facility/Mentally Retarded', 'active' => 1, 'guid' => 'eb3b94a8-baef-48de-80bb-20ba9465a4af'),
				array('key' => '55', 'name' => 'Residential Substance Abuse Treatment Facility', 'active' => 1, 'guid' => 'c9f8d5de-8e08-46a1-8bae-231783a295a2'),
				array('key' => '56', 'name' => 'Psychiatric Residential Treatment Center', 'active' => 1, 'guid' => '8ba47649-3cbf-49b5-ac7e-9663e53a9795'),
				array('key' => '60', 'name' => 'Mass Immunization Center', 'active' => 1, 'guid' => '905992e4-e172-4bc3-8bb3-240b81a44b78'),
				array('key' => '61', 'name' => 'Comprehensive Inpatient Rehabilitation Facility', 'active' => 1, 'guid' => '4706d13f-bca8-476f-b217-cecf0ee56318'),
				array('key' => '62', 'name' => 'Comprehensive Outpatient Rehabilitation Facility', 'active' => 1, 'guid' => 'd0014445-262a-4dc7-b41c-c88ba5dad2ab'),
				array('key' => '65', 'name' => 'End Stage Renal Disease Treatment Facility', 'active' => 1, 'guid' => '121fdadb-2caf-468c-9f79-5aaa947d38ac'),
				array('key' => '71', 'name' => 'State or Local Public Health Clinic', 'active' => 1, 'guid' => 'a214f437-c072-4197-978b-d919471d427e'),
				array('key' => '72', 'name' => 'Rural Health Clinic', 'active' => 1, 'guid' => '0a981977-c060-4258-a679-f64a2a3c03b7'),
				array('key' => '81', 'name' => 'Independent Laboratory', 'active' => 1, 'guid' => '34fb5d8f-c068-4a81-8740-504b4b114f1a'),
				array('key' => '99', 'name' => 'Other Unlisted Facility', 'active' => 1, 'guid' => '9865976d-a8b0-4811-9161-8d583df8e0ad'),
			);


			$level = array();
			$level['guid'] = '22fb4e1e-a37a-4e7a-9dae-8e220ba939e8';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateIdentifierTypesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Identifier Type';
			$key = 'IDENTIFIER';
			$enumeration = new Enumeration();
			$enumeration->populateByUniqueName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				array('key' => 'OTHER', 'name' => 'Other', 'active' => 1, 'guid' => '4f69f0ee-8a9f-4789-a9d5-6fcd4406f8c0'),
				array('key' => 'SSN', 'name' => 'SSN', 'active' => 1, 'guid' => '59086c0f-6666-4ac4-8008-e199e9da1310'),
				array('key' => 'EIN', 'name' => 'EIN', 'active' => 1, 'guid' => 'd30e11a0-8600-414d-847f-ab69061e2c62'),
				array('key' => 'NPI', 'name' => 'NPI', 'active' => 1, 'guid' => '2e2f5558-83bb-421d-be62-d1e5aaf1bb95'),
				array('key' => 'UPIN', 'name' => 'UPIN', 'active' => 1, 'guid' => '116ec1bf-01bc-4f60-b11a-b17432a802c1'),
				array('key' => 'OTHER_MRN', 'name' => 'Other MRN', 'active' => 1, 'guid' => '3ed75316-99b8-4dbf-916d-95184a890260'),
				array('key' => 'DL', 'name' => 'DL', 'active' => 1, 'guid' => '1063b140-3759-4cb5-8e50-a0bc19d59ef7'),
			);


			$level = array();
			$level['guid'] = '8c200e66-f97e-40e9-9e39-f102ad2c6c31';
			$level['key'] = $key;
			$level['name'] = $name;
			$level['category'] = 'System';
			$level['active'] = 1;
			$level['data'] = $enums;

			$data = array($level);

			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	protected static function _saveEnumeration($data,$parentId=0) {
		$enumerationsClosure = new EnumerationsClosure();
		foreach ($data as $item) {
			$item['key'] = strtoupper($item['key']); // make sure keys are all UPPERCASE
			$enumerationId = $enumerationsClosure->insertEnumeration($item,$parentId);
			if (isset($item['data'])) {
				self::_saveEnumeration($item['data'],$enumerationId);
			}
		}
	}

	public static function enumerationToJson($name) {
		$enumeration = new Enumeration();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		return $enumerationIterator->toJsonArray('enumerationId',array('name'));
	}

}
