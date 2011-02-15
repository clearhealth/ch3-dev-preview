<?php
/*****************************************************************************
*       Claim.php
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


class Claim extends WebVista_Model_ORM {

	protected $claimId;
	protected $_table = 'claims';
	protected $_primaryKeys = array('claimId');

	public static function balanceOperators() {
		return array(
			'='=>'equal',
			'>'=>'greater than',
			'>='=>'greater than or equal',
			'<'=>'less than',
			'<='=>'less than or equal',
			'between'=>'between'
		);
	}

	public static function listOptions() {
		return array(
			'healthcloud'=>'Send to HealthCloud',
			'download4010A'=>'Download 4010A1',
			'download5010'=>'Download 5010',
			'CMS1500PDF'=>'CMS1500 PDF',
			'CMS1450PDF'=>'CMS1450 PDF',
			'previewStatements'=>'Preview Statements',
			'publishStatements'=>'Publish Statements',
		);
	}

}
