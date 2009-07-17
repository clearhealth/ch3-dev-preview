
djConfig.usePlainJson=true;

function submitmenumanagerForm() {
    dojo.xhrPost ({
        url: getBaseUrl() + "/menu-manager.raw/edit-process",
        form: 'menumanager',
        content: {
            siteSection: siteSection,
        },
        load: function(data){
            alert(__(data));
            populateMenuTree();
        },
        error: function (error) {
    	    console.error ('Error: ', error);
        }
    });
}


function updateSiteSection(val) {
    if (val == undefined) {
        val = dojo.byId('chSiteSection').value;
    }
    siteSection = val;
}

function updateConnectionContent(val) {
    if (val == undefined) {
        val = dojo.byId('menuManager-type').value;
    }
    var menuId = dojo.byId('menuManager-menuId').value;
    connectionType = val;
    ajaxGet("connection-content", "chConnectionContent", 
            {connectionType: val, menuId: menuId});
}

updateSiteSection();
updateConnectionContent();
