<?php
/*****************************************************************************
*       PatientEducation.php
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


class PatientEducation extends WebVista_Model_ORM {

	protected $code;
	protected $patientId;
	protected $level;
	protected $education;
	protected $comments;

	protected $_primaryKeys = array('code','patientId');
	protected $_table = 'patientEducations';

	const ENUM_PARENT_NAME = 'Patient Education Preferences';
	const ENUM_TOPIC_PARENT_NAME = 'Education Topic Preferences';
	const ENUM_LEVEL_PARENT_NAME = 'Education Level Preferences';

	const ENUM_EDUC_PARENT_NAME = 'Education Preferences';
	const ENUM_EDUC_SECTION_NAME = 'Section';
	const ENUM_EDUC_SECTION_OTHER_NAME = 'Other';
	const ENUM_EDUC_SECTION_COMMON_NAME = 'Common';
	const ENUM_EDUC_LEVEL_NAME = 'Level';

	public function getPatientEducationId() {
		return $this->code;
	}

}
