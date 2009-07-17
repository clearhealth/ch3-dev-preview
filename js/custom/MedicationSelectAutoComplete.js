dojo.provide("custom.MedicationSelectAutoComplete");
dojo.declare("custom.MedicationSelectAutoComplete", dojox.data.QueryReadStore, {
	fetch:function (request) {
		// strip off the *
		var queryName = request.query.name;
		var tradename = queryName.substr(0,queryName.length-1);
        	request.serverQuery = {'': tradename} ;
		delete(request.start);
		delete(request.count);
        	return this.inherited("fetch", arguments);
    	},
	_filterResponse: function(data) {
		var retData = new Object();
		retData.items = new Array();
		retData.numRows = data.length;
		retData.identifier = 'label';
		for (var i=0; i<data.length; i++) {
			retData.items[i] = new Object();
			retData.items[i].label = data[i].id;
			retData.items[i].name = data[i].tradename + ' ' + data[i].strength + ' ' + data[i].unit + ' ' + data[i].packsize + data[i].packtype + ' ' + data[i].ndc;
		}
		return retData;
	}
});
dojo.provide("custom.MedicationSelectComboBox");
dojo.declare("custom.MedicationSelectComboBox", dijit.form.ComboBox, {
	_doSelect: function(tgt){
			this.selectedKey = tgt.item.i.label;
                        this.item = tgt.item;
                        this.setValue(this.store.getValue(tgt.item, this.searchAttr), true);
                }
});

