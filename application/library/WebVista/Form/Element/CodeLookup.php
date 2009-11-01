<?php
/*****************************************************************************
*       CodeLookup.php
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


/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * RichEdit form element
 */
class Zend_Form_Element_CodeLookup extends Zend_Form_Element_Xhtml {

	var $helper = null;

	public function render(Zend_View_Interface $view = null) {
		$src = strtolower($this->getAttrib('src'));
		switch ($src) {
			case 'cpt':
				break;
			case 'icd9':
				break;
			default:
				return __('Invalid source.');
				break;
		}

		$view = $this->getView();
		$belongsTo = $this->getBelongsTo();
		$name = $this->getName();
		$value = $this->getValue();
		$id = $belongsTo .'-' . $name;
		$completeName = $belongsTo .'[' . $name . ']';

		$isReadonly = false;
		if (isset($view->signatureInfo) && strlen($view->signatureInfo) > 0) {
			$isReadonly = true;
		}

		$codesValues = '';
		if (strlen($value) > 0) {
			$arrValues = explode("^|^",$value);
			foreach ($arrValues as $v) {
				$checked = substr($v,0,2);
				$strVal = substr($v,2);
				$codesValues .= '<div><input type="checkbox" id="'.$name.'Codes" name="'.$name.'Codes" value="'.$strVal.'"';
				if ($checked == '1-') {
					$codesValues .= ' checked="checked"';
				}
				if ($isReadonly) {
					$codesValues .= ' disabled="disabled"';
				}
				$codesValues .= ' /> '.$strVal.'<br /></div>';
			}
		}

		$ret = '';
		if (!$isReadonly) {
			$ret .= <<<EOL

<div style="width:100%;height:100%;">
	<input type="text" id="q" name="q" style="width:80%" onkeypress="return codeLookupKeyPressInput(event);" /><button id="searchLabel" onClick="return codeLookup();">Search</button>
	<br />
	<style>div.gridbox_xp table.obj td {border-bottom: none;border-right:none;}</style>
	<div id="codeLookupGridContainer" style="height:150px;"></div>
	<input type="button" id="codeLookupAddId" value="Add" onClick="codeLookupAdd()" disabled="true" />
</div>

<script>

function codeLookup() {
	codeLookupGrid.clearAll();
	codeLookupGrid.load("{$view->baseUrl}/code-lookup.raw?src={$src}&q="+dojo.byId('q').value,function() {
							dojo.byId("codeLookupAddId").disabled = true;},"json");
	return false;
}

function codeLookupAdd() {
	var rowId = codeLookupGrid.getSelectedRowId();
	if (rowId == null) {
		alert('No code selected');
		return;
	}

	var codesContainer = dojo.byId("codesContainer");
	var strTxt = codeLookupGrid.cells(rowId,0).getValue();
	var val = rowId + ' - ' + strTxt;

	var cbInput = document.createElement("input");
	cbInput.type = "checkbox";
	cbInput.id = "{$name}Codes";
	cbInput.name = "{$name}Codes";
	cbInput.value = val;
	cbInput.checked = cbInput.defaultChecked = true;

	var oDiv = document.createElement("div");
	oDiv.appendChild(cbInput);
	oDiv.innerHTML += ' ' + val + '<br />';
	codesContainer.appendChild(oDiv);
}

function codeLookupKeyPressInput(e) {
	var key = window.event ? e.keyCode : e.which;
	if (key == 13) {
		codeLookup();
		return false;
	}
}

var codeLookupGrid = new dhtmlXGridObject('codeLookupGridContainer');
codeLookupGrid.setImagePath("{$view->baseUrl}/img/");
codeLookupGrid.setHeader('Description,Code');
codeLookupGrid.setInitWidths("*,120");
codeLookupGrid.setColAlign("left,right");
codeLookupGrid.setColTypes("ro");
codeLookupGrid.setSkin("xp");
codeLookupGrid.attachEvent("onRowSelect",codeLookupRowSelectHandler);
codeLookupGrid.attachEvent("onRowDblClicked",codeLookupRowDoubleClickedHandler);
codeLookupGrid.init();

function codeLookupRowSelectHandler(rowId,cellIndex) {
	dojo.byId("codeLookupAddId").disabled = false;
}

function codeLookupRowDoubleClickedHandler(rowId,colIndex) {
	codeLookupAdd();
}

</script>
<br />

EOL;
		}

		$ret .= <<<EOL

<div id="codesContainer">{$codesValues}</div>
<input type="hidden" id="{$id}" name="{$completeName}" value="{$value}" />

<script>

function transcriptionNotesFormSubmit() {
	var codesEl = document.getElementsByName("{$name}Codes");
	if (codesEl == null) {
		return true;
	}
	var val = new Array();
	for (i = 0; i < codesEl.length; i++) {
		var chk = 0;
		if (codesEl[i].checked) {
			chk = 1;
		}
		val.push(chk + "-" + codesEl[i].value);
	}
	dojo.byId('{$id}').value = val.join("^|^");
	return true;
}

</script>

EOL;

		return $ret;
	}

}
