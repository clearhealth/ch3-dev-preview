<?php
/*****************************************************************************
*	honey.php
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
 
/* honey.php - sample fake delay script to push data
   - should use ob_flush() to send chunks rather than 
   just take a long time ...
*/

session_start(); 

$char = " "; 
$fakeDelay = (empty($_GET['delay'])) ? 1 : $_GET['delay'];
$dataSize = (empty($_GET['size'])) ? 2*1024 : $_GET['size'];
if (empty($_SESSION['counter'])) $_SESSION['counter'] = 1; 
$dataSent = 0;
$blockSize = 1024;

if ($fakeDelay) { sleep($fakeDelay); }

print "view num: ".$_SESSION['counter']++;
while ($dataSent<=$dataSize) {
	for ($i=0; $i<$blockSize/4; $i++) {
		print $char; 
	} print "<br />"; 
	$dataSent += $blockSize; 
	sleep(1);
}

?>
