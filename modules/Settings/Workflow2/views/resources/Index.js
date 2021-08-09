/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.09.15 11:53
 * You must not use this file without permission.
 */
var Workflow2Admin = {
    MainModule:null,
    TargetFolder:null,
    initEvents:function() {
        /*jQuery('.WorkflowFolderImageSelector').bind("change", function() {
            setColorLayer(currentColorPicker[0], this.value);

        }).jscolor();*/
        //var myPicker = new jscolor.color(jQuery('.WorkflowFolderImageSelector'), {pickerClosable:true, showlast:lastColors});

        jQuery('.exportWFBtn').on('click', function(e) {
            var workflowId = jQuery(this).data('id');
            if(typeof e.shiftKey != 'undefined' && e.shiftKey === true) {
                bootbox.confirm('Please confirm to export without version limits!', function(response) {
                    if(response === true) {
                        var version = '7.00.00';
                    } else {
                        var version = '';
                    }

                    window.location.href='index.php?module=Workflow2&action=Export&parent=Settings&workflow=' + workflowId +'&passwd=&version=' + version;
                });

                return;
            }

            window.location.href='index.php?module=Workflow2&action=Export&parent=Settings&workflow=' + workflowId +'&passwd=';
        });

        jQuery('.WFChangeVisibility').on('click', function(e) {
            var id = jQuery(e.currentTarget).closest('tr').data('id');
            var value = jQuery(e.currentTarget).data('value');

            jQuery.post('index.php', {
                module:'Workflow2',
                parent:'Settings',
                action:'WorkflowVisibility',
                id: id,
                value: value
            },function () {
                Workflow2Admin.refreshPage();
            });
        });

        jQuery('.addWorkflowButton').on('click', function(e) {
            RedooAjax('Workflow2').postSettingsView('CreateWorkflowPopup', {targetModule:jQuery('#overviewModule').val()}).then(function(response) {
                RedooUtils('Workflow2').showModalBox(response).then(function(data) {

                    jQuery('#new_workflow_module').on('change', function(e) {
                        if(jQuery(e.currentTarget).val() == UsersModID) {
                            jQuery('#workflow_trigger').select2('val', 'WF2_FRONTENDTRIGGER');
                            jQuery('.ShowUsers').slideDown('fast');
                            jQuery('#workflow_trigger option[value!="WF2_FRONTENDTRIGGER"]').prop('disabled', true);
                        } else {
                            jQuery('#workflow_trigger option[value!="WF2_FRONTENDTRIGGER"]').prop('disabled', false);
                            jQuery('.ShowUsers').slideUp('fast');
                        }
                    });

                    jQuery('[type="submit"]', data).on('click', function() {
                        var newModuleName = jQuery('form.newWorkflowPopup [name="new_workflow_module"]').val();

                        RedooAjax('Workflow2').postSettingsAction('CreateWorkflow', {
                            'workflow_module': newModuleName,
                            'workflow_folder': jQuery('form.newWorkflowPopup [name="WorkflowFolderName"]').val(),
                            'workflow_trigger': jQuery('#workflow_trigger').val()
                        }, 'json').then(function(response) {
                            window.location.href = 'index.php?module=Workflow2&view=Config&parent=Settings&workflow=' + response.id;
                        });

                        return false;
                    });

                });
            });
        });

        jQuery('#overviewModule').on('change', function() {
            Workflow2Admin.MainModule = jQuery(this).val();
            Workflow2Admin.refreshPage();

            history.pushState({targetModule:Workflow2Admin.MainModule}, 'Module ' + Workflow2Admin.MainModule, "index.php?module=Workflow2&view=Index&parent=Settings&targetModule=" + Workflow2Admin.MainModule);
        }).select2();
        jQuery('#overviewFolder').on('change', function() {
            Workflow2Admin.TargetFolder = jQuery(this).val();
            Workflow2Admin.refreshPage();

            history.pushState({targetFolder:Workflow2Admin.TargetFolder}, 'Folder ' + Workflow2Admin.TargetFolder, "index.php?module=Workflow2&view=Index&parent=Settings&targetFolder=" + Workflow2Admin.TargetFolder);
        }).select2();

        jQuery('.SearchField').on('click', function(e) {
            if(jQuery('.WorkflowSearchContainer').length > 0) {
                jQuery('#WorkflowSearch').val('').trigger('keyup');
                jQuery('.WorkflowSearchContainer').slideUp('fast', function() {
                    jQuery('.WorkflowSearchContainer').remove();
                });
                return;
            }

            var target = jQuery(e.currentTarget);
            var position = target.offset();
            var html = '<div class="WorkflowSearchContainer" style="display:none;position:absolute;top:' + (position.top + 30) +'px;left:' + (position.left) +'px;background-color:#ffffff;padding:5px;border:1px solid #000;"><input type="text" placeholder="' + app.vtranslate('Filter Workflows') +'" style="margin:0;border:1px solid #ccc;box-shadow:none;" id="WorkflowSearch" /></div>';
            jQuery('body').append(html);
            jQuery('.WorkflowSearchContainer').slideDown('fast');

            jQuery('#WorkflowSearch').on('keyup', function(e) {
                var value = this.value;

                if(value.length >= 3) {
                    jQuery('.SearchFound').removeClass('SearchFound');
                    jQuery('div#listViewContents .workflowModuleHeader').addClass('SearchNotFound');
                    jQuery('div#listViewContents .workflowOverview').addClass('SearchNotFound');
                    jQuery('div#listViewContents .WorkflowModuleTable ').addClass('SearchNotFound');
                    jQuery('div#listViewContents .workflowOverview[data-search*="' + value.toLowerCase() + '"]').addClass('SearchFound').closest('.WorkflowModuleTable').addClass('SearchFound').prev().addClass('SearchFound');
                } else {
                    jQuery('.SearchFound').removeClass('SearchFound');
                    jQuery('.SearchNotFound').removeClass('SearchNotFound');
                }
            });

        });

        jQuery('.MoveToNewFolder').on('click', function(e) {
            var newfolder = prompt(app.vtranslate('Name of new Folder?', 'Settings:Workflow2'));
            var parentId = jQuery(e.currentTarget).closest('tr').data('id');

            jQuery.post('index.php', {module:'Workflow2', 'action':'CreateNewFolder', 'parent':'Settings', 'workflowId':parentId,'newfolder':newfolder}, function() {
                Workflow2Admin.refreshPage();
            });
        });
        jQuery('.WorkflowFolder .WorkflowFolderTitle').on('click', function(e) {
            var target = jQuery(e.currentTarget).closest('.WorkflowFolder');

            if(target.hasClass('FolderClosed')) {
                target.addClass('FolderOpened').removeClass('FolderClosed');

                jQuery.post('index.php', {module:'Workflow2', 'action':'SetTabVisibility', 'parent':'Settings', 'foldermode':1, 'target':target.data('folder'), visible:1});
            } else {
                target.addClass('FolderClosed').removeClass('FolderOpened');

                jQuery.post('index.php', {module:'Workflow2', 'action':'SetTabVisibility', 'parent':'Settings', 'foldermode':1, 'target':target.data('folder'), visible:0});
            }
        });
        jQuery('.editFolderTitle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var parentFolder = jQuery(e.currentTarget).closest('.WorkflowFolder');
            var currentName = parentFolder.data('folder');
            var html = '<input type="text" class="WorkflowFolderTitleEditor" value="' + currentName + '" />&nbsp;&nbsp;<i style="margin-top:2px;cursor:pointer;" class="icon-ok EditorSave"></i>&nbsp;&nbsp;<i style="margin-top:2px;cursor:pointer;" class="icon-remove EditorCancel"></i>';

            parentFolder.find('.WorkflowFolderTitleContainer').html(html);

            parentFolder.find('.WorkflowFolderTitleContainer').on('click', function(e) {
                e.stopPropagation();
            });
            parentFolder.find('.EditorSave').on('click', function(e) {
                e.stopPropagation();

                var newTitle = parentFolder.find('.WorkflowFolderTitleEditor').val();

                var found = false;
                jQuery('.WorkflowFolder').each(function(index, ele) {
                    if(jQuery(ele).data('folder') == newTitle) {
                        found = true;
                        return false;
                    }
                });
                if(found === true) {
                    jQuery.notification(
                        {
                            title: 'Not possible',
                            error: true,
                            timeout: 5000,
                            content: "Folder already exist !"
                        }
                    );
                    return;
                }

                jQuery.post('index.php', {module:'Workflow2', 'action':'RenameFolder','parent':'Settings', oldtitle:parentFolder.data('folder'), newtitle:newTitle}, function() {
                    jQuery.notification(
                        {
                            title: "Successfully",
                            timeout: 5000,
                            color: '#2cae35',
                            icon: 'W',
                            content: 'Folder was renamed'
                        }
                    );
                });

                parentFolder.find('.WorkflowFolderTitleContainer').html('<b>&nbsp;' + newTitle + '</b>');
            });
            parentFolder.find('.EditorCancel').on('click', function(e) {
                e.stopPropagation();
                parentFolder.find('.WorkflowFolderTitleContainer').html('<b>&nbsp;' + parentFolder.data('folder') + '</b>');
            });
        });
        if(jQuery('.WorkflowFolder tbody').length > 0) {
            jQuery( ".WorkflowList tbody" ).sortable({
                connectWith: ".WorkflowList tbody",
                //helper: 'clone',
                helper:function(event, items) {
                    return jQuery('<td colspan="7" style="font-size:14px;padding:0 0 0 30px;line-height:40px;font-weight:bold;color:#aaa;letter-spacing: 1px;background-color:#fff;">' + jQuery(items[0]).data('name') + '</td>')
                },
                distance:65,
                placeholder: "folderview-sort-placeholder",
                'start': function (event, ui) {
                    jQuery( ".WorkflowList tbody").each(function(index, ele) {
                        if(jQuery(ele).height() == 0) {
                            jQuery(ele).append('<tr class="sort-disabled"><td></td></tr>');
                        }
                    });

                    ui.placeholder.html('<td colspan="7" style="font-size:18px;padding-left:30px;font-weight:bold;color:#aaa;letter-spacing: 1px;">' + jQuery(ui.item[0]).data('name') + '</td>');
                },
                stop: function() {
                    jQuery('tr.sort-disabled').remove();
                },
                'update':function(event, ui) {
                    var folder = jQuery(ui.item[0]).closest('.WorkflowFolder');

                    jQuery.post('index.php', {module:'Workflow2', 'parent':'Settings', 'action': 'SetWorkflowFolder', workflow:jQuery(ui.item[0]).data('id'), folder:folder.data('folder')});
                }
            }).disableSelection();
        }

        jQuery('.workflowModuleHeader .toggleImage').on('click', function() {
            var header = jQuery(this).closest('.workflowModuleHeader');
            var target = header.data('target');
            var visible = jQuery('#workflowList' + target).data('visible');

            if(visible == '0') {
                jQuery('#workflowList' + target).show();
                jQuery('.toggleImageExpand', header).hide();
                jQuery('.toggleImageCollapse', header).show();
                jQuery('#workflowList' + target).data('visible', 1);

                jQuery.post('index.php', {module:'Workflow2', 'action':'SetTabVisibility','parent':'Settings', 'target':target, visible:1});
            } else {
                jQuery('#workflowList' + target).hide();
                jQuery('.toggleImageExpand', header).show();
                jQuery('.toggleImageCollapse', header).hide();
                jQuery('#workflowList' + target).data('visible', 0);

                jQuery.post('index.php', {module:'Workflow2', 'action':'SetTabVisibility','parent':'Settings', 'target':target, visible:0});
            }

        });

    },
    refreshPage:function() {
        RedooUtils('Workflow2').refreshContent('Index', true, {
            targetModule:Workflow2Admin.MainModule !== null ? Workflow2Admin.MainModule : '',
            targetFolder:Workflow2Admin.TargetFolder !== null ? Workflow2Admin.TargetFolder : ''
        }).then(function() {
            Workflow2Admin.initEvents();
        });
    }
};

jQuery(function() {
    Workflow2Admin.initEvents();

    jQuery(window).on("popstate", function(data) {
        if(typeof data.state.targetModule == 'undefined' && typeof data.state.targetFolder == 'undefined') return;

        Workflow2Admin.MainModule = data.state.targetModule;
        Workflow2Admin.TargetFolder = data.state.targetFolder;
        Workflow2Admin.refreshPage();
    });
});

function importWorkflow() {
    RedooAjax('Workflow2').postSettingsView('WorkflowImporter').then(function(response) {
        RedooUtils('Workflow2').showModalBox(response).then(function(data) {
            RedooUtils('Workflow2').loadScript('modules/Workflow2/views/resources/js/jquery.form.min.js').then(function() {
                var options = {
                    //target:        '#output1',   // target element(s) to be updated with server response
                    //beforeSubmit:  showRequest,  // pre-submit callback
                    success:       function(response) {

                        if(response.result == 'ok') {
                            jQuery.notification(
                                {
                                    title: "Workflow Import",
                                    timeout: 5000,
                                    color: '#2cae35',
                                    icon: 'W',
                                    content: 'Workflow was imported'
                                }
                            );
                            app.hideModalWindow();

                            refreshWorkflowList();
                        }
                        if(response.result == 'error') {
                            jQuery.notification(
                                {
                                    title: "Error during Import",
                                    error: true,
                                    timeout: 5000,
                                    content: response.message
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
                jQuery('#WorkflowImportForm').ajaxForm(options);

                jQuery('#modalSubmitButton').removeAttr('disabled');

            });
        });
    });
}