<?php
/*****************************************************************************
*       Drawing.php
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
class Zend_Form_Element_Drawing extends Zend_Form_Element_Xhtml {

	var $helper = null;

	public function init() {
		parent::init();

		// override the print onclick attribute
		$this->getView()->elementPrint->setAttrib('onclick','printImageDrawing()');
	}

	public function render(Zend_View_Interface $view = null) {
		// overtake $view arguments
		$currentView = $this->getView();
		$belongsTo = $this->getBelongsTo();
		$name = $this->getName();
		$value = $this->getValue();
		$id = $belongsTo .'-' . $name;
		$completeName = $belongsTo .'[' . $name . ']';

		$clinicalNoteId = $currentView->form->getElement("clinicalNoteId")->getValue();

		$annotationIterator = new ClinicalNoteAnnotationIterator();
		$filters = array();
		$filters['clinicalNoteId'] = $clinicalNoteId;
		$annotationIterator->setFilter($filters);

		$setDefaultAnnotations = "var annotations = [];";
		foreach ($annotationIterator as $annotation) {
			$setDefaultAnnotations .= PHP_EOL . "annotations.push({x:{$annotation->xAxis},y:{$annotation->yAxis},value:(<r><![CDATA[{$annotation->annotation}]]></r>).toString(),valueId:{$annotation->clinicalNoteAnnotationId}});";
		}
		$setDefaultAnnotations .= PHP_EOL . "imageDrawing.setDefaultAnnotations(annotations);";

		$attachmentReferenceId = preg_replace('[^a-zA-Z0-9-]','//',$this->getAttrib('src'));

		$attachment = new Attachment();
		$attachment->attachmentReferenceId = $attachmentReferenceId;
		$attachment->populateWithAttachmentReferenceId();
		$imageUrl = "{$currentView->baseUrl}/attachments.raw/view-attachment?attachmentId={$attachment->attachmentId}";

		$setDefaultLines = "var lines = [];";
		if ($value) {
			$setDefaultLines .= PHP_EOL . "lines = dojo.fromJson('{$value}');";
		}
		$setDefaultLines .= PHP_EOL . "imageDrawing.setDefaultLines(lines);";

		/* NOTE: 
		 * <div id="{$name}ContentSpace" style="position:relative;z-index:9999;"></div>
		 * MUST be set to higher z-index otherwise the annotation will not display
		 * <div id="{$name}Surface" style="position:relative;width:100%;height:100%;border: 1px solid #000;"></div>
		 * MUST be set its position to relative otherwise the drawing position is misplaced
		 */

		$docType = $currentView->doctype();
		$str =  <<<EOL

<input type="button" value="Draw" onClick="imageDrawing.setAction('draw')" />
<input type="button" value="Annotate" onClick="imageDrawing.setAction('annotate')" />
<input type="button" value="Clear" onClick="imageDrawing.setAction('clear')" />
<div id="{$name}ContentSpace" style="position:relative;z-index:9999;"></div>
<div id="{$name}Surface" style="position:relative;width:100%;height:100%;border: 1px solid #000;"></div>

<input type="hidden" id="{$id}" name="{$completeName}" value="{$value}" />

<script type="text/javascript">

var surfaceHeight = 1000;
var surfaceWidth = 1650;
var imageDrawing = new DrawingClass("{$name}Surface",surfaceWidth,surfaceHeight,"{$name}ContentSpace");
{$setDefaultLines}
{$setDefaultAnnotations}
imageDrawing.loadImage('{$imageUrl}');
imageDrawing.setEditURL('{$currentView->baseUrl}/clinical-notes.raw/process-edit-annotation');
imageDrawing.setDeleteURL('{$currentView->baseUrl}/clinical-notes.raw/process-delete-annotation');
imageDrawing.setClinicalNoteId('{$clinicalNoteId}');

function transcriptionNotesFormSubmit() {
	var obj = dojo.byId("{$id}");
	if (obj) {
		var lines = imageDrawing.getLines();
		obj.value = lines ;
	}
	return true;
}

function printImageDrawing() {
	var printHtml = (<r><![CDATA[
<?xml version="1.0" encoding="UTF-8" ?>
{$docType}
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
]]></r>).toString();

	var docHead = document.getElementsByTagName("head")[0];
	printHtml += docHead.innerHTML;
	printHtml += '</head><body class="tundra">';
	printHtml += dojo.byId('mainToolbar').innerHTML;

	// transform annotation boxes to printer friendly
	imageDrawing.printerFriendlyAnnotations();
	printHtml += dojo.byId('cntemplateform').innerHTML;
	// revert printer friendly annotation boxes after iframe assignment
	imageDrawing.revertPrinterFriendlyAnnotations();

	printHtml += "</body></html>";

	var iframe = dojo.byId("iframeprint");
	var doc = null;
	if (iframe.contentDocument) {
		// Firefox/Opera
		doc = iframe.contentDocument;
	}
	else if (iframe.contentWindow) {
		// Internet Explorer
		doc = iframe.contentWindow.document;
	}
	else if (iframe.document) {
		// Others
		doc = iframe.document;
	}
	if (doc == null) {
		throw "Document not initialized";
	}

	doc.open();
	doc.write(printHtml);
	doc.close();

	// revert printer friendly annotation boxes after iframe assignment
	//imageDrawing.revertPrinterFriendlyAnnotations();

	dojo.byId('iframeprint').contentWindow.focus();
	dojo.byId('iframeprint').contentWindow.print();
}

</script>

EOL;
		return $str;
	}

}
