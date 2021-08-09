/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.08.15 21:21
 * You must not use this file without permission.
 */
function initEvents() {
    jQuery('select#addConnection').select2({
        formatSelection: function (item) {
            opt = jQuery(item.element);
            sel = opt.text();

            og = opt.closest('optgroup').attr('label');
            if(typeof og == 'undefined') {
                return sel;
            }
            return og + ' - ' + sel;

        },
        escapeMarkup: function(m) { return m; }
    });


    jQuery('#addConnectionBtn').on('click', function() {
        var type = jQuery('#addConnection').val();
        if(type == '') {
            return;
        }

        RedooAjax('Workflow2').postSettingsAction('ProviderEditor', { type: type }).then(function(response) {
            RedooUtils('Workflow2').showModalBox(response).then(function() {
                jQuery('.testSettingsBtn').on('click', function() {
                    var options = {
                        url:'index.php?parent=Settings&module=Workflow2&action=ProviderTest',
                        success:function(response) {
                            console.log(response);
                        },
                        dataType:'json'
                    };

                    jQuery(this).ajaxSubmit(options);
                });

                jQuery('#WorkflowProviderEditor').on('submit', function() {
                    var options = {
                        success:function(response) {
                            reloadProviderManager();
                            jQuery('.testSettingsBtn').fadeIn('fast');
                        },
                        dataType:'json'
                    };

                    jQuery(this).ajaxSubmit(options);

                    return false;
                });
            });
        });
    });

    jQuery('.delProviderBtn').on('click', function() {
        jQuery.post('index.php', {
            'module':'Workflow2',
            'action':'ProviderDel',
            'parent':'Settings',
            'id':jQuery(this).data('id')
        }, function() {
            reloadProviderManager();
        });
    });
    jQuery('.editProviderBtn').on('click', function() {
        var id = jQuery(this).data('id');
        var type = jQuery(this).data('type');
        if(type == '') {
            return;
        }

        RedooAjax('Workflow2').postSettingsAction('ProviderEditor', { type: type, 'id':id }).then(function(response) {
            RedooUtils('Workflow2').showModalBox(response).then(function() {
                jQuery('.testSettingsBtn').on('click', function() {
                    var options = {
                        url:'index.php?parent=Settings&module=Workflow2&action=ProviderTest',
                        success:function(response) {
                            if(response.result == 'ok') {
                                jQuery('#providerTestResult').html('Test successfully!').css('color', 'darkgreen').show();
                                jQuery('#providerTestError').hide();
                            } else {
                                jQuery('#providerTestResult').hide();
                                jQuery('#providerTestError').html(response.message).css('color', 'darkred').show();
                            }
                            console.log(response);
                        },
                        dataType:'json'
                    };

                    jQuery('#WorkflowProviderEditor').ajaxSubmit(options);
                });

                jQuery('#WorkflowProviderEditor').on('submit', function() {
                    jQuery('#globalmodal').block({
                        'message' : 'Please wait ...'
                    });

                    var options = {
                        success:function(response) {
                            var eleId1 = 'div' + Math.round(Math.random() * 100000);
                            jQuery('#resultContainer').append('<div id="' + eleId2 + '" class="alert alert-success">Sucessfully saved settings!</div>');
                            window.setTimeout(function() {jQuery('#' + eleId1).slideUp('fast')}, 5000)

                            if(response.result === 'error') {
                                var eleId2 = 'div' + Math.round(Math.random() * 100000);
                                jQuery('#resultContainer').append('<div id="' + eleId2 + '" class="alert alert-danger">' + response.message + '</div>');
                                window.setTimeout(function() {jQuery('#' + eleId2).slideUp('fast')}, 5000)
                            }

                            jQuery('#globalmodal').unblock();
                        },
                        dataType:'json'
                    };

                    jQuery(this).ajaxSubmit(options);

                    return false;
                });
            });
        });
    });
}
jQuery(function() {
    initEvents();
});

function reloadProviderManager(openModule) {
    RedooUtils('Workflow2').refreshContent('ProviderManager', true).then(initEvents);
}