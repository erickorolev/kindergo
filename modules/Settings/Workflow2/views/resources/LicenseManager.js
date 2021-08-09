function refreshLicense() {
    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'LicenseRefresh'}, function() {
        window.location.reload();
    });
}
function removeLicense() {
    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'LicenseRemove'}, function() {
        window.location.reload();
    });
}
function setLicense() {
    bootbox.prompt("Your License-Code:", function(license) {
        if(license == null || license == false) return;

        var params = {
            module: 'Workflow2',
            action: 'SetLicense',
            parent: 'Settings',
            dataType: 'json',
            license: license
        };

        if(license != null) {
            RedooUtils('Workflow2').blockUI({
                'message' : 'Please wait'
            });

            RedooAjax('Workflow2').post(params).then(function(data) {
                if(data.result.success == true) {
                    RedooUtils('Workflow2').blockUI({
                        'message' : 'We refresh the list of Tasks you could use.'
                    });
                    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'RefreshTypes'}, function() {
                        window.location.reload();
                    });
                }
                if(data.result.success == false) alert(data.result["error"]);
            });
        }
    });
}

jQuery(function() {
    jQuery('.UpdateCheckModule').on('click', function(e) {
        var module = jQuery(e.currentTarget).data('module');
        jQuery.post('index.php', { 'module':module, 'parent':'Settings', 'view':'Upgrade', 'step' : 1}, function(response) {
            app.helper.showModal(response, { cb: function (data) {
                jQuery('.StartUpdate').on('click', function() {
                    jQuery('#RUNNING_UPDATE').show();

                    jQuery.post('index.php', { 'module':module, 'parent':'Settings', 'view':'Upgrade', 'step' : 3}, function(response) {
                        window.location.reload();
                    });
                });
            } });
        });
    });
});