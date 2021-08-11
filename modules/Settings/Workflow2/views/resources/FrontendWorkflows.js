/**
 * Created by Stefan on 13.11.2016.
 */
var workflowID = 1;

(function($) {
    $(function() {
        function backupToggleable() {
            var panelids = [];
            jQuery('.Toggleable.Visible', '.contentsDiv').each(function(index, ele) {
                panelids.push($(ele).data('panelid'));
            });
            window._serializedOpenToggleables = panelids;
        }
        function restoreToggleable() {
            if(typeof window._serializedOpenToggleables == 'undefined') return;

            jQuery.each(window._serializedOpenToggleables, function(index, panelid) {
                $('.Toggleable[data-panelid="' + panelid + '"]', '.contentsDiv' ).toggleClass('Visible Invisible');
            });
        }


        var FrontendConfig = {
            addEvents:function() {
                $('#addWorkflowButton').on('click', function(e) {
                    var wfId = $('#addWorkflow').val();
                    if(wfId == '') return;

                    RedooAjax('Workflow2').postAction('FrontendWorkflowAdd', {
                        wfid:wfId
                    }, true).then(FrontendConfig.refresh);
                });

                $('.ActivateToggle').on('change', function(e) {
                    var entry = $($(e.currentTarget).closest('.WorkflowFrontendContentIntern'));

                    RedooAjax('Workflow2').postAction('FrontendWorkflowActivate', {
                        'id': entry.data('id'),
                        'status': $(e.currentTarget).prop('checked') ? 1 : 0
                    }, true);
                });

                $('.ToggleHandler').on('click', function(e) {
                    $(e.currentTarget).closest('.Toggleable').toggleClass('Visible Invisible');
                });

                $('.editConfig').on('click', function(e) {
                    var triggerID = jQuery(e.currentTarget).closest('.ToggleContent').data('id');
                    workflowID = jQuery(e.currentTarget).closest('.ToggleContent').data('workflowid');
                    FrontendConfig.openEditor(triggerID);

                    e.preventDefault();
                    e.stopPropagation();
                });

                $('.deleteConfig').on('click', function(e) {
                    var triggerID = jQuery(e.currentTarget).closest('.ToggleContent').data('id');

                    bootbox.confirm(app.vtranslate('Please confirm'), function(response) {
                        if(response === false) return;

                        RedooAjax('Workflow2').postAction('RemoveFrontendTrigger', {triggerID: triggerID}, true).then(function() {
                            console.log(jQuery('.WorkflowFrontendContainerIntern[data-panelid="wf_' + triggerID + '"]'));
                            jQuery('.WorkflowFrontendContainerIntern[data-panelid="wf_' + triggerID + '"]').remove();
                        });
                    });

                    e.preventDefault();
                    e.stopPropagation();
                });
            },
            openEditor:function(triggerID) {
                RedooAjax('Workflow2').postView('FrontendWorkflowEditor', { id:triggerID }, true).then(function(html) {
                    RedooUtils('Workflow2').showModalBox(html).then(function(data) {
                        var data = $(data);
                        var quickCreateForm = data.find('form[name="FrontendWorkflowEditor"]');

                        jQuery(quickCreateForm).submit(function(e) {
                            e.preventDefault();

                            var options = {
                                success:       function(data) {
                                    FrontendConfig.refresh();
                                },
                                dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
                            };

                            jQuery(this).ajaxSubmit(options);

                            return false;
                        });
                    });
                });
            },
            refresh:function() {
                backupToggleable();
                RedooUtils('Workflow2').refreshContent('FrontendWorkflowConfig', true).then(function() {
                    FrontendConfig.addEvents();
                    restoreToggleable();
                });
            },
        }

        FrontendConfig.addEvents();
    });
})(jQuery);