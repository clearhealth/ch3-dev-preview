<?php
/*****************************************************************************
*	dojoxAnalytics.php
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

	require_once("./JSON.php");

	$filename = "./logs/analytics.log";
	$json = new Services_JSON;

	$id = $_REQUEST["id"];
	$items = $json->decode($_REQUEST["data"]);

	if (!$handle = fopen($filename, 'a+')) {
		print '{error: "server error"}';
		exit;
	}

	foreach($items as $i=>$item){
		$item->_analyticsId = $id;
		$item->_analyticsTimeStamp = time();
		$log = $json->encode($item) . "\n";
		fwrite($handle, $log);
	}
	
	fclose($handle);

	$response = "{'eventsRecieved': '" . sizeof($items) . "', 'id': '" . $id . "'}";
	if ($_REQUEST["callback"]){
		print $_REQUEST["callback"] . "(" . $response . ");";
	}else{
		print $response;
	}
	
?>
