/*****************************************************************************
*       ch3grid_excell_sub_row.js
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

function eXcell_sub_row(cell){
	if (cell) {
		this.cell = cell;
		this.grid = this.cell.parentNode.grid
		var _this = this;
		// clean up all open divs
		this.grid.attachEvent("onClearAll",function(){ _this._cleanUpOpenedSubRow(_this.cell); });
	}
	this.divBox = null;
}

eXcell_sub_row.prototype = new eXcell;

eXcell_sub_row.prototype._cleanUpOpenedSubRow = function(cell) {
	if (!this.divBox) {
		return;
	}
	if (!cell) {
		cell = this.cell;
	}
	var divBox = this.divBox;

	var node = null;
	for (var i = divBox.childNodes.length - 1; i >= 0; i--) {
		node = divBox.childNodes[i];
		if (node.nodeName == "DIV") {
			divBox.removeChild(node);
		}
	}

	this.divBox = null;
};

eXcell_sub_row.prototype.getTitle = function() {
	var title = "";
	if (this.cell._value) {
		title = "click to";
		if (this.cell._state == "plus") {
			title += " expand";
		}
		else {
			title += " collapse";
		}
	}
	return title;
};

eXcell_sub_row.prototype.getValue = function() {
	return this.cell._value;
};

eXcell_sub_row.prototype.setValue = function(val) {
	this.grid.setSubRowValue(this,val);
};

eXcell_sub_row.prototype._setState = function(state,cell) {
	if (!state) {
		state = "blank";
	}
	if (!cell) {
		cell = this.cell;
	}
	cell._state = state;
	var onClick = "this.";
	if (!_isKHTML) {
		onClick += "parentNode.";
	}
	onClick += "parentNode.parentNode.parentNode.grid.toggleSubRow(this);event.cancelBubble=true;";
	cell.innerHTML = "<img src=\"" + this.grid.imgURL + state + ".gif\" width=\"18\" height=\"18\" onclick=\"" + onClick + "\" />";
};

dhtmlXGridObject.prototype.setSubRowValue = function(subRow,val) {
	if (!subRow) {
		return;
	}
	var cell = subRow.cell;
	var state = "blank";
	if (val) {
		state = "plus";
		cell._value = val;
	}
	if (!cell._this) {
		cell._this = subRow;
	}
	subRow._setState(state,cell);
};

dhtmlXGridObject.prototype.toggleSubRow = function(obj) {
	var cell = obj.parentNode.parentNode;
	if (!_isKHTML) {
		cell = obj.parentNode;
	}
	if (!cell) {
		return;
	}
	if (cell._state == "plus") {
		this.expandSubRow(cell);
	}
	else if (cell._state == "minus") {
		this.collapseSubRow(cell);
	}
	this._fixCssSubRow(cell);
};

dhtmlXGridObject.prototype._fixCssSubRow = function(cell) {
	if (!cell) {
		return;
	}
	var td = cell;
	var tr = td.parentNode;
	var table = tr.parentNode.parentNode;
	var divBox = table.parentNode;

	// fix the alignment of the opened subrow
	var div = null;
	var t = null;
	var d = null;
	var topAllowance = 45;
	var height = 0;
	var cellHeight = 0;
	// table has tbody tag, start at tbody
	var tbody = table.firstChild;
	for (var i = 0; i < tbody.childNodes.length; i++) {
		t = tbody.childNodes[i];
		d = t.childNodes[cell.cellIndex];
		if (!d || !(d._annotationIndex && divBox.childNodes[d._annotationIndex]) || d._state != "minus") {
			continue;
		}
		div = divBox.childNodes[d._annotationIndex];
		height = div.offsetHeight;

		cellHeight = height + 30;
		t.style.height = cellHeight + "px";
		d.style.height = cellHeight + "px";

		div.style.height = height + "px";
		div.style.top = (d.offsetTop + topAllowance) + "px";
		div.style.left = d.offsetLeft + "px";
	}
}

dhtmlXGridObject.prototype.expandSubRow = function(cell) {
	if (!cell || !cell._value || cell._state != "plus") {
		return;
	}
	var td = cell;
	var tr = td.parentNode;
	var table = tr.parentNode.parentNode;
	var divBox = table.parentNode;

	if (!td._this.divBox) {
		td._this.divBox = divBox;
	}

	var d = null;
	for (var i = 0; i < tr.childNodes.length; i++) {
		d = tr.childNodes[i];
		d.style.verticalAlign = "top";
		d.style.paddingTop = "3px";
	}

	var div = null;

	if (cell._annotationIndex && divBox.childNodes[cell._annotationIndex]) {
		div = divBox.childNodes[cell._annotationIndex];
		div.style.display = "block";
	}
	else {
		div = document.createElement("DIV");
		if (td._sub_row_type) {
			td._this.renderSubRow(td,div);
			td._this.grid.callEvent("onSubRowOpen",[td._this.cell,(!!td._this._expanded)]); // temporarily useless
		}
		else {
			div.innerHTML = cell._value;
		}
		div.className = "dhx_sub_row";
		div.style.cssText = "position:absolute;overflow:auto;font-family:Tahoma;font-size:8pt;margin-top:2px;margin-left:4px;";

		// get the size of the divBox and serve as the index of the annotation box
		cell._annotationIndex = divBox.childNodes.length;
		// append this to divclass="objbox"
		divBox.appendChild(div);
	}

	var state = "minus";
	cell._this._setState(state,cell);
};

dhtmlXGridObject.prototype.collapseSubRow = function(cell) {
	if (!cell || cell._state != "minus") {
		return;
	}
	var state = "plus";
	var td = cell;
	var tr = td.parentNode;
	var table = tr.parentNode.parentNode;
	var divBox = table.parentNode;

	tr.style.height = "";
	td.style.height = "";

	var d = null;
	for (var i = 0; i < tr.childNodes.length; i++) {
		d = tr.childNodes[i];
		d.style.verticalAlign = "";
		d.style.paddingTop = "";
	}

	if (cell._annotationIndex && divBox.childNodes[cell._annotationIndex]) {
		var obj = divBox.childNodes[cell._annotationIndex];
		obj.style.display = "none";
	}

	cell._this._setState(state,cell);
};



function eXcell_sub_row_grid(cell) {
	this.base = eXcell_sub_row;
	this.base(cell);
}

eXcell_sub_row_grid.prototype = new eXcell_sub_row;

eXcell_sub_row_grid.prototype.setValue = function(val) {
	this.grid.setSubRowValue(this,val);
	this.cell._sub_row_type = "grid";
}

eXcell_sub_row_grid.prototype.renderSubRow = function(row,div) {
	if (!row) {
		return;
	}
	if (!div) {
		div = document.createElement("DIV");
	}
	div.ctrl = row;
	var grid = row._this.grid;
	row._sub_grid = new dhtmlXGridObject(div);
	row._sub_grid.parentGrid = grid;
	row._sub_grid.setImagePath(grid.imgURL);
	row._sub_grid.enableAutoHeight(true);

	row._sub_grid.loadXML(row._value,function() {
		grid._fixCssSubRow(row);
		row._sub_grid.objBox.style.overflow = "hidden";
		row._sub_row_type = null;
	});
	return div;
};
