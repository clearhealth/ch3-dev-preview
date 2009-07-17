<?php
/*****************************************************************************
*	webbuild.php
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

	$buildScriptsDir = "/Users/jrbsilver/svn/dojo/branches/0.4/buildscripts";
	$buildCacheDir = "/Users/jrbsilver/svn/dojo/branches/0.4/buildscripts/webbuild/webbuildtemp/0.4.2rc1/web/buildscripts";
	$depList = isset($_POST['depList']) ? $_POST['depList'] : null;
	$provideList = isset($_POST['provideList']) ? $_POST['provideList'] : 'null';
	$version = isset($_POST['version']) ? $_POST['version'] : '0.0.0dev';
	$xdDojoUrl = isset($_POST['xdDojoUrl']) ? $_POST['xdDojoUrl'] : '';

	if(!isset($depList)){
?>
		<html>
			Please specify a comma-separated list of files.
		</html>
<?
	}else{
		header("Content-Type: application/x-javascript");
		header("Content-disposition: attachment; filename=dojo.js");
		
		$dojoContents = `/usr/bin/java -jar $buildScriptsDir/../shrinksafe/custom_rhino.jar $buildScriptsDir/makeDojoJsWeb.js $buildCacheDir/dojobuilds $depList $provideList $version $xdDojoUrl`;

		print($dojoContents);
	}
?>
