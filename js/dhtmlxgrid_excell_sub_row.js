//v.2.1 build 90226

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
To use this component please contact sales@dhtmlx.com to obtain license
*/
function eXcell_sub_row(cell){
	if (cell){
		this.cell = cell;
    	this.grid = this.cell.parentNode.grid;
	}
	
	this.getValue = function(){
		return this.grid.getUserData(this.cell.parentNode.idd,"sub_row");
	}
	this._setState = function(m,v){
		(v||this.cell).innerHTML="<img src='"+this.grid.imgURL+m+"' width='18' height='18' />";
		(v||this.cell).firstChild.onclick=this.grid._expandMonolite;
	}
	this.open = function (){
		this.cell.firstChild.onclick(null,true)
	}
	this.close = function (){
		this.cell.firstChild.onclick(null,false,true)
	}
	this.setValue = function(val){
		if (val)
			this.grid.setUserData(this.cell.parentNode.idd,"sub_row",val);
		this._setState(val?"plus.gif":"blanc.gif");
	}
	this.setContent = function(val){
		if (this.cell.parentNode._expanded){
			this.cell.parentNode._expanded.innerHTML=val;
			this.grid._detectHeight(this.cell.parentNode._expanded,this.cell,this.cell.parentNode._expanded.scrollHeight)
		}
		else{
			this.cell._previous_content=null;
			this.setValue(val);
			this.cell._sub_row_type=null
		}
			
	}
	this.isDisabled = function(){ return true; }
	this.getTitle = function(){ return this.grid.getUserData(this.cell.parentNode.idd,"sub_row")?"click to expand|collapse":""; }
}
eXcell_sub_row.prototype = new eXcell;

function eXcell_sub_row_ajax(cell){
	this.base=eXcell_sub_row;
	this.base(cell);
	
	this.setValue = function(val){
		if (val)
			this.grid.setUserData(this.cell.parentNode.idd,"sub_row",val);
			this.cell._sub_row_type="ajax";
		this._setState(val?"plus.gif":"blanc.gif");
	}
}
eXcell_sub_row_ajax.prototype = new eXcell_sub_row;

function eXcell_sub_row_grid(cell){
	this.base=eXcell_sub_row;
	this.base(cell);
	
	this.setValue = function(val){
		if (val)
			this.grid.setUserData(this.cell.parentNode.idd,"sub_row",val);
			this.cell._sub_row_type="grid";
		this._setState(val?"plus.gif":"blanc.gif");
	}
	this.getSubGrid = function(){
		if (!cell._sub_grid) return null;
		return cell._sub_grid;
	}
}
eXcell_sub_row_grid.prototype = new eXcell_sub_row;

dhtmlXGridObject.prototype._expandMonolite=function(n,show,hide){
	var td=this.parentNode;
	var row=td.parentNode;
	var that=row.grid;
	var c=that.getUserData(row.idd,"sub_row");
	
	if (!that._sub_row_editor)
    	that._sub_row_editor=new eXcell_sub_row(td);
	
	if (!c) return;
	
	if (row._expanded && !show){
		that._sub_row_editor._setState("plus.gif",td);
		td._previous_content=row._expanded;
		that.objBox.removeChild(row._expanded);
		row._expanded=false;
		row.style.height=(row.oldHeight||20)+"px";
		td.style.height=(row.oldHeight||20)+"px";	
		
		if (that._fake)
			that._fake.rowsAr[row.idd].style.height=(row.oldHeight||20)+"px";
			
		for (var i=0; i<row.cells.length; i++){
			row.cells[i].style.verticalAlign="middle";
			row.cells[i].style.paddingTop="0px";
		}
		
		delete that._flow[row.idd];
		that._correctMonolite();
		row._expanded.ctrl=null;
	}else if (!row._expanded && !hide){
		that._sub_row_editor._setState("minus.gif",td);
		if (td._previous_content){
			var d=td._previous_content;
			d.ctrl=td;
			that.objBox.appendChild(d);
			that._detectHeight(d,td,parseInt(d.style.height))	
		}
		else {
			var d=document.createElement("DIV");
			row.oldHeight=td.offsetHeight;
			d.ctrl=td;
			if (td._sub_row_type)
				that._sub_row_render[td._sub_row_type](that,d,td,c);
			else
				d.innerHTML=c;
			d.style.cssText="position:absolute; left:0px; top:0px; overflow:auto; font-family:Tahoma; font-size:8pt; margin-top:2px; margin-left:4px;";
			d.className="dhx_sub_row";
			that.objBox.appendChild(d);
			that._detectHeight(d,td)			
		}
		

			
		
		if (!that._flow) {
			that.attachEvent("onGridReconstructed",function(){ this._correctMonolite(); });
			that.attachEvent("onResizeEnd",function(){ this._correctMonolite(true); });
			that.attachEvent("onAfterCMove",function(){ this._correctMonolite(true); });
			that.attachEvent("onDrop",function(){ this._correctMonolite(true); });
			that.attachEvent("onBeforePageChanged",function(){ this._collapsMonolite(); return true; });
			that.attachEvent("onGroupStateChanged",function(){ this._correctMonolite(); return true; });
			that.attachEvent("onFilterEnd",function(){ this._collapsMonolite(); });
			that.attachEvent("onUnGroup",function(){ this._collapsMonolite(); });
			that.attachEvent("onPageChanged",function(){ this._collapsMonolite(); });
			
			that.attachEvent("onXLE",function(){ this._collapsMonolite(); });
			that.attachEvent("onClearAll",function(){ for (var i in this._flow) {
				if (this._flow[i] && this._flow[i].parentNode) this._flow[i].parentNode.removeChild(this._flow[i]);
			}; this._flow=[]; });
			that.attachEvent("onEditCell",function(a,b,c){  if ((a!==2) && this._flow[b] && this.cellType[c]!="ch" && this.cellType[c]!="ra") this._expandMonolite.apply(this._flow[b].ctrl.firstChild,[0,false,true]);  return true; });
			that.attachEvent("onCellChanged",function(id,ind){ if (!this._flow[id]) return; 
					var c=this.cells(id,ind).cell;
					c.style.verticalAlign="top";
					c.style.paddingTop="3px";
			});
			
			that._flow=[];
		}
		that._flow[row.idd]=d;
		that._correctMonolite();
		//d.style.top=row.offsetTop+20+"px";
		
		
		for (var i=0; i<row.cells.length; i++){
			row.cells[i].style.verticalAlign="top";
			row.cells[i].style.paddingTop="3px";
		}
		if (that._fake){
			var frow=that._fake.rowsAr[row.idd];
			for (var i=0; i<frow.cells.length; i++){
				frow.cells[i].style.verticalAlign="top";
				frow.cells[i].style.paddingTop="3px";
			}
		}
		td.style.paddingTop="1px";
		row._expanded=d;
	}
	if (that._ahgr)
			that.setSizes()
	if (that.parentGrid)
		that.callEvent("onGridReconstructed",[]);
	that.callEvent("onSubRowOpen",[row.idd,(!!row._expanded)]);
}
dhtmlXGridObject.prototype._sub_row_render={
    "ajax":function(that,d,td,c){
        d.innerHTML="Loading...";
        //d.innerHTML=that.i18n.loading;
        var xml=new dtmlXMLLoaderObject(function(){
            d.innerHTML=xml.xmlDoc.responseText;
            var z=xml.xmlDoc.responseText.match(/<script[^>]*>([^<]+)<\/script>/g);
            if (z)
                for (var i=0; i<z.length; i++)
                    eval(z[i].replace(/<([\/]{0,1})s[^>]*>/g,""));

			that._detectHeight(d,td)
			that._correctMonolite();
			that.setUserData(td.parentNode.idd,"sub_row",xml.xmlDoc.responseText);
			td._sub_row_type=null;
			if (that._ahgr)
				that.setSizes()
			that.callEvent("onSubAjaxLoad",[td.parentNode.idd,xml.xmlDoc.responseText]);
		}, this,true,true);
		xml.loadXML(c);
	},
	"grid":function(that,d,td,c){
		   td._sub_grid= new dhtmlXGridObject(d);
		   td._sub_grid.parentGrid=that;
		   td._sub_grid.setImagePath(that.imgURL);
		   td._sub_grid.enableAutoHeight(true);
		   td._sub_grid.attachEvent("onGridReconstructed",function(){
		   		that._detectHeight(d,td,td._sub_grid.objBox.scrollHeight+td._sub_grid.hdr.offsetHeight);
		   		that._correctMonolite();
		   		this.setSizes();
		   		if (that.parentGrid) that.callEvent("onGridReconstructed",[]);
	   	   })
		   if (!that.callEvent("onSubGridCreated",[td._sub_grid,td.parentNode.idd,td._cellIndex,c])) return;
		   td._sub_grid.loadXML(c,function(){
				that._detectHeight(d,td,td._sub_grid.objBox.scrollHeight+td._sub_grid.hdr.offsetHeight);
				td._sub_grid.objBox.style.overflow="hidden";
				that._correctMonolite();
				td._sub_row_type=null;
				if (!that.callEvent("onSubGridLoaded",[td._sub_grid,td.parentNode.idd,td._cellIndex,c])) return;
		   	});		   
	}
}

dhtmlXGridObject.prototype._detectHeight=function(d,td,h){
	var l=td.offsetLeft+td.offsetWidth;
		//d.style.left=(l+100)+"px";
		d.style.left=(l-25)+"px";
		d.style.width=td.parentNode.offsetWidth-l-4+"px"
		var h=h||d.scrollHeight;
		d.style.overflow="hidden";
		d.style.height=h+"px";		
		var row=td.parentNode;
		td.parentNode.style.height=(row.oldHeight||20)+3+h*1+"px";	
		td.style.height=(row.oldHeight||20)+3+h*1+"px";	
		if (this._fake){
			var tr=this._fake.rowsAr[td.parentNode.idd];
			tr.style.height=(row.oldHeight||20)+3+h*1+"px";	
		}
}
dhtmlXGridObject.prototype._correctMonolite=function(mode){
	for (var a in this._flow)
		if (this._flow[a] && this._flow[a].tagName=="DIV")
			if (this.rowsAr[a]){			
				if (this.rowsAr[a].style.display=="none") {
					this.cells4(this._flow[a].ctrl).close();
					continue;
				}
				//this._flow[a].style.top=(this.rowsAr[a].offsetTop+(this.rowsAr[a].oldHeight||20)+90)+"px";
				this._flow[a].style.top=(this.rowsAr[a].offsetTop+(this.rowsAr[a].oldHeight||20)+25)+"px";
				if (mode) {
					var l=this._flow[a].ctrl.offsetLeft+this._flow[a].ctrl.offsetWidth;
					this._flow[a].style.left=l+"px";
					this._flow[a].style.width=this.rowsAr[a].offsetWidth-l-4+"px"
				}
			}
			else{
				this._flow[a].ctrl=null;
				this.objBox.removeChild(this._flow[a]);
				delete this._flow[a];
			}
}
dhtmlXGridObject.prototype._collapsMonolite=function(){
		for (var a in this._flow)
			if (this._flow[a] && this._flow[a].tagName=="DIV")
				if (this.rowsAr[a])
					this.cells4(this._flow[a].ctrl).close();
}
//(c)dhtmlx ltd. www.dhtmlx.com
