<?php
/*****************************************************************************
*	locale.php
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
$basePath = Zend_Registry::get('basePath');
$contents = trim(file_get_contents($basePath . '/js/dojo/cldr/nls/en/gregorian.js'));?>
// temporarily set the dojo's baseUrl to cater the template rendering
dojo.baseUrl = '<?=$this->view->baseUrl?>/js<?=$this->view->baseUrl?>/';

// en and en-us are the same
dojo.provide("dojo.cldr.nls.en.gregorian");
dojo.cldr.nls.en.gregorian._built=true;
dojo.provide("dojo.cldr.nls.gregorian.en");
dojo.provide("dojo.cldr.nls.gregorian.en_us");
dojo.cldr.nls.gregorian.en=<?=$contents?>;
dojo.cldr.nls.gregorian.en_us=<?=$contents?>;

<?$contents = trim(file_get_contents($basePath . '/js/dojo/cldr/nls/gregorian.js'));?>
dojo.provide("dojo.cldr.nls.gregorian");
dojo.cldr.nls.gregorian._built=true;
dojo.provide("dojo.cldr.nls.gregorian");
dojo.cldr.nls.gregorian=<?=$contents?>;


<?$contents = trim(file_get_contents($basePath . '/js/dijit/form/nls/validate.js'));?>
// en and en-us are the same
dojo.provide("dijit.form.nls.en.validate");
dijit.form.nls.en.validate._built=true;
dojo.provide("dijit.form.nls.validate.en");
dojo.provide("dijit.form.nls.validate.en_us");
dijit.form.nls.validate.en=<?=$contents?>;
dijit.form.nls.validate.en_us=<?=$contents?>;

dojo.provide("dijit.form.nls.validate");
dijit.form.nls.validate._built=true;
dojo.provide("dijit.form.nls.validate");
dijit.form.nls.validate=<?=$contents?>;

<?$contents = trim(file_get_contents($basePath . '/js/dijit/form/nls/ComboBox.js'));?>
// en and en-us are the same
dojo.provide("dijit.form.nls.en.ComboBox");
dijit.form.nls.en.ComboBox._built=true;
dojo.provide("dijit.form.nls.ComboBox.en");
dojo.provide("dijit.form.nls.ComboBox.en_us");
dijit.form.nls.ComboBox.en=<?=$contents?>;
dijit.form.nls.ComboBox.en_us=<?=$contents?>;

dojo.provide("dijit.form.nls.ComboBox");
dijit.form.nls.ComboBox._built=true;
dojo.provide("dijit.form.nls.ComboBox");
dijit.form.nls.ComboBox=<?=$contents?>;

<?$contents = trim(file_get_contents($basePath . '/js/dojo/cldr/nls/number.js'));?>
// en and en-us are the same
dojo.provide("dojo.cldr.nls.en.number");
dojo.cldr.nls.en.number._built=true;
dojo.provide("dojo.cldr.nls.number.en");
dojo.provide("dojo.cldr.nls.number.en_us");
dojo.cldr.nls.number.en=<?=$contents?>;
dojo.cldr.nls.number.en_us=<?=$contents?>;

dojo.provide("dojo.cldr.nls.number");
dojo.cldr.nls.number._built=true;
dojo.provide("dojo.cldr.nls.number");
dojo.cldr.nls.number=<?=$contents?>;

