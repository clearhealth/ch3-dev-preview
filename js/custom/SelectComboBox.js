dojo.provide("custom.SelectComboBox");
dojo.declare("custom.SelectComboBox", dijit.form.ComboBox, {
	_doSelect: function(tgt){
		this.selectedKey = tgt.item.i.label;
		this.item = tgt.item;
		this.setValue(this.store.getValue(tgt.item, this.searchAttr), true);
	},
});
