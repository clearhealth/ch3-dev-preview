<?php
/*****************************************************************************
*	AllTests.php
*
*	Author:  ClearHealth Inc. (www.clear-health.com)	2009
*	
*	ClearHealth(TM), HealthCloud(TM), WebVista(TM) and their 
*	respective logos, icons, and terms are registered trademarks 
*	of ClearHealth Inc.
*
*	Though this software is open source you MAY NOT use our 
*	trademarks, graphics, logos and icons without explicit permission. 
*	Derivitive works MUST NOT be primarily identified using our 
*	trademarks, though statements such as "Based on ClearHealth(TM) 
*	Technology" or "incoporating ClearHealth(TM) source code" 
*	are permissible.
*
*	This file is licensed under the GPL V3, you can find
*	a copy of that license by visiting:
*	http://www.fsf.org/licensing/licenses/gpl.html
*	
*****************************************************************************/

require_once dirname(__FILE__) . '/../TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Models_AllTests::main');
}

class Models_AllTests {

	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('ClearHealth 3.0 - Models');
		$suite->addTestSuite('Models_DbTablesTest');
		$suite->addTestSuite('Models_PHPModulesTest');
		return $suite;
	}

}

if (PHPUnit_MAIN_METHOD == 'Models_AllTests::main') {
	Models_AllTests::main();
}
