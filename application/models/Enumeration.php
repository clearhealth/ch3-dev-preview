<?php
/*****************************************************************************
*       Enumeration.php
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


class Enumeration extends WebVista_Model_ORM implements NSDRMethods {

	protected $enumerationId;
	protected $guid;
	protected $name;
	protected $key;
	protected $active;
	protected $category;
	protected $ormClass;
	protected $ormId;
	protected $ormEditMethod;

	protected $_table = "enumerations";
	protected $_primaryKeys = array('enumerationId');

	protected $_context = '*';
	protected $_alias = null;

	public function __construct($context='*',$alias=null) {
		$this->_context = $context;
		$this->_alias = $alias;
		parent::__construct();
	}

	public function populateByGuid($guid = null) {
		if ($guid === null) {
			$guid = $this->guid;
		}
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where('guid = ?',$guid);
		$retval = $this->populateWithSql($dbSelect->__toString());
		$this->postPopulate();
		return $retval;
	}

	public function populateByUniqueName($name) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from(array('e'=>$this->_table))
			       ->join(array('ec'=>'enumerationsClosure'),'e.enumerationId = ec.ancestor')
			       ->where('name = ?',$name)
			       ->where('enumerationId = descendant')
			       ->where('depth = 0');
		$retval = $this->populateWithSql($dbSelect->__toString());
		$this->postPopulate();
		return $retval;
	}

	public function nsdrPersist($tthis,$context,$data) {
	}

	public function nsdrPopulate($tthis,$context,$data) {
		$ret = '';
		if (preg_match('/^com\.clearhealth\.enumerations\.(.*)$/',$this->_alias,$matches)) {
			$name = $matches[1];
			$enumeration = new self();
			$enumeration->populateByFilter('key',$name);
			$enumerationsClosure = new EnumerationsClosure();
			$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
			$data = array();
			foreach ($enumerationIterator as $enum) {
				if ($this->_context != '*' && $this->_context == $enum->key) {
					$data = $enum->toArray();
					break;
				}
				$data[] = $enum->toArray();
			}
			$ret = $data;
		}
		return $ret;
	}

	public function nsdrMostRecent($tthis,$context,$data) {
	}

	static public function getIterByEnumerationId($enumerationId) {
		$enumerationId = (int) $enumerationId;
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$enumSelect = $db->select()
			->from($enumeration->_table)
			->where('parentId = ' . $enumerationId);
		$iter = $enumeration->getIterator($enumSelect);
		return $iter;
		
	}

	static public function getIterByEnumerationName($name) {
                $enumeration = new self();
                $db = Zend_Registry::get('dbAdapter');
                $enumSelect = $db->select()
                        ->from($enumeration->_table)
                        ->where('parentId = (select enumerationId from enumerations where name=' . $db->quote($name) .')');
                $iter = $enumeration->getIterator($enumSelect);
                return $iter;

        }
	static public function getEnumArray($name,$key='key',$value='name') {
                //$iter = self::getIterByEnumerationName($name);
                //return $iter->toArray($key, $value);

		$enumeration = new self();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		$ret = array();
		foreach ($enumerationIterator as $enumeration) {
			$ret[$enumeration->$key] = $enumeration->$value;
		}
		return $ret;
        }

	/**
	 * Get Enumeration Iterator by distinct category
	 *
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByDistinctCategories() {
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($enumeration->_table)
			       ->where("category != ''")
			       ->group("category");
		return $enumeration->getIterator($dbSelect);
	}

	/**
	 * Get Enumeration Iterator by distinct name given an enumerationId of category
	 *
	 * @param int $enumerationId Enumeration ID of category
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByDistinctNames($enumerationId) {
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$innerDbSelect = $db->select()->from($enumeration->_table,"category")
				    ->where('enumerationId = ?',(int)$enumerationId);
		$dbSelect = $db->select()->from(array('e1'=>$enumeration->_table))
			       ->joinLeft(array('e2'=>$enumeration->_table),'e2.category = e1.category',array())
			       ->where("e2.category = ({$innerDbSelect})")
			       ->group('e1.name');
		//trigger_error($dbSelect,E_USER_NOTICE);
		return $enumeration->getIterator($dbSelect);
	}


	/**
	 * Get Enumeration Iterator by name given an enumeration's name
	 *
	 * @param string $name Enumeration's name
	 * @return WebVista_Model_ORMIterator
	 * @access public static
	 */
	public static function getIterByName($name) {
		$name = preg_replace('/[^a-z_0-9-]/i','',$name);
		$enumeration = new self();
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()->from($enumeration->_table)
			       ->where('name = ?',$name);
		//trigger_error($dbSelect,E_USER_NOTICE);
		return $enumeration->getIterator($dbSelect);
	}

	public function populateByEnumerationName($name) {
		return $this->populateByFilter('name',$name);
	}

	public function populateByFilter($key,$val) {
		$db = Zend_Registry::get('dbAdapter');
		$dbSelect = $db->select()
			       ->from($this->_table)
			       ->where("`{$key}` = ?",$val);
		$retval = $this->populateWithSql($dbSelect->__toString());
		$this->postPopulate();
		return $retval;
	}

	public static function generateTestData($force = false) {
		self::generateContactPreferencesEnum($force);
		self::generateImmunizationPreferencesEnum($force);
		self::generateTeamPreferencesEnum($force);
		self::generateHSAPreferencesEnum($force);
		self::generateReasonPreferencesEnum($force);
		self::generateProcedurePreferencesEnum($force);
		self::generateEducationPreferencesEnum($force);
		self::generatePatientEducationPreferencesEnum($force);
		self::generateEducationTopicPreferencesEnum($force);
		self::generateEducationLevelPreferencesEnum($force);
		self::generatePatientExamPreferencesEnum($force);
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
	}

	public static function generateDemographicsPreferencesEnum($force = true) {
		$ret = false;
		do {
			$name = 'Demographics';
			$key = 'DEMOGRAPH';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'height' => array('key' => 'HT', 'name' => 'Height', 'active' => 1, 'data' => array(
					'inch' => array('key' => 'IN', 'name' => 'Inches', 'active' => 1),
					'cm' => array('key' => 'CM', 'name' => 'Centimeter', 'active' => 0),
				)),
				'weight' => array('key' => 'WT', 'name' => 'Weight', 'active' => 1, 'data' => array(
					'pound' => array('key' => 'LB', 'name' => 'Pounds', 'active' => 1),
					'kg' => array('key' => 'KG', 'name' => 'Kilograms', 'active' => 0),
				)),
				'temperature' => array('key' => 'TEMP', 'name' => 'Temperature', 'active' => 1, 'data' => array(
					'fahrenheit' => array('key' => 'F', 'name' => 'Fahrenheit', 'active' => 1),
					'celcius' => array('key' => 'C', 'name' => 'Celcius', 'active' => 0),
				)),
				'marital' => array('key' => 'MSTATUS', 'name' => 'Marital Status', 'active' => 1, 'data' => array(
					'accompanied' => array('key' => 'ACCOMP', 'name' => 'Accompanied', 'active' => 1),
					'divorced' => array('key' => 'DIVORCED', 'name' => 'Divorced', 'active' => 1),
					'married' => array('key' => 'MARRIED', 'name' => 'Married', 'active' => 1),
					'notspec' => array('key' => 'NOTSPEC', 'name' => 'Not Specified', 'active' => 1),
					'separated' => array('key' => 'SEPARATED', 'name' => 'Separated', 'active' => 1),
					'single' => array('key' => 'SINGLE', 'name' => 'Single', 'active' => 1),
					'unknown' => array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1),
					'widowed' => array('key' => 'WIDOWED', 'name' => 'Widowed', 'active' => 1),
				)),
				'confidentiality' => array('key' => 'CONFIDENT', 'name' => 'Confidentiality', 'active' => 1, 'data' => array(
					'nosr' => array('key' => 'NOSR', 'name' => 'No Special Restrictions', 'active' => 1),
					'basiconfi' => array('key' => 'BASICCONFI', 'name' => 'Basic Confidentiality', 'active' => 1),
					'familyPlanning' => array('key' => 'FAMILYPLAN', 'name' => 'Family Planning', 'active' => 1),
					'diseaseCon' => array('key' => 'DISEASECON', 'name' => 'Disease Confidentiality', 'active' => 1),
					'diseaseFPC' => array('key' => 'DISEASEFPC', 'name' => 'Disease and Family Planning Confidentiality', 'active' => 1),
					'extremeCon' => array('key' => 'EXTREMECON', 'name' => 'Extreme Confidentiality', 'active' => 1),
				)),
				'gender' => array('key' => 'G', 'name' => 'Gender', 'active' => 1, 'data' => array(
					'male' => array('key' => 'M', 'name' => 'Male', 'active' => 1),
					'female' => array('key' => 'F', 'name' => 'Female', 'active' => 1),
					'unknown' => array('key' => 'U', 'name' => 'Unknown', 'active' => 1),
				)),
			);

			$level = array();
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enumCountriesList = array(
				'AFG' => 'Afghanistan',
				'ALB' => 'Albania',
				'DZA' => 'Algeria',
				'ASM' => 'American Samoa',
				'AND' => 'Andorra',
				'AGO' => 'Angola',
				'AIA' => 'Anguilla',
				'ATA' => 'Antarctica',
				'ATG' => 'Antigua and Barbuda',
				'ARG' => 'Argentina',
				'ARM' => 'Armenia',
				'ABW' => 'Aruba',
				'AUS' => 'Australia',
				'AUT' => 'Austria',
				'AZE' => 'Azerbaijan',
				'BHS' => 'Bahamas',
				'BHR' => 'Bahrain',
				'BGD' => 'Bangladesh',
				'BRB' => 'Barbados',
				'BLR' => 'Belarus',
				'BEL' => 'Belgium',
				'BLZ' => 'Belize',
				'BEN' => 'Benin',
				'BMU' => 'Bermuda',
				'BTN' => 'Bhutan',
				'BOL' => 'Bolivia',
				'BIH' => 'Bosnia and Herzegovina',
				'BWA' => 'Botswana',
				'BVT' => 'Bouvet Island',
				'BRA' => 'Brazil',
				'IOT' => 'British Indian Ocean Territory',
				'BRN' => 'Brunei Darussalam',
				'BGR' => 'Bulgaria',
				'BFA' => 'Burkina Faso',
				'BDI' => 'Burundi',
				'KHM' => 'Cambodia',
				'CMR' => 'Cameroon',
				'CAN' => 'Canada',
				'CPV' => 'Cape Verde',
				'CYM' => 'Cayman Islands',
				'CAF' => 'Central African Republic',
				'TCD' => 'Chad',
				'CHL' => 'Chile',
				'CHN' => 'China',
				'CXR' => 'Christmas Island',
				'CCK' => 'Cocos (Keeling) Islands',
				'COL' => 'Colombia',
				'COM' => 'Comoros',
				'COG' => 'Congo',
				'COD' => 'Congo, the Democratic Republic of the',
				'COK' => 'Cook Islands',
				'CRI' => 'Costa Rica',
				'CIV' => 'Cote D\'Ivoire',
				'HRV' => 'Croatia',
				'CUB' => 'Cuba',
				'CYP' => 'Cyprus',
				'CZE' => 'Czech Republic',
				'DNK' => 'Denmark',
				'DJI' => 'Djibouti',
				'DMA' => 'Dominica',
				'DOM' => 'Dominican Republic',
				'ECU' => 'Ecuador',
				'EGY' => 'Egypt',
				'SLV' => 'El Salvador',
				'GNQ' => 'Equatorial Guinea',
				'ERI' => 'Eritrea',
				'EST' => 'Estonia',
				'ETH' => 'Ethiopia',
				'FLK' => 'Falkland Islands (Malvinas)',
				'FRO' => 'Faroe Islands',
				'FJI' => 'Fiji',
				'FIN' => 'Finland',
				'FRA' => 'France',
				'GUF' => 'French Guiana',
				'PYF' => 'French Polynesia',
				'ATF' => 'French Southern Territories',
				'GAB' => 'Gabon',
				'GMB' => 'Gambia',
				'GEO' => 'Georgia',
				'DEU' => 'Germany',
				'GHA' => 'Ghana',
				'GIB' => 'Gibraltar',
				'GRC' => 'Greece',
				'GRL' => 'Greenland',
				'GRD' => 'Grenada',
				'GLP' => 'Guadeloupe',
				'GUM' => 'Guam',
				'GTM' => 'Guatemala',
				'GIN' => 'Guinea',
				'GNB' => 'Guinea-Bissau',
				'GUY' => 'Guyana',
				'HTI' => 'Haiti',
				'HMD' => 'Heard Island and Mcdonald Islands',
				'VAT' => 'Holy See (Vatican City State)',
				'HND' => 'Honduras',
				'HKG' => 'Hong Kong',
				'HUN' => 'Hungary',
				'ISL' => 'Iceland',
				'IND' => 'India',
				'IDN' => 'Indonesia',
				'IRN' => 'Iran, Islamic Republic of',
				'IRQ' => 'Iraq',
				'IRL' => 'Ireland',
				'ISR' => 'Israel',
				'ITA' => 'Italy',
				'JAM' => 'Jamaica',
				'JPN' => 'Japan',
				'JOR' => 'Jordan',
				'KAZ' => 'Kazakhstan',
				'KEN' => 'Kenya',
				'KIR' => 'Kiribati',
				'PRK' => 'Korea, Democratic People\'s Republic of',
				'KOR' => 'Korea, Republic of',
				'KWT' => 'Kuwait',
				'KGZ' => 'Kyrgyzstan',
				'LAO' => 'Lao People\'s Democratic Republic',
				'LVA' => 'Latvia',
				'LBN' => 'Lebanon',
				'LSO' => 'Lesotho',
				'LBR' => 'Liberia',
				'LBY' => 'Libyan Arab Jamahiriya',
				'LIE' => 'Liechtenstein',
				'LTU' => 'Lithuania',
				'LUX' => 'Luxembourg',
				'MAC' => 'Macao',
				'MKD' => 'Macedonia, the Former Yugoslav Republic of',
				'MDG' => 'Madagascar',
				'MWI' => 'Malawi',
				'MYS' => 'Malaysia',
				'MDV' => 'Maldives',
				'MLI' => 'Mali',
				'MLT' => 'Malta',
				'MHL' => 'Marshall Islands',
				'MTQ' => 'Martinique',
				'MRT' => 'Mauritania',
				'MUS' => 'Mauritius',
				'MYT' => 'Mayotte',
				'MEX' => 'Mexico',
				'FSM' => 'Micronesia, Federated States of',
				'MDA' => 'Moldova, Republic of',
				'MCO' => 'Monaco',
				'MNG' => 'Mongolia',
				'MSR' => 'Montserrat',
				'MAR' => 'Morocco',
				'MOZ' => 'Mozambique',
				'MMR' => 'Myanmar',
				'NAM' => 'Namibia',
				'NRU' => 'Nauru',
				'NPL' => 'Nepal',
				'NLD' => 'Netherlands',
				'ANT' => 'Netherlands Antilles',
				'NCL' => 'New Caledonia',
				'NZL' => 'New Zealand',
				'NIC' => 'Nicaragua',
				'NER' => 'Niger',
				'NGA' => 'Nigeria',
				'NIU' => 'Niue',
				'NFK' => 'Norfolk Island',
				'MNP' => 'Northern Mariana Islands',
				'NOR' => 'Norway',
				'OMN' => 'Oman',
				'PAK' => 'Pakistan',
				'PLW' => 'Palau',
				'PSE' => 'Palestinian Territory, Occupied',
				'PAN' => 'Panama',
				'PNG' => 'Papua New Guinea',
				'PRY' => 'Paraguay',
				'PER' => 'Peru',
				'PHL' => 'Philippines',
				'PCN' => 'Pitcairn',
				'POL' => 'Poland',
				'PRT' => 'Portugal',
				'PRI' => 'Puerto Rico',
				'QAT' => 'Qatar',
				'REU' => 'Reunion',
				'ROM' => 'Romania',
				'RUS' => 'Russian Federation',
				'RWA' => 'Rwanda',
				'SHN' => 'Saint Helena',
				'KNA' => 'Saint Kitts and Nevis',
				'LCA' => 'Saint Lucia',
				'SPM' => 'Saint Pierre and Miquelon',
				'VCT' => 'Saint Vincent and the Grenadines',
				'WSM' => 'Samoa',
				'SMR' => 'San Marino',
				'STP' => 'Sao Tome and Principe',
				'SAU' => 'Saudi Arabia',
				'SEN' => 'Senegal',
				'SCG' => 'Serbia and Montenegro',
				'SYC' => 'Seychelles',
				'SLE' => 'Sierra Leone',
				'SGP' => 'Singapore',
				'SVK' => 'Slovakia',
				'SVN' => 'Slovenia',
				'SLB' => 'Solomon Islands',
				'SOM' => 'Somalia',
				'ZAF' => 'South Africa',
				'SGS' => 'South Georgia and the South Sandwich Islands',
				'ESP' => 'Spain',
				'LKA' => 'Sri Lanka',
				'SDN' => 'Sudan',
				'SUR' => 'Suriname',
				'SJM' => 'Svalbard and Jan Mayen',
				'SWZ' => 'Swaziland',
				'SWE' => 'Sweden',
				'CHE' => 'Switzerland',
				'SYR' => 'Syrian Arab Republic',
				'TWN' => 'Taiwan, Province of China',
				'TJK' => 'Tajikistan',
				'TZA' => 'Tanzania, United Republic of',
				'THA' => 'Thailand',
				'TLS' => 'Timor-Leste',
				'TGO' => 'Togo',
				'TKL' => 'Tokelau',
				'TON' => 'Tonga',
				'TTO' => 'Trinidad and Tobago',
				'TUN' => 'Tunisia',
				'TUR' => 'Turkey',
				'TKM' => 'Turkmenistan',
				'TCA' => 'Turks and Caicos Islands',
				'TUV' => 'Tuvalu',
				'UGA' => 'Uganda',
				'UKR' => 'Ukraine',
				'ARE' => 'United Arab Emirates',
				'GBR' => 'United Kingdom',
				'USA' => 'United States',
				'UMI' => 'United States Minor Outlying Islands',
				'URY' => 'Uruguay',
				'UZB' => 'Uzbekistan',
				'VUT' => 'Vanuatu',
				'VEN' => 'Venezuela',
				'VNM' => 'Viet Nam',
				'VGB' => 'Virgin Islands, British',
				'VIR' => 'Virgin Islands, U.s.',
				'WLF' => 'Wallis and Futuna',
				'ESH' => 'Western Sahara',
				'YEM' => 'Yemen',
				'ZMB' => 'Zambia',
				'ZWE' => 'Zimbabwe',
			);			

			$enumStatesList = array(
				'AA' => 'Armed Forces Americas (except Canada)',
				'AE' => 'Armed Forces Africa',
				'AE' => 'Armed Forces Canada',
				'AE' => 'Armed Forces Europe',
				'AE' => 'Armed Forces Middle East',
				'AK' => 'Alaska',
				'AL' => 'Alabama',
				'AP' => 'Armed Forces Pacific',
				'AR' => 'Arkansas',
				'AS' => 'American Samoa',
				'AZ' => 'Arizona',
				'CA' => 'California',
				'CO' => 'Colorado',
				'CT' => 'Connecticut',
				'DC' => 'District of Columbia',
				'DE' => 'Delaware',
				'FL' => 'Florida',
				'FM' => 'Federated States of Micronesia',
				'GA' => 'Georgia',
				'GU' => 'Guam',
				'HI' => 'Hawaii',
				'IA' => 'Iowa',
				'ID' => 'Idaho',
				'IL' => 'Illinois',
				'IN' => 'Indiana',
				'KS' => 'Kansas',
				'KY' => 'Kentucky',
				'LA' => 'Louisiana',
				'MA' => 'Massachusetts',
				'MD' => 'Maryland',
				'ME' => 'Maine',
				'MH' => 'Marshall Islands',
				'MI' => 'Michigan',
				'MN' => 'Minnesota',
				'MO' => 'Missouri',
				'MP' => 'Northern Mariana Islands',
				'MS' => 'Mississippi',
				'MT' => 'Montana',
				'NC' => 'North Carolina',
				'ND' => 'North Dakota',
				'NE' => 'Nebraska',
				'NH' => 'New Hampshire',
				'NJ' => 'New Jersey',
				'NM' => 'New Mexico',
				'NV' => 'Nevada',
				'NY' => 'New York',
				'OH' => 'Ohio',
				'OK' => 'Oklahoma',
				'OR' => 'Oregon',
				'PA' => 'Pennsylvania',
				'PR' => 'Puerto Rico',
				'PW' => 'Palau',
				'RI' => 'Rhode Island',
				'SC' => 'South Carolina',
				'SD' => 'South Dakota',
				'TN' => 'Tennessee',
				'TX' => 'Texas',
				'UT' => 'Utah',
				'VA' => 'Virginia',
				'VI' => 'Virgin Islands',
				'VT' => 'Vermont',
				'WA' => 'Washington',
				'WI' => 'Wisconsin',
				'WV' => 'West Virginia',
				'WY' => 'Wyoming',
			);

			// countries
			$level1 = array();
			$level1['key'] = 'COUNTRIES';
			$level1['name']	= 'Countries';
			foreach ($enumCountriesList as $k=>$v) {
				$tmp = array();
				$tmp['key'] = $k;
				$tmp['name'] = $v;
				$tmp['active'] = 1;
				$level1['data'][] = $tmp;
			}

			// states
			$level2 = array();
			$level2['key'] = 'STATES';
			$level2['name']	= 'States';
			foreach ($enumStatesList as $k=>$v) {
				$tmp = array();
				$tmp['key'] = $k;
				$tmp['name'] = $v;
				$tmp['active'] = 1;
				$level2['data'][] = $tmp;
			}

			// top level
			$topLevel = array();
			$topLevel['key'] = $key;
			$topLevel['name'] = $name;

			$topLevel['category'] = 'System';
			$topLevel['active'] = 1;
			$topLevel['data'] = array();
			$topLevel['data'][] = $level1;
			$topLevel['data'][] = $level2;

			$data = array();
			$data[] = $topLevel;

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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'appointment' => array('key' => 'APP_REASON', 'name' => AppointmentTemplate::ENUM_PARENT_NAME, 'active' => 1, 'data' => array(
					'provider' => array('key' => 'PROVIDER', 'name' => 'Provider', 'active' => 1),
					'specialist' => array('key' => 'SPECIALIST', 'name' => 'Specialist', 'active' => 1),
					'medicalPhone' => array('key' => 'MEDPHONE', 'name' => 'Medical Phone', 'active' => 1),
					'medicalPU' => array('key' => 'MEDPU', 'name' => 'Medication PU', 'active' => 1),
					'education' => array('key' => 'EDUCATION', 'name' => 'Education', 'active' => 1),
					'eligibility' => array('key' => 'ELIG', 'name' => 'Eligibility', 'active' => 1),
					'blockedTime' => array('key' => 'BLOCKTIME', 'name' => 'Blocked Time', 'active' => 1),
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
			$topLevel = array();
			$topLevel['key'] = $key;
			$topLevel['name'] = $name;
			$topLevel['category'] = 'System';
			$topLevel['active'] = 1;
			$topLevel['data'] = $enums;

			$data = array();
			$data[] = $topLevel;

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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$symptomsList = array(
				//'CONFUSION','ITCHING,WATERING EYES','HYPOTENSION','DROWSINESS','CHEST PAIN',
				//'DIARRHEA','HIVES','DRY MOUTH','CHEST PAIN','DIARRHEA',
				//'HIVES','DRY MOUTH','CHILLS','RASH',
				'AGITATION','AGRANULOCYTOSIS','ALOPECIA','ANAPHYLAXIS','ANEMIA',
				'ANOREXIA','ANXIETY','APNEA','APPETITE,INCREASED','ARRHYTHMIA',
				'ASTHENIA','ASTHMA','ATAXIA','ATHETOSIS','BRACHYCARDIA',
				'BREAST ENGORGEMENT','BRONCHOSPASM','CARDIAC ARREST','CHEST PAIN','CHILLS',
				'COMA','CONFUSION','CONGESTION,NASAL','CONJUNCTIVAL CONGESTION','CONSTIPATION',
				'COUGHING','DEAFNESS','DELERIUM','DELUSION','DEPRESSION',
				'DEPRESSION,MENTAL','DEPRESSION,POSTICTAL','DERMATITIS','DERMATITIS,CONTACT','DERMATITIS,PHOTOALLERGENIC',
				'DIAPHORESIS','DIARRHEA','DIPLOPIA','DISTURBED COORDINATION','DIZZINESS',
				'DREAMING,INCREASED','DROWSINESS','DRY MOUTH','DRY NOSE','DRY THROAT',
				'DYSPNEA','DYSURIA','ECCHYMOSIS','ECG CHANGES','ECZEMA',
				'EDEMA','EPIGASTRIC DISTRESS','EPISTAXIS','ERYTHEMA','EUPHORIA',
				'EXCITATION','EXTRASYSTOLE','FACE FLUSHED','FACIAL DYSKINESIA','FAINTNESS',
				'FATIGUE','FEELING OF WARMTH','FEVER','GALACTORRHEA','GENERALIZED RASH',
				'GI REACTION','GLAUCOMA','GYNECOMASTIA','HALLUCINATIONS','HEADACHE',
				'HEART BLOCK','HEMATURIA','HEMOGLOBIN,INCREASED','HIVES','HYPERSENSITIVITY',
				'HYPERTENSION','HYPOTENSION','IMPAIRMENT OF ERECTION','IMPOTENCE','INAPPROPRIATE PENILE ERECTION',
				'INSOMNIA','IRRITABILITY','ITCHING,WATERING EYES','JUNCTIONAL RHYTHM','LABYRINTHITIS,ACUTE',
				'LACRIMATION','LDH,INCREASED','LETHARGY','LEUKOCYTE COUNT,DECREASED','LIBIDO,DECREASED',
				'LIBIDO,INCREASED','MIOSIS','MYOCARDIAL INFARCTION','NAUSEA,VOMITING','NERVOUSNESS,AGITATION',
				'NEUTROPHIL COUNT,DECREASED','NIGHTMARES','OPTIC ATROPHY','ORGASM,INHIBITED','ORONASALPHARYNGEAL IRRITATION',
				'PAIN,JOINT','PALPITATIONS','PANCYTOPENIA','PARESTHESIA','PARKINSONIAN-LIKE SYNDROME',
				'PHOTOSENSITIVITY','POSSIBLE REACTION','PRIAPISM','PROLONGED PENILE ERECTION','PRURITIS',
				'PTOSIS','PURPURA','RALES','RASH','RASH,PAPULAR',
				'RESPIRATORY DISTRESS','RETROGRADE EJACULATION','RHINITIS','RHINORRHEA','RHONCHUS',
				'S-T CHANGES,TRANSIENT','SEIZURES','SEIZURES,TONIC-CLONIC','SELF-DEPRECATION','SEVERE RASH',
				'SHORTNESS OF BREATH','SINUS BRACHYCARDIA','SNEEZING','SOMNOLENCE','SPEECH DISORDER',
				'SWELLING (NON-SPECIFIC)','SWELLING-EYES','SWELLING-LIPS','SWELLING-THROAT','SYNCOPE',
				'TACHYCARDIA','THROMBOCYTOPENIA','TREMORS','URINARY FLOW,DELAYED','URINARY FREQUENCY',
				'URINARY FREQUENCY,INCREASED','URINARY RETENTION','URTICARIA','UVEITIS','VERTIGO',
				'VISION,BLURRED','VISUAL DISTURBANCES','VOMITING','WEAKNESS','WEIGHT GAIN',
				'WHEEZING',
			);

			$enums = array(
				'allergies' => array('key' => 'ALLERGIES', 'name' => 'Allergies', 'active' => 1, 'data' => array(
					'symptom' => array('key' => 'SYMPTOM', 'name' => PatientAllergy::ENUM_SYMPTOM_PARENT_NAME, 'active' => 1, 'data' => array()),
					'severity' => array('key' => 'SEVERITY', 'name' => PatientAllergy::ENUM_SEVERITY_PARENT_NAME, 'active' => 1, 'data' => array(
						'mild' => array('key' => 'MILD', 'name' => 'Mild', 'active' => 1),
						'moderate' => array('key' => 'MODERATE', 'name' => 'Moderate', 'active' => 1),
					)),
					'reactionType' => array('key' => 'REACTYPE', 'name' => PatientAllergy::ENUM_REACTION_TYPE_PARENT_NAME, 'active' => 1, 'data' => array(
						'allergy' => array('key' => 'ALLERGY', 'name' => 'Allergy', 'active' => 1),
						'pharma' => array('key' => 'PHARMA', 'name' => 'Pharmacological', 'active' => 1),
						'unknown' => array('key' => 'UNKNOWN', 'name' => 'Unknown', 'active' => 1),
						'drugClass' => array('key' => 'DRUGCLASS', 'name' => 'Drug Class Allergy', 'active' => 1),
						'specDrug' => array('key' => 'SPECDRUG', 'name' => 'Specific Drug Allergy', 'active' => 1),
					)),
				)),
			);
			// symptoms
			$ctr = 1;
			foreach ($symptomsList as $symptom) {
				$tmp = array();
				$tmp['key'] = $ctr++;
				$tmp['name'] = $symptom;
				$tmp['active'] = 1;
				$enums['allergies']['symptom']['data'][] = $tmp;
			}

			$level = array();
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

	public static function generateContactPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Contact Preferences';
			$key = 'CONTACT';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'PT';
			$level1['name'] = 'Phone Types';
			$level1['active'] = 1;

			$level11 = array();
			$level11['key'] = 'HOME';
			$level11['name'] = 'Home';
			$level11['active'] = 1;

			$level12 = array();
			$level12['key'] = 'WORK';
			$level12['name'] = 'Work';
			$level12['active'] = 1;

			$level13 = array();
			$level13['key'] = 'BILL';
			$level13['name'] = 'Billing';
			$level13['active'] = 1;

			$level14 = array();
			$level14['key'] = 'MOB';
			$level14['name'] = 'Mobile';
			$level14['active'] = 1;

			$level15 = array();
			$level15['key'] = 'EMER';
			$level15['name'] = 'Emergency';
			$level15['active'] = 1;

			$level16 = array();
			$level16['key'] = 'FAX';
			$level16['name'] = 'Fax';
			$level16['active'] = 1;

			$level17 = array();
			$level17['key'] = 'EMPL';
			$level17['name'] = 'Employer';
			$level17['active'] = 1;

			$level1['data'] = array();
			$level1['data'][] = $level11;
			$level1['data'][] = $level12;
			$level1['data'][] = $level13;
			$level1['data'][] = $level14;
			$level1['data'][] = $level15;
			$level1['data'][] = $level16;
			$level1['data'][] = $level17;

			$level2 = array();
			$level2['key'] = 'AT';
			$level2['name'] = 'Address Types';
			$level2['active'] = 1;

			$level21 = array();
			$level21['key'] = 'HOME';
			$level21['name'] = 'Home';
			$level21['active'] = 1;

			$level22 = array();
			$level22['key'] = 'EMPL';
			$level22['name'] = 'Employer';
			$level22['active'] = 1;

			$level23 = array();
			$level23['key'] = 'BILL';
			$level23['name'] = 'Billing';
			$level23['active'] = 1;

			$level24 = array();
			$level24['key'] = 'OT';
			$level24['name'] = 'Other';
			$level24['active'] = 1;

			$level25 = array();
			$level25['key'] = 'MAIN';
			$level25['name'] = 'Main';
			$level25['active'] = 1;

			$level26 = array();
			$level26['key'] = 'SEC';
			$level26['name'] = 'Secondary';
			$level26['active'] = 1;

			$level2['data'] = array();
			$level2['data'][] = $level21;
			$level2['data'][] = $level22;
			$level2['data'][] = $level23;
			$level2['data'][] = $level24;
			$level2['data'][] = $level25;
			$level2['data'][] = $level26;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array();
			$data[] = $level0;
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enums = array(
				'file' => array('key' => 'FILE', 'name' => 'File', 'active' => 1, 'data' => array(
					'addPatient' => array('key' => 'ADDPATIENT', 'name' => 'Add Patient', 'active' => 1),
					'selectPatient' => array('key' => 'SELPATIENT', 'name' => 'Select Patient', 'active' => 1),
					'reviewSignChanges' => array('key' => 'RSC', 'name' => 'Review / Sign Changes', 'active' => 1),
					'changePassword' => array('key' => 'CHANGEPW', 'name' => 'Change Password', 'active' => 1),
					'editSigningKey' => array('key' => 'SIGNINGKEY', 'name' => 'Edit Signing Key', 'active' => 1),
					'myPreferences' => array('key' => 'MYPREF', 'name' => 'My Preferences', 'active' => 1),
					'quit' => array('key' => 'QUIT', 'name' => 'Quit', 'active' => 1),
				)),
				'action' => array('key' => 'ACTION', 'name' => 'Action', 'active' => 1, 'data' => array(
					'addVitals' => array('key' => 'ADDVITALS', 'name' => 'Add Vitals', 'active' => 1),
					'print' => array('key' => 'PRINT', 'name' => 'Print', 'active' => 1, 'data' => array(
						'flowSheet' => array('key' => 'FLOWSHEET', 'name' => 'Flow Sheet', 'active' => 1),
					)),
					'manageSchedule' => array('key' => 'MANSCHED', 'name' => 'Manage Schedules', 'active' => 1),
				)),
			);

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['ormClass'] = 'MenuItem';
			$level0['ormEditMethod'] = 'ormEditMethod';
			$level0['data'] = $enums;

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);

			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);

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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();

			$enums = array(
				'series' => array('key' => 'series', 'name' => PatientImmunization::ENUM_SERIES_NAME, 'active' => 1, 'data' => array(
					array('key' => 'partComp', 'name' => 'Partially complete', 'active' => 1),
					array('key' => 'complete', 'name' => 'Complete', 'active' => 1),
					array('key' => 'booster', 'name' => 'Booster', 'active' => 1),
					array('key' => 'series1', 'name' => 'Series 1', 'active' => 1),
					array('key' => 'series2', 'name' => 'Series 2', 'active' => 1),
					array('key' => 'series3', 'name' => 'Series 3', 'active' => 1),
					array('key' => 'series4', 'name' => 'Series 4', 'active' => 1),
					array('key' => 'series5', 'name' => 'Series 5', 'active' => 1),
					array('key' => 'series6', 'name' => 'Series 6', 'active' => 1),
					array('key' => 'series7', 'name' => 'Series 7', 'active' => 1),
					array('key' => 'series8', 'name' => 'Series 8', 'active' => 1),
				)),
				'section' => array('key' => 'section', 'name' => PatientImmunization::ENUM_SECTION_NAME, 'active' => 1, 'data' => array(
					'other' => array('key' => 'other', 'name' => PatientImmunization::ENUM_SECTION_OTHER_NAME, 'active' => 1),
					'common' => array('key' => 'common', 'name' => PatientImmunization::ENUM_SECTION_COMMON_NAME, 'active' => 1),
				)),
				'reaction' => array('key' => 'reaction', 'name' => PatientImmunization::ENUM_REACTION_NAME, 'active' => 1, 'data' => array(
					array('key' => 'FV', 'name' => 'Fever', 'active' => 1),
					array('key' => 'IR', 'name' => 'Irritability', 'active' => 1),
					array('key' => 'LRS', 'name' => 'Local reaction or swelling', 'active' => 1),
					array('key' => 'VM', 'name' => 'Vomiting', 'active' => 1),
				)),
				'bodySite' => array('key' => 'bodySite', 'name' => PatientImmunization::ENUM_BODY_SITE_NAME, 'active' => 1, 'data' => array(
					array('key' => 'BE', 'name' => 'Bilateral Ears', 'active' => 1),
					array('key' => 'LVL', 'name' => 'Left Vastus Lateralis', 'active' => 1),
					array('key' => 'OU', 'name' => 'Bilateral Eyes', 'active' => 1),
					array('key' => 'NB', 'name' => 'Nebulized', 'active' => 1),
					array('key' => 'BN', 'name' => 'Bilateral Nares', 'active' => 1),
					array('key' => 'PA', 'name' => 'Perianal', 'active' => 1),
					array('key' => 'BU', 'name' => 'Buttock', 'active' => 1),
					array('key' => 'PERIN', 'name' => 'Perineal', 'active' => 1),
					array('key' => 'CT', 'name' => 'Chest Tube', 'active' => 1),
					array('key' => 'RA', 'name' => 'Right Arm', 'active' => 1),
					array('key' => 'LA', 'name' => 'Left Arm', 'active' => 1),
					array('key' => 'RAC', 'name' => 'Right Anterior Chest', 'active' => 1),
					array('key' => 'LAC', 'name' => 'Left Anterior Chest', 'active' => 1),
					array('key' => 'RACF', 'name' => 'Right Antecubital Fossa', 'active' => 1),
					array('key' => 'LACF', 'name' => 'Left Antecubital Fossa', 'active' => 1),
					array('key' => 'RD', 'name' => 'Right Deltoid', 'active' => 1),
					array('key' => 'LD', 'name' => 'Left Deltoid', 'active' => 1),
					array('key' => 'RE', 'name' => 'Right Ear', 'active' => 1),
					array('key' => 'LE', 'name' => 'Left Ear', 'active' => 1),
					array('key' => 'REJ', 'name' => 'Right External Jugular', 'active' => 1),
					array('key' => 'LEJ', 'name' => 'Left External Jugular', 'active' => 1),
					array('key' => 'OD', 'name' => 'Right Eye', 'active' => 1),
					array('key' => 'OS', 'name' => 'Left Eye', 'active' => 1),
					array('key' => 'RF', 'name' => 'Right Foot', 'active' => 1),
					array('key' => 'LF', 'name' => 'Left Foot', 'active' => 1),
					array('key' => 'RG', 'name' => 'Right Gluteus Medius', 'active' => 1),
					array('key' => 'LG', 'name' => 'Left Gluteus Medius', 'active' => 1),
					array('key' => 'RH', 'name' => 'Right Hand', 'active' => 1),
					array('key' => 'LH', 'name' => 'Left Hand', 'active' => 1),
					array('key' => 'RIJ', 'name' => 'Right Internal Jugular', 'active' => 1),
					array('key' => 'LIJ', 'name' => 'Left Internal Jugular', 'active' => 1),
					array('key' => 'RLAQ', 'name' => 'Rt Lower Abd Quadrant', 'active' => 1),
					array('key' => 'LLAQ', 'name' => 'Left Lower Abd Quadrant', 'active' => 1),
					array('key' => 'RLFA', 'name' => 'Right Lower Forearm', 'active' => 1),
					array('key' => 'LLFA', 'name' => 'Left Lower Forearm', 'active' => 1),
					array('key' => 'RMFA', 'name' => 'Right Mid Forearm', 'active' => 1),
					array('key' => 'LMFA', 'name' => 'Left Mid Forearm', 'active' => 1),
					array('key' => 'RN', 'name' => 'Right Naris', 'active' => 1),
					array('key' => 'LN', 'name' => 'Left Naris', 'active' => 1),
					array('key' => 'RPC', 'name' => 'Right Posterior Chest', 'active' => 1),
					array('key' => 'LPC', 'name' => 'Left Posterior Chest', 'active' => 1),
					array('key' => 'RSC', 'name' => 'Right Subclavian', 'active' => 1),
					array('key' => 'LSC', 'name' => 'Left Subclavian', 'active' => 1),
					array('key' => 'RT', 'name' => 'Right Thigh', 'active' => 1),
					array('key' => 'LT', 'name' => 'Left Thigh', 'active' => 1),
					array('key' => 'RUA', 'name' => 'Right Upper Arm', 'active' => 1),
					array('key' => 'LUA', 'name' => 'Left Upper Arm', 'active' => 1),
					array('key' => 'RUAQ', 'name' => 'Right Upper Abd Quadrant', 'active' => 1),
					array('key' => 'LUAQ', 'name' => 'Left Upper Abd Quadrant', 'active' => 1),
					array('key' => 'RUFA', 'name' => 'Right Upper Forearm', 'active' => 1),
					array('key' => 'LUFA', 'name' => 'Left Upper Forearm', 'active' => 1),
					array('key' => 'RVL', 'name' => 'Right Vastus Lateralis', 'active' => 1),
					array('key' => 'LVG', 'name' => 'Left Ventragluteal', 'active' => 1),
					array('key' => 'RVG', 'name' => 'Right Ventragluteal', 'active' => 1),
				)),
				'adminRoute' => array('key' => 'adminRoute', 'name' => PatientImmunization::ENUM_ADMINISTRATION_ROUTE_NAME, 'active' => 1, 'data' => array(
					array('key' => 'AP', 'name' => 'Apply Externally', 'active' => 1),
					array('key' => 'MM', 'name' => 'Mucous Membrane', 'active' => 1),
					array('key' => 'B', 'name' => 'Buccal', 'active' => 1),
					array('key' => 'NS', 'name' => 'Nasal', 'active' => 1),
					array('key' => 'DT', 'name' => 'Dental', 'active' => 1),
					array('key' => 'NG', 'name' => 'Nasogastric', 'active' => 1),
					array('key' => 'EP', 'name' => 'Epidural', 'active' => 1),
					array('key' => 'NP', 'name' => 'Nasal Prongs', 'active' => 1),
					array('key' => 'ET', 'name' => 'Endotrachial Tube', 'active' => 1),
					array('key' => 'NT', 'name' => 'Nasotrachial Tube', 'active' => 1),
					array('key' => 'GTT', 'name' => 'Gastrostomy Tube', 'active' => 1),
					array('key' => 'OP', 'name' => 'Ophthalmic', 'active' => 1),
					array('key' => 'GU', 'name' => 'GU Irrigant', 'active' => 1),
					array('key' => 'OT', 'name' => 'Otic', 'active' => 1),
					array('key' => 'IMR', 'name' => 'Immerse (Soak) Body Part', 'active' => 1),
					array('key' => 'OTH', 'name' => 'Other/Miscellaneous', 'active' => 1),
					array('key' => 'IA', 'name' => 'Intra-arterial', 'active' => 1),
					array('key' => 'PF', 'name' => 'Perfusion', 'active' => 1),
					array('key' => 'IB', 'name' => 'Intrabursal', 'active' => 1),
					array('key' => 'PO', 'name' => 'Oral', 'active' => 1),
					array('key' => 'IC', 'name' => 'Intracardiac', 'active' => 1),
					array('key' => 'PR', 'name' => 'Rectal', 'active' => 1),
					array('key' => 'ICV', 'name' => 'Intracervical (uterus)', 'active' => 1),
					array('key' => 'RM', 'name' => 'Rebreather Mask', 'active' => 1),
					array('key' => 'ID', 'name' => 'Intradermal', 'active' => 1),
					array('key' => 'SD', 'name' => 'Soaked Dressing', 'active' => 1),
					array('key' => 'IH', 'name' => 'Inhalation', 'active' => 1),
					array('key' => 'SC', 'name' => 'Subcutaneous', 'active' => 1),
					array('key' => 'IHA', 'name' => 'Intrahepatic Artery', 'active' => 1),
					array('key' => 'SL', 'name' => 'Sublingual', 'active' => 1),
					array('key' => 'IM', 'name' => 'Intramuscular', 'active' => 1),
					array('key' => 'TP', 'name' => 'Topical', 'active' => 1),
					array('key' => 'IN', 'name' => 'Intranasal', 'active' => 1),
					array('key' => 'TRA', 'name' => 'Tracheostomy', 'active' => 1),
					array('key' => 'IO', 'name' => 'Intraocular', 'active' => 1),
					array('key' => 'TD', 'name' => 'Transdermal', 'active' => 1),
					array('key' => 'IP', 'name' => 'Intraperitoneal', 'active' => 1),
					array('key' => 'TL', 'name' => 'Translingual', 'active' => 1),
					array('key' => 'IS', 'name' => 'Intrasynovial', 'active' => 1),
					array('key' => 'UR', 'name' => 'Urethral', 'active' => 1),
					array('key' => 'IT', 'name' => 'Intrathecal', 'active' => 1),
					array('key' => 'VG', 'name' => 'Vaginal', 'active' => 1),
					array('key' => 'IU', 'name' => 'Intrauterine', 'active' => 1),
					array('key' => 'VM', 'name' => 'Ventimask', 'active' => 1),
					array('key' => 'IV', 'name' => 'Intravenous', 'active' => 1),
					array('key' => 'WND', 'name' => 'Wound', 'active' => 1),
					array('key' => 'MTH', 'name' => 'Mouth/Throat', 'active' => 1),
				)),
			);

			// below immunizations should be moved to Other
			$sectionOther = &$enums['section']['data']['other'];
			$sectionOther['data'] = array(); // section other

			$enumerationLevel2 = array();
			$enumerationLevel2[] = 'BCG';
			$enumerationLevel2[] = 'DT (pediatric)';
			$enumerationLevel2[] = 'DTaP';
			$enumerationLevel2[] = 'DTaP-Hep B-IPV';
			$enumerationLevel2[] = 'DTaP-Hib';
			$enumerationLevel2[] = 'DTaP-Hib-IPV';
			$enumerationLevel2[] = 'DTP';
			$enumerationLevel2[] = 'DTP-Hib';
			$enumerationLevel2[] = 'DTP-Hib-Hep B';
			$enumerationLevel2[] = 'Hep A-Hep B';
			$enumerationLevel2[] = 'Hep A, adult';
			$enumerationLevel2[] = 'Hep A, NOS';

			foreach ($enumerationLevel2 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = uniqid(rand());
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$sectionOther['data'][] = $tmp;
			}
			$level2 = array();
			$level2['key'] = uniqid(rand());
			$level2['name'] = 'Hep A, pediatric, NOS';
			$level2['active'] = 1;
			$level2['data'] = array();
			$enumerationLevel3 = array();
			$enumerationLevel3[] = 'Hep A, ped/adol, 2 dose';
			$enumerationLevel3[] = 'Hep A, ped/adol, 3 dose';
			foreach ($enumerationLevel3 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = uniqid(rand());
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$level2['data'][] = $tmp;
			}
			$sectionOther['data'][] = $level2;

			$enumerationLevel2 = array();
			$enumerationLevel2[] = 'Hep B, adolescent or pediatric';
			$enumerationLevel2[] = 'Hep B, adolescent/high risk infant';
			$enumerationLevel2[] = 'Hep B, adult4';
			$enumerationLevel2[] = 'Hep B, dialysis';
			$enumerationLevel2[] = 'Hib (PRP-OMP)';
			$enumerationLevel2[] = 'Hib-Hep B';
			$enumerationLevel2[] = 'Hib, NOS';
			$enumerationLevel2[] = 'IG';
			$enumerationLevel2[] = 'influenza, live, intranasal';
			$enumerationLevel2[] = 'influenza, NOS';
			$enumerationLevel2[] = 'influenza, split (incl. purified surface antigen)';
			$enumerationLevel2[] = 'IPV';
			$enumerationLevel2[] = 'Japanese encephalitis';
			$enumerationLevel2[] = 'M/R';
			$enumerationLevel2[] = 'measles';
			$enumerationLevel2[] = 'meningococcal';
			$enumerationLevel2[] = 'meningococcal A,C,Y,W-135 diphtheria conjugate';
			$enumerationLevel2[] = 'MMR';
			$enumerationLevel2[] = 'MMRV';
			$enumerationLevel2[] = 'mumps';
			$enumerationLevel2[] = 'OPV';
			$enumerationLevel2[] = 'pneumococcal';
			$enumerationLevel2[] = 'pneumococcal conjugate';
			$enumerationLevel2[] = 'pneumococcal, NOS';
			$enumerationLevel2[] = 'polio, NOS';
			$enumerationLevel2[] = 'rabies, NOS';
			$enumerationLevel2[] = 'RIG';
			$enumerationLevel2[] = 'rotavirus, monovalent';
			$enumerationLevel2[] = 'rotavirus, NOS';
			$enumerationLevel2[] = 'rotavirus, pentavalent';
			$enumerationLevel2[] = 'rotavirus, tetravalent';
			$enumerationLevel2[] = 'rubella';
			$enumerationLevel2[] = 'rubella/mumps';
			$enumerationLevel2[] = 'Td (adult)';
			$enumerationLevel2[] = 'Tdap';
			$enumerationLevel2[] = 'typhoid, oral';
			$enumerationLevel2[] = 'typhoid, ViCPs';
			$enumerationLevel2[] = 'varicella';
			$enumerationLevel2[] = 'yellow fever';
			$enumerationLevel2[] = 'zoster';
			foreach ($enumerationLevel2 as $key=>$val) {
				$tmp = array();
				$tmp['key'] = uniqid(rand());
				$tmp['name'] = $val;
				$tmp['active'] = 1;
				$sectionOther['data'][] = $tmp;
			}

			$level0['data'] = $enums;

			$data = array($level0);
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}
			$level111 = array();
			$level111['key'] = 'N';
			$level111['name'] = 'Nurse';
			$level111['active'] = 1;
			$level111['ormClass'] = 'TeamMember';
			$level111['ormEditMethod'] = 'ormEditMethod';

			$level112 = array();
			$level112['key'] = 'PA';
			$level112['name'] = 'Physician Assistant';
			$level112['active'] = 1;
			$level112['ormClass'] = 'TeamMember';
			$level112['ormEditMethod'] = 'ormEditMethod';

			$level113 = array();
			$level113['key'] = 'NP1';
			$level113['name'] = 'Nurse Practitioner';
			$level113['active'] = 1;
			$level113['ormClass'] = 'TeamMember';
			$level113['ormEditMethod'] = 'ormEditMethod';

			$level114 = array();
			$level114['key'] = 'NP2';
			$level114['name'] = 'Nurse Practitioner';
			$level114['active'] = 1;
			$level114['ormClass'] = 'TeamMember';
			$level114['ormEditMethod'] = 'ormEditMethod';

			$level11 = array();
			$level11['key'] = 'A';
			$level11['name'] = 'Attending';
			$level11['active'] = 1;
			$level11['ormClass'] = 'TeamMember';
			$level11['ormEditMethod'] = 'ormEditMethod';
			$level11['data'] = array();
			$level11['data'][] = $level111;
			$level11['data'][] = $level112;
			$level11['data'][] = $level113;
			$level11['data'][] = $level114;

			$level1 = array();
			$level1['key'] = 'B';
			$level1['name'] = 'Blue';
			$level1['active'] = 1;
			$level1['ormClass'] = 'TeamMember';
			$level1['ormEditMethod'] = 'ormEditMethod';
			$level1['data'] = array();
			$level1['data'][] = $level11;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['ormClass'] = 'TeamMember';
			$level0['ormEditMethod'] = 'ormEditMethod';
			$level0['data'] = array();
			$level0['data'][] = $level1;

			$data = array();
			$data[] = $level0;
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'LSA';
			$level1['name'] = 'Lab Status Alerts';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'VSA';
			$level2['name'] = 'Vitals Status Alerts';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'NSA';
			$level3['name'] = 'Note Status Alerts';
			$level3['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;

			$data = array();
			$data[] = $level0;
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'RPCB';
			$level1['name'] = 'Call Back';
			$level1['active'] = 1;

			$level2 = array();
			$level2['key'] = 'RPCP';
			$level2['name'] = 'Check Progress';
			$level2['active'] = 1;

			$level3 = array();
			$level3['key'] = 'RPC';
			$level3['name'] = 'Converted';
			$level3['active'] = 1;

			$level4 = array();
			$level4['key'] = 'RPRT';
			$level4['name'] = 'Repeat Test';
			$level4['active'] = 1;

			$level5 = array();
			$level5['key'] = 'RPO';
			$level5['name'] = 'Other';
			$level5['active'] = 1;

			$level6 = array();
			$level6['key'] = 'RPNA';
			$level6['name'] = 'N/A';
			$level6['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;
			$level0['data'][] = $level6;

			$data = array();
			$data[] = $level0;
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'GIPROC';
			$level1['name'] = 'GI PROCEDURES';
			$level1['active'] = 1;

			$level2['key'] = 'COLON';
			$level2['name'] = 'COLONOSCOPY';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
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
			$enumeration = new self();
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
				'section' => array('key' => 'section', 'name' => PatientEducation::ENUM_EDUC_SECTION_NAME, 'active' => 1, 'data' => array(
					'other' => array('key' => 'other', 'name' => PatientEducation::ENUM_EDUC_SECTION_OTHER_NAME, 'active' => 1, 'data' => array(
						array('key' => 'HFA', 'name' => 'HF ACTIVITY', 'active' => 1),
						array('key' => 'HFD', 'name' => 'HF DIET', 'active' => 1),
						array('key' => 'HFDM', 'name' => 'HF DISCHARGE MEDS', 'active' => 1),
						array('key' => 'HFF', 'name' => 'HF FOLLOWUP', 'active' => 1),
						array('key' => 'HFS', 'name' => 'HF SYMPTOMS', 'active' => 1),
					)),
					'common' => array('key' => 'common', 'name' => PatientEducation::ENUM_EDUC_SECTION_COMMON_NAME, 'active' => 1, 'data' => array(
						array('key' => 'hyper', 'name' => 'Hypertension', 'active' => 1),
					)),
				)),
				'level' => array('key' => 'level', 'name' => PatientEducation::ENUM_EDUC_LEVEL_NAME, 'active' => 1, 'data' => array(
					array('key' => 'POOR', 'name' => 'Poor', 'active' => 1),
					array('key' => 'FAIR', 'name' => 'Fair', 'active' => 1),
					array('key' => 'GOOD', 'name' => 'Good', 'active' => 1),
					array('key' => 'GNA', 'name' => 'Group-no assessment', 'active' => 1),
					array('key' => 'REFUSED', 'name' => 'Refused', 'active' => 1),
				)),
			);

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = $enums;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generatePatientEducationPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_PARENT_NAME;
			$key = 'PatEduPref';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();

			$level11 = array();
			$level11['key'] = 'sections';
			$level11['name'] = 'Sections';
			$level11['active'] = 1;

			$data = array($level0);
			//self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateEducationTopicPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_TOPIC_PARENT_NAME;
			$key = 'Educ_Topic';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'HFA';
			$level1['name'] = 'HF ACTIVITY';
			$level1['active'] = 1;

			$level2['key'] = 'HFD';
			$level2['name'] = 'HF DIET';
			$level2['active'] = 1;

			$level3['key'] = 'HFDM';
			$level3['name'] = 'HF DISCHARGE MEDS';
			$level3['active'] = 1;

			$level4['key'] = 'HFF';
			$level4['name'] = 'HF FOLLOWUP';
			$level4['active'] = 1;

			$level5['key'] = 'HFS';
			$level5['name'] = 'HF SYMPTOMS';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateEducationLevelPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientEducation::ENUM_LEVEL_PARENT_NAME;
			$key = 'Educ_Level';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'POOR';
			$level1['name'] = 'Poor';
			$level1['active'] = 1;

			$level2['key'] = 'FAIR';
			$level2['name'] = 'Fair';
			$level2['active'] = 1;

			$level3['key'] = 'GOOD';
			$level3['name'] = 'Good';
			$level3['active'] = 1;

			$level4['key'] = 'GNA';
			$level4['name'] = 'Group-no assessment';
			$level4['active'] = 1;

			$level5['key'] = 'REFUSED';
			$level5['name'] = 'Refused';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generatePatientExamPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_PARENT_NAME;
			$key = 'PatExPref';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();

			$level11 = array();
			$level11['key'] = 'sections';
			$level11['name'] = 'Sections';
			$level11['active'] = 1;

			$data = array($level0);
			//self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateExamResultPreferencesEnum($force = false) {
		$ret = false;
		do {
			$name = PatientExam::ENUM_RESULT_PARENT_NAME;
			$key = 'Exam_Res';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'Exam_Abn';
			$level1['name'] = 'Abnormal';
			$level1['active'] = 1;

			$level2['key'] = 'Exam_Norm';
			$level2['name'] = 'Normal';
			$level2['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;

			$data = array($level0);
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'EXAM_ABD';
			$level1['name'] = 'ABDOMEN EXAM';
			$level1['active'] = 1;

			$level2['key'] = 'EXAM_AMS';
			$level2['name'] = 'AUDIOMETRIC SCREENING';
			$level2['active'] = 1;

			$level3['key'] = 'EXAM_AMT';
			$level3['name'] = 'AUDIOMETRIC THRESHOLD';
			$level3['active'] = 1;

			$level4['key'] = 'EXAM_BREAST';
			$level4['name'] = 'BREAST EXAM';
			$level4['active'] = 1;

			$level5['key'] = 'EXAM_CHEST';
			$level5['name'] = 'CHEST EXAM';
			$level5['active'] = 1;

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;
			$level0['data'][] = $level2;
			$level0['data'][] = $level3;
			$level0['data'][] = $level4;
			$level0['data'][] = $level5;

			$data = array($level0);
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level1 = array();
			$level1['key'] = 'ADM_SCHED';
			$level1['name'] = Medication::ENUM_ADMIN_SCHED;
			$level1['active'] = 1;

			$enumList = array(
				'BID'=>'twice per day',
				'TID'=>'three times per day',
				'MO-WE-FR'=>'once on monday, once on wednesday, once on friday',
				'NOW'=>'right now',
				'ONCE'=>'one time',
				'Q12H'=>'every 12 hours',
				'Q24H'=>'every 24 hours',
				'Q2H'=>'every 2 hours',
				'Q3H'=>'every 3 hours',
				'Q4H'=>'every 4 hours',
				'Q6H'=>'every 6 hours',
				'Q8H'=>'every 8 hours',
				'Q5MIN'=>'every 5 minutes',
				'QDAY'=>'once per day',
			);

			foreach ($enumList as $k=>$v) {
				$tmp = array();
				$tmp['key'] = $k;
				$tmp['name'] = $v;
				$tmp['active'] = 1;
				if (!isset($level1['data'])) {
					$level1['data'] = array();
				}
				$level1['data'][] = $tmp;
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			$level0['data'][] = $level1;

			$data = array($level0);
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
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$enumList = array(
				'#FFF8DC' => 'Cornsilk',
				'#FAEBD7' => 'Antiquewhite',
				'#FFF5EE' => 'Seashell',
				'#FAF0E6' => 'Linen',
				'#FFFFF0' => 'Ivory',
				'#FFFAF0' => 'Floralwhite',
				'#FFFAFA' => 'Snow',
				'#F0FFFF' => 'Azure',
				'#F5FFFA' => 'Mintcream',
				'#F8F8FF' => 'Ghostwhite',
				'#F0FFF0' => 'Honeydew',
				'#F0F8FF' => 'Aliceblue',
				'#F5F5DC' => 'Beige',
				'#FDF5E6' => 'Oldlace',
				'#FFE4C4' => 'Bisque',
				'#FFE4B5' => 'Moccasin',
				'#F5DEB3' => 'Wheat',
				'#FFDEAD' => 'Navajowhite',
				'#FFEBCD' => 'Blanchedalmond',
				'#D2B48C' => 'Tan',
				'#FFE4E1' => 'Mistyrose',
				'#FFF0F5' => 'Lavenderblush',
				'#E6E6FA' => 'Lavender',
				'#87CEFA' => 'Lightskyblue',
				'#87CEEB' => 'Skyblue',
				'#00BFFF' => 'Deepskyblue',
				'#7FFFD4' => 'Aquamarine',
				'#6495ED' => 'Cornflowerblue',
				'#E9967A' => 'Darksalmon',
				'#FFA07A' => 'Lightsalmon',
				'#B0E0E6' => 'Powderblue',
			);

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;
			$level0['data'] = array();
			foreach ($enumList as $k=>$v) {
				$tmp = array();
				$tmp['key'] = $k;
				$tmp['name'] = $v;
				$tmp['active'] = 1;
				$level0['data'][] = $tmp;
			}

			$data = array();
			$data[] = $level0;
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}

	public static function generateFacilitiesEnum($force = false) {
		$ret = false;
		do {
			$name = 'Facilities';
			$key = 'facilities';
			$enumeration = new self();
			$enumeration->populateByEnumerationName($name);
			// check for key existence
			if (strlen($enumeration->key) > 0 && $enumeration->key == $key) {
				if (!$force) {
					break;
				}
				$enumerationClosure = new EnumerationsClosure();
				$enumerationClosure->deleteEnumeration($enumeration->enumerationId);
			}

			$level0 = array();
			$level0['key'] = $key;
			$level0['name'] = $name;
			$level0['category'] = 'System';
			$level0['active'] = 1;

			$data = array($level0);
			self::_saveEnumeration($data);
			$ret = true;
		} while(false);
		return $ret;
	}
		
	protected static function _saveEnumeration($data,$parentId=0) {
		$enumerationsClosure = new EnumerationsClosure();
		foreach ($data as $item) {
			$enumerationId = $enumerationsClosure->insertEnumeration($item,$parentId);
			if (isset($item['data'])) {
				$item['guid'] = NSDR::create_guid();
				self::_saveEnumeration($item['data'],$enumerationId);
			}
		}
	}

	public static function enumerationToJson($name) {
		$enumeration = new self();
		$enumeration->populateByEnumerationName($name);
		$enumerationsClosure = new EnumerationsClosure();
		$enumerationIterator = $enumerationsClosure->getAllDescendants($enumeration->enumerationId,1);
		return $enumerationIterator->toJsonArray('enumerationId',array('name'));
	}

	public static function checkDuplicates() {
		$ret = array();
		$db = Zend_Registry::get('dbAdapter');
		$enumeration = new self();
		$enumerationIterator = $enumeration->getIterator();
		foreach ($enumerationIterator as $enum) {
			$sqlSelect = $db->select()
					->from($enumeration->_table,array('COUNT(`key`) AS ctr'))
					->where('`key` = ?',$enum->key);
			if ($row = $db->fetchRow($sqlSelect)) {
				if ($row['ctr'] >= 2) {
					$ret[] = $enum->key;
				}
			}
		}
		return $ret;
	}

}
