dhtmlXGridObject._emptyLineImg="line";

dhtmlXGridObject.prototype._updateTGRState=function(z,force){ 
	if (force || !z.update || z.id==0) return;
	this._updateLine(z,this.rowsAr[z.id]);
	z.update=false;
}
dhtmlXGridObject.prototype._updateLine=function(z,row){ 
	row=row||this.rowsAr[z.id];
	if (!row) return;
	var im=row.imgTag;
	
		var n=1;
		if (z.index==0){
			if (z.level==0){
				if ((z.parent.childs.length-1)>z.index)
					n=3;
				else n=1;
			}
			else
			{
				if ((z.parent.childs.length-1)>z.index)
					n=3;
				else
					n=2;
			}
		}
		else
			if ((z.parent.childs.length-1)>z.index)
				n=3;
			else
				n=2;
			
		
		im.src=this.imgURL+z.state+n+".gif";
	}
dhtmlXGridObject.prototype._updateParentLine=function(z,row){ 
		row=row||this.rowsAr[z.id];
		if (!row) return;
		var im=row.imgTag;
		
		for (var i=z.level; i>0; i--){
			im=im.previousSibling;
			z=z.parent;
			if (z.id==0) break;
			if ((z.parent.childs.length-1)>z.index)
				im.src=this.imgURL+"line1.gif";
			else
				im.src=this.imgURL+"blanc.gif";
		}
}

dhtmlXGridObject.prototype._renderSortA=dhtmlXGridObject.prototype._renderSort;
dhtmlXGridObject.prototype._renderSort=function(){
	this._renderSortA.apply(this,arguments);
	this._h2.forEachChild((0),function(z){
		this._updateLine(z);
		this._updateParentLine(z);
	},this);
} 

/**
*   @desc: enable lines in treeGrid 
*   @type: public
*   @edition: Professional
*   @topic: 0
*/
dhtmlXGridObject.prototype.enableTreeGridLines=function(){
	this.attachEvent("onXLE",function(a,b,id){
		this._h2.forEachChild((id||0),function(z){
				this._updateLine(z);
				this._updateParentLine(z);
		},this);
	});    
	
	this.attachEvent("onOpenEnd",function(id){
		this._h2.forEachChild((id||0),function(z){
				this._updateLine(z);
				this._updateParentLine(z);
		},this);
	});    
	
	
	this.attachEvent("onRowAdded",function(id){ 
		var z=this._h2.get[id];
		this._updateLine(z); 
		this._updateParentLine(z);
		if (z.index<(z.parent.childs.length-1)){
			z=z.parent.childs[z.index+1];
			this._updateLine(z);
			this._updateParentLine(z);
		}
		else if (z.index!=0){
			z=z.parent.childs[z.index-1];
			this._updateLine(z);
			this._updateParentLine(z);
		}
	});
	this.attachEvent("onOpen",function(id,state){ 
		if (state){ 
			var z=this._h2.get[id];
			for (var i=0; i < z.childs.length; i++) 
				this._updateParentLine(z.childs[i]);
		}
		return true;
	});
	this.attachEvent("onBeforeRowDeleted",function(id){
		var z=this._h2.get[id];
		var w=null;
		if (z.index!=0)
			w=z.parent.childs[z.index-1];
		z=z.parent;
		var self=this;
			
		window.setTimeout(function(){
			self._updateLine(z);
			self._updateParentLine(z);
			
			if (w){
				self._updateLine(w);
				self._updateParentLine(w);
				}
		},1);
	});
} 

//(c)dhtmlx ltd. www.dhtmlx.com
