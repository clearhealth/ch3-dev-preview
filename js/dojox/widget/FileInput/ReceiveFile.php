<?php
/*****************************************************************************
*	ReceiveFile.php
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


// THIS IS AN EXAMPLE
// you will obviously need to do more server side work than I am doing here to check and move your upload.
// API is up for discussion, jump on http://dojotoolkit.org/forums

// JSON.php is available in dojo svn checkout
require("../../../dojo/tests/resources/JSON.php");
$json = new Services_JSON();

// fake delay
sleep(3);
$name = empty($_REQUEST['name'])? "default" : $_REQUEST['name'];
if(is_array($_FILES)){
	$ar = array(
		// lets just pass lots of stuff back and see what we find.
		// the _FILES aren't coming through in IE6 (maybe 7)
		'status' => "success",
		'name' => $name,
		'request' => $_REQUEST,
		'postvars' => $_POST,
		'details' => $_FILES,
		// and some static subarray just to see
		'foo' => array('foo'=>"bar")
	);

}else{
	$ar = array(
		'status' => "failed",
		'details' => ""
	);
}

// yeah, seems you have to wrap iframeIO stuff in textareas?
$foo = $json->encode($ar);
?>
<textarea><?php print $foo; ?></textarea>
