dojo.provide("custom.PatientSelectAutoComplete");
dojo.declare("custom.PatientSelectAutoComplete", dojox.data.QueryReadStore, {
	fetch:function (request) {
        	request.serverQuery = { patientSelect: request.query.name };
        	return this.inherited("fetch", arguments);
    	}
});


