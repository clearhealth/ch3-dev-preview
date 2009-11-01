<?php
/*****************************************************************************
*       HL7Message.php
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


class HL7Message extends WebVista_Model_ORM {

	protected $hl7MessageId;
	protected $message;
	protected $type;
	protected $_table = "hl7Messages";
	protected $_primaryKeys = array("hl7MessageId");


	public static function generateTestData() {
		$message = new self();
		$message->message = 'PID|1234|doe^john^h';
		$message->type = 'HL7';
		$message->persist();
	}
}
