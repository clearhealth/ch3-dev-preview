<?php
/*****************************************************************************
*       PatientImmunization.php
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


class PatientImmunization extends WebVista_Model_ORM {

	protected $code;
	protected $patientId;
	protected $reportedNotAdministered;
	protected $patientReported;
	protected $series;
	protected $reaction;
	protected $repeatContraindicated;
	protected $immunization;
	protected $comment;

	protected $_primaryKeys = array("code");
	protected $_table = "patientImmunizations";

}
