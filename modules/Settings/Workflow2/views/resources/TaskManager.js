/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 04.05.14 13:54
 * You must not use this file without permission.
 */
function refreshRepoList() {
    var params = {
        module: 'Workflow2',
        view: 'TaskRepoManager',
        parent: 'Settings'
    };
    AppConnector.request(params).then(function(data) {
        jQuery(jQuery(".contentsDiv")[0]).html(data);
    });
}
function refreshTaskList() {
    var params = {
        module: 'Workflow2',
        view: 'TaskManagement',
        parent: 'Settings'
    };
    RedooAjax('Workflow2').request(params).then(function(data) {
        jQuery(".settingsPageDiv").html(data);
        initEvents();
    });
}

jQuery(function() {
    jQuery('.pushLicense').on('click', function() {
        var license = prompt('License Key');
        if(license == null) return;

        var id = jQuery(this).closest('tr').data('id');
        jQuery.post('index.php', {module:'Workflow2', 'action':'PushRepoLicense', 'parent':'Settings', 'license':license, 'repoid':id}, function() {

        });
    });
    jQuery('.deleteRepository').on('click', function() {
        var id = jQuery(this).closest('tr').data('id');
        var check = confirm('Please confirm to delete this repository!\n\rAll installed tasks from this repository, will keep the current status, but will not get updates in future.');
        if(check == false) return;

        jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'TaskRepoDelete', id:id}, refreshRepoList);
    });
});
function updateRepositoryStatus(repo_id, value) {
    RedooUtils('Workflow2').blockUI({
        'message' : 'Repository will be configured'
    });

    jQuery.post('index.php?module=Workflow2&parent=Settings&action=TaskRepoSetStatus', {repo_id: repo_id, status:value }, function(response) {
        RedooUtils('Workflow2').unblockUI();
    });

}

function addRepositoryPopup() {
    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', view:'TaskRepoAdd'}, function(response) {
        app.showModalWindow(response, function(data) {
            jQuery.getScript('modules/Workflow2/views/resources/js/jquery.form.min.js', function() {
                var options = {
                    //target:        '#output1',   // target element(s) to be updated with server response
                    //beforeSubmit:  showRequest,  // pre-submit callback
                    success:       function(response) {

                        if(response.success == true) {
                            jQuery.notification(
                                {
                                    title: "Repository added",
                                    timeout: 5000,
                                    color: '#2cae35',
                                    icon: 'W',
                                    content: 'Your changes are saved'
                                }
                            );
                            refreshRepoList();
                            app.hideModalWindow();
                        }
                        if(response.success == false) {
                            jQuery.notification(
                                {
                                    title: "error during Adding repository",
                                    error: true,
                                    timeout: 5000,
                                    content: response.error.message
                                }
                            );
                        }
                    },  // post-submit callback

                    // other available options:
                    //url:       ''         // override for form's 'action' attribute
                    //type:      type        // 'get' or 'post', override for form's 'method' attribute
                    dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
                    //clearForm: true        // clear all form fields after successful submit
                    //resetForm: true        // reset the form after successful submit

                    // $.ajax options can be used here too, for example:
                    //timeout:   3000
                };

                // bind form using 'ajaxForm'
                jQuery('#popupForm').ajaxForm(options);

                jQuery('#repo_url').on('change', function() {
                    jQuery('#modalSubmitButton').attr('disabled', 'disabled');

                    var newurl = jQuery(this).val();

                    url_validate = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                    if(url_validate.test(newurl)){
                        jQuery.post('index.php?module=Workflow2&parent=Settings&action=TaskRepoCheck', { url:newurl }, function(response) {

                            if(response.success == false || response.result.success == false) {
                                jQuery('#repoTitle').html(jQuery('#repoTitle').data('placeholder')).css('fontWeight', 'default');
                                jQuery('#modalSubmitButton').attr('disabled', 'disabled');
                                alert(response.result.error);
                                return;
                            } else {
                                if(response.result.licenseKey != '') {
                                    jQuery('#Autolicense').show();
                                } else {
                                    jQuery('#Autolicense').hide();
                                }
                                jQuery('[name="_nonce"]').val(response.result['_nonce']);
                                jQuery('[name="repo_license"]').val(response.result.licenseKey);

                                jQuery('#repoTitle').html(response.result.title).css('fontWeight', 'bold');

                                if(response.result.license == true) {
                                    jQuery('#licenseColumn').slideDown('fast');
                                } else {
                                    jQuery('#licenseColumn').slideUp('fast');
                                }

                                jQuery('#modalSubmitButton').removeAttr('disabled');
                            }

                        }, 'json');


                    }

                });
                //jQuery('#modalSubmitButton').removeAttr('disabled');
            });
        });
    });
}

function importTaskfile() { 
    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', view:'TaskImport'},function(response) {
        RedooUtils('Workflow2').showModalBox(response).then(function(data) {
            jQuery.getScript('modules/Workflow2/views/resources/js/jquery.form.min.js', function() {
                var options = {
                    //target:        '#output1',   // target element(s) to be updated with server response
                    //beforeSubmit:  showRequest,  // pre-submit callback
                    success:       function(response) {

                        if(response.success == true) {
                            jQuery.notification(
                                {
                                    title: "Task import successfully",
                                    timeout: 5000,
                                    color: '#2cae35',
                                    icon: 'W',
                                    content: 'Your changes are saved'
                                }
                            );
                            refreshTaskList();
                            app.hideModalWindow();
                        }
                        if(response.success == false) {
                            jQuery.notification(
                                {
                                    title: "error during Import",
                                    error: true,
                                    timeout: 5000,
                                    content: response.error.message
                                }
                            );
                        }
                    },  // post-submit callback

                    // other available options:
                    //url:       ''         // override for form's 'action' attribute
                    //type:      type        // 'get' or 'post', override for form's 'method' attribute
                    dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
                    //clearForm: true        // clear all form fields after successful submit
                    //resetForm: true        // reset the form after successful submit

                    // $.ajax options can be used here too, for example:
                    //timeout:   3000
                };

                // bind form using 'ajaxForm'
                jQuery('#popupForm').ajaxForm(options);

                jQuery('#modalSubmitButton').removeAttr('disabled');
            });
        });
    });

}
function updateRepositories() {
    RedooUtils('Workflow2').blockUI({
        'message' : 'Repositories will be updates'
    });

    jQuery.post('index.php', {module:'Workflow2', parent:'Settings', action:'TaskRepoUpdate'}, function(response) {
        RedooUtils('Workflow2').unblockUI();
        refreshTaskList();
    });
}
function installAllUpdates(repo_id) {
    RedooUtils('Workflow2').blockUI({
        'message' : 'We refresh the list of Tasks you could use.'
    });
    jQuery.post('index.php?module=Workflow2&action=RefreshTypes&parent=Settings', { repo_id:repo_id, mode:"updates" }, function() {
        RedooUtils('Workflow2').unblockUI();
        refreshTaskList();
    });
}
function installAll(repo_id) {
    RedooUtils('Workflow2').blockUI({
        'message' : 'We refresh the list of Tasks you could use.'
    });
    jQuery.post('index.php?module=Workflow2&action=RefreshTypes&parent=Settings', { repo_id:repo_id, mode:"new" }, function() {
        RedooUtils('Workflow2').unblockUI();
        refreshTaskList();
    });
}

function installUpdate(type_id, skipSignatureCheck, reDownload) {
    if(typeof skipSignatureCheck == 'undefined') {
        skipSignatureCheck = false;
    }
    if(typeof reDownload == 'undefined') {
        reDownload = 0;
    }

    RedooUtils('Workflow2').blockUI({
        'message' : 'Update will be installed'
    });

    jQuery.post('index.php?module=Workflow2&parent=Settings&action=TaskUpdate', {
        type_id:type_id,
        skipSignatureCheck:skipSignatureCheck,
        'allowDowngrade':reDownload
    }, function(response) {
        if(response.success == false) {
           jQuery.notification(
               {
                   title: "error during Task Operation",
                   error: true,
                   timeout: 5000,
                   content: response.error.message
               }
           );
       }
        if(response == 'checksum') {
            var doubleCheck = confirm('The download has wrong signature. Do you still want setup this?');

            if(doubleCheck == true) {
                installUpdate(type_id, true);
                return;
            }
        }
        RedooUtils('Workflow2').unblockUI();
        refreshTaskList();
    });
}

function createManualType() {
    if(!confirm('This option is implemented only for developers.\n\n-! Please don\'t use it, if you are not a developer !-')) {
        return;
    }

    var typeName = prompt('Key of new Type (only a-z,A-Y,0-9,_,-)');
    if(typeName === null) {
        return;
    }
    var className = prompt('Classname of new Type (Prefix with "WfTask"!)', 'WfTask' + typeName.charAt(0).toUpperCase() + typeName.substr(1));
    if(className === null) {
        return;
    }
    if(className.substr(0, 6) != 'WfTask') {
        className = 'WfTask' + className;
    }

    var typeLabel = prompt('Label of new Type', typeName);
    if(typeLabel === null) {
        return;
    }


    RedooUtils('Workflow2').blockUI({
        'message' : 'Task will be created'
    });

    jQuery.post('index.php?module=Workflow2&parent=Settings&action=TaskCreate', {
        typeLabel:typeLabel,
        typeName:typeName,
        className: className
    }, function(response) {
        RedooUtils('Workflow2').unblockUI();
        refreshTaskList();
    });

}

