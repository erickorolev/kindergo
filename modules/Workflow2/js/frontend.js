(function () {
/** DetailView **/

"use strict";
jQuery('.listViewActionsDiv .addButton[onclick*="runListViewWorkflow"]').each(function(index, ele) {
    var html = jQuery('strong', this).html();

    jQuery(this).html('<strong>' + html + '</strong>');
    var onclick = jQuery(this).attr('onclick');

    if(onclick.indexOf('//#') != -1) {
        var parts = onclick.split('//');
        parts[1] = parts[1].replace(';','');

        jQuery(this).css('backgroundColor', parts[1]);
    }
});
//hh
var WorkflowRecordMessages = [];
function startWorkflowById(workflow, crmid, async) {
    if(typeof async == "undefined") {
        async = true;
    }
    if(async != true) {
        async = false;
    }

    if(typeof crmid == "undefined") {
        crmid = "0";
    }
    if(typeof workflow == "undefined") {
        return false;
    }

    var html = "<div id='workflow_executer' style='width:150px;height:150px;background-image:url(modules/Workflow2/icons/modal_white.png);border:1px solid #777777; box-shadow:0 0 2px #ccc; position:absolute;top:100px;right:300px;text-align:center;'><br><br><img src='modules/Workflow2/icons/sending.gif'><br><br><strong>Executing Workflow ...</strong></div>";
    jQuery("body").append(html);
    jQuery.ajax("index.php", {
        async: async,
        cache: false,
        data:{
            "module" : "Workflow2",
            "action" : "Workflow2Ajax",
            "file"   : "ajaxExecuteWorkflow",

            "crmid" : crmid,
            "workflow" : workflow
        },
        type: 'POST',
        dataType: 'json'
    }).always(function( response ) {
        jQuery("#workflow_executer").remove();

        if(response.result == "startfields") {
            var html = "<div style='position:absolute;background-color:#fff;border:3px solid #5890c9;box-shadow:0px 0px 5px #777;border-radius:3px;top:-100px;left:0px;width:200px;padding:5px;'><form method='POST' onsubmit='submitStartfields(" + '"' + response.workflow + '","' + crmid + '","' + module + '"' + ");return false;' id='wf_startfields'>";
            html += "<img src='modules/Workflow2/icons/cross-button.png' style='position:absolute;right:-6px;top:-6px;cursor:pointer;' onclick='jQuery(\"#startfieldsContainer\").fadeOut(\"fast\");'>";
            html += "<div class='small'>These Workflow requests some values.</div>";

            jQuery.each(response.fields, function(index, value) {
                var inputField = "";
                var fieldName = '' + value.name + '';

                switch(value.type) {
                    case "TEXT":

                        inputField = '<input type="text" style="width:180px;" name="' + fieldName + '" value="' + value.default + '">';
                        break;
                    case "CHECKBOX":
                        if(value.default === null) {
                            value.default = "off";
                        }
                        inputField = '<input type="checkbox" name="' + fieldName + '" ' + (value["default"].toLowerCase()=="on"?"checked='checked'":"") + ' value="on">';
                        break;
                    case "SELECT":
                        var splitValues = value["default"].split("\n");

                        inputField = '<select style="width:183px;" name="' + fieldName + '">';
                        jQuery.each(splitValues, function(index, value) {
                            var fieldValue = value;
                            var fieldKey = value;

                            if(value.indexOf("#~#") != -1) {
                                var parts = value.split("#~#");
                                fieldValue = parts[0];
                                fieldKey = parts[1];
                            }

                            inputField += "<option value='" + fieldKey + "'>" + fieldValue + "</option>";
                        });
                        inputField += '</select>';

                        break;
                    case "DATE":
                        inputField = '<input style="width:130px;" type="text" name="' + fieldName + '" id="'+fieldName+'" value="' + value["default"] + '">';
                        inputField += '<img src="modules/Workflow2/icons/calenderButton.png" style="margin-bottom:-8px;cursor:pointer;" id="jscal_trigger_' + fieldName + '">';
                        inputField += '<script type="text/javascript">Calendar.setup ({inputField : "' + fieldName + '", ifFormat : "%Y-%m-%d", button:"jscal_trigger_' + fieldName + '",showsTime : false, singleClick : true, step : 1});</script>';

                        break;
                }

                html += "<label><div style='overflow:hidden;min-height:26px;padding:2px 0;'><div style='" + (value.type=="CHECKBOX"?"float:left;":"") + "'><strong>"+ value.label + "</strong></div><div style='text-align:right;'>" + inputField + "</div></div></label>";
            });
            html += "<input type='submit' name='submitStartField' value='Execute Workflow' class='button small edit'>";
            html += "</form></div>";

            jQuery("#startfieldsContainer").hide();
            jQuery("#startfieldsContainer").html(html);
            jQuery("#startfieldsContainer").fadeIn("fast");
        }
    });

}

function reloadWFDWidget() {
    var widgetContainer = jQuery('div.widgetContainer#' + jQuery("#module").val() + '_sideBar_Workflows');
    var key = widgetContainer.attr('id');
    FlexUtils('Workflow2').cacheSet(key, 0);

    widgetContainer.html('');

    // Vtiger_Index_Js.loadWidgets(widgetContainer);
}

window.continueWorkflow = function (execid, crmid, block_id) {
    var Execution = new WorkflowExecution();
    Execution.setContinue(execid, block_id);
    Execution.execute();

}

window.stopWorkflow = function(execid, crmid, taskid, direct) {
    if(typeof direct == 'undefined' || direct != true) {
        if(!confirm("stop Workflow?"))
            return;
    }

    jQuery.post("index.php?module=Workflow2&action=QueueStop", {
            "crmid" : crmid,
            "execID" : execid,
            "taskID" : taskid
        },
        function(response) {
            reloadWFDWidget();
        }
    );

    return false;
}

/** ListView **/
function executeWorkflow(button, module, selection) {
    var selectedIDs = "";

    if(typeof selection == "undefined") {
        selectedIDs = jQuery('#allselectedboxes').val().split(";");
        selectedIDs = selectedIDs.join(";");
    } else {
        selectedIDs = selection.join(";");
    }

    if (jQuery("#Wf2ListViewPOPUP").length == 0)
    {
        var div = document.createElement('div');
        div.setAttribute('id','Wf2ListViewPOPUP');
        div.setAttribute('style','display:none;width:350px; position:absolute;');
        div.innerHTML = 'Loading';
        document.body.appendChild(div);

        //      for IE7 compatiblity we can not use setAttribute('style', <val>) as well as setAttribute('class', <val>)
        newdiv = document.getElementById('Wf2ListViewPOPUP');
        newdiv.style.display = 'none';
        newdiv.style.width = '400px';
        newdiv.style.position = 'absolute';
    }

    jQuery('#status').show();

    currentListViewPopUpContent = "#wf2popup_wf_execute";

    jQuery.post("index.php", {
        "module" : "Workflow2",
        "action" : "Workflow2Ajax",
        "file"   : "ListViewPopup",

        "return_module" : module,
        "record_ids"    : selectedIDs
    }, function(response) {
        jQuery("#Wf2ListViewPOPUP").html(response);

        fnvshobj(button,'Wf2ListViewPOPUP');

        var EMAILListview = document.getElementById('Wf2ListViewPOPUP');
        var EMAILListviewHandle = document.getElementById('Workflow2ViewDivHandle');
        Drag.init(EMAILListviewHandle,EMAILListview);

        jQuery('#status').hide();

    });

}

var currentListViewPopUpContent = "#wf2popup_wf_execute";
function showWf2PopupContent(id) {
    jQuery(currentListViewPopUpContent + "_TAB").addClass("deactiveWf2Tab");
    jQuery(id + "_TAB").removeClass("deactiveWf2Tab");
    jQuery(currentListViewPopUpContent).hide();
    jQuery(id).show();
    currentListViewPopUpContent= id;

    if(id == "wf2popup_wf_importer") {
        jQuery("#execute_mode").val("execute");
    } else {
        jQuery("#execute_mode").val("import");
    }
}

function executeLVWorkflow() {
    if(jQuery("#execute_mode").val() == "import") {
        return true;
    }

    var record_ids = jQuery("#WFLV_record_ids").val();
    var return_module = jQuery("#WFLV_return_module").val();
    var workflow = jQuery("#exec_this_workflow").val();
    var parallel = jQuery("#exec_workflow_parallel").attr("checked")=="checked"?1:0;

    var ids = record_ids.split("#~#");

    jQuery("#executionProgress_Value").html("0 / " + ids.length);
    jQuery("#executionProgress").show();

    jQuery.ajaxSetup({async:false});
    var counter = 0;

    jQuery.each(ids, function(index, value) {
        jQuery.post("index.php?module=Workflow2&action=Workflow2Ajax&file=ajaxExecuteWorkflow", {
                "crmid" : value,
                "return_module" : return_module,
                "workflow" : workflow,
                "allow_parallel" : parallel
            }
        );
        counter = counter + 1;
        jQuery("#executionProgress_Value").html(counter + " / " + ids.length);
    });
    jQuery.ajaxSetup({async:true});

    jQuery("#executionProgress_Value").html("100%");

    if(currentListViewPopUpContent == "#wf2popup_wf_execute") {
        return false;
    }
}
var ENABLEredirectionOrReloadAfterFinish = true;
var WorkflowMetaData = {};
var WithinRecordLessWF = false;

function runListViewWorkflow(workflowId, couldStartWithoutRecord, collection_process) {
    if(typeof couldStartWithoutRecord === 'undefined' && typeof collection_process === 'undefined') {

        if(typeof WorkflowMetaData[workflowId] === 'undefined') {
            FlexAjax('Workflow2').postAction('WorkflowInfo', {workflow_id: workflowId}, 'json').then(function (workflowInfo) {
                WorkflowMetaData[workflowId] = workflowInfo;

                runListViewWorkflow(workflowId);
            });
            return false;
        } else {
            couldStartWithoutRecord = WorkflowMetaData[workflowId].withoutrecord;
            collection_process = WorkflowMetaData[workflowId].collection_process;
        }
    }

    if(WithinRecordLessWF === true)  {
        couldStartWithoutRecord = false;
    }
    if(typeof couldStartWithoutRecord === 'undefined') {
        couldStartWithoutRecord = false;
    }
    if(typeof collection_process === 'undefined') {
        collection_process = false;
    }

    var processSettings = {};
    if(typeof WorkflowDesignerProcessSettings == 'undefined' || typeof WorkflowDesignerProcessSettings[workflowId] == 'undefined') {
        processSettings = {'withoutrecord': couldStartWithoutRecord, 'collection_process' : collection_process};
    } else {
        processSettings = WorkflowDesignerProcessSettings[workflowId];
    }

    var listInstance = window.app.controller();
    var params = listInstance.getListSelectAllParams(false);

    if(params !== false) {
        var selectedIds = params.selected_ids;
    } else {
        var selectedIds = [0];
    }

    RedooUtils('Workflow2').blockUI({
        title: 'Executing ... ',
        message: '<p style="margin:20px 0px;font-size:14px;"><strong style="text-transform:uppercase;">' + FLEXLANG('Please wait', 'Workflow2') + ' ...&nbsp;&nbsp;&nbsp;&nbsp;</strong><span id="workflowDesignerDone">0</span> of <span id="workflowDesignerTotal">' + (selectedIds!='all'?selectedIds.length:'?') + '</span> done</p><div style="margin: 10px 10px 10px 10px;" id="executionProgress" class="progress"><div class="progress-bar progress-bar-success progress-bar-striped" style="width: 0;"></div></div>',
        theme: false,
        css: {
            'backgroundColor':'#2d3e49',
            'color': '#ffffff',
            'border': '1px solid #fff'
        },
        onBlock: function() {
            var counter = -1;

            if(selectedIds == 'all') {
                jQuery.ajaxSetup({async:false});
                var parameter = listInstance.getDefaultParams();
                parameter.module = 'Workflow2';
                parameter.view = undefined;
                parameter.action = 'GetSelectedIds';

                jQuery.post('index.php', parameter, function(response) {
                    selectedIds =  response.ids;
                }, 'json');

                jQuery('#workflowDesignerTotal').html(selectedIds.length);
                jQuery.ajaxSetup({async:true});
            }

            var totalIds = selectedIds.length;

            if(selectedIds.length > 1) {
                ENABLEredirectionOrReloadAfterFinish = false;
            }

            var couldStartWithoutRecord = false;

            if(selectedIds.length == 0 && processSettings.withoutrecord == false) {
                alert('Please choose a record to execute');
                RedooUtils('Workflow2').unblockUI();
                return;
            }
            /*
            if(selectedIds.length == 0) {
                couldStartWithoutRecord = true;
            }
*/
            if(processSettings.collection_process == "1") {
                var workflow = new Workflow();
                ENABLEredirectionOrReloadAfterFinish = true;
                workflow.setRequestedData({ 'recordids':selectedIds.join(',') }, 'collection_recordids');

                var crmid = selectedIds.shift();

                workflow.execute(workflowId, crmid, function(response) {
                    RedooUtils('Workflow2').unblockUI();
                    if(typeof response.redirection == "undefined") {
                        window.location.reload();
                    }

                    return true;
                });
                return;
            }
            if(processSettings.withoutrecord == "1" && selectedIds.length == 1 && selectedIds[0] == 0) {
                var workflow = new Workflow();
                ENABLEredirectionOrReloadAfterFinish = true;
                var crmid = 0;

                WithinRecordLessWF = true;
                workflow.execute(workflowId, crmid, function(response) {
                    RedooUtils('Workflow2').unblockUI();
                    if(typeof response.redirection == "undefined") {
                        window.location.reload();
                    }

                    return true;
                }, true);
                return;
            }

            function _executeCallback() {
                counter = counter + 1;
                jQuery('#workflowDesignerDone').html(counter);
                var crmid = selectedIds.shift();

                if(couldStartWithoutRecord === true) {
                    crmid = 0;
                    couldStartWithoutRecord = false;
                }

                var progress = Math.round(((totalIds - selectedIds.length) / totalIds) * 100);
                jQuery('#executionProgress .progress-bar-success').css('width', progress + '%');

                if(typeof crmid !== 'undefined') {
                    var workflow = new Workflow();
                    workflow.setBackgroundMode(true);
                    workflow.execute(workflowId, crmid, _executeCallback);
                } else {
                    RedooUtils('Workflow2').unblockUI();
                    window.location.reload();
                }
            }

            _executeCallback();
        }
    });

}
function runListViewSidebarWorkflow() {
    runListViewWorkflow(jQuery("#workflow2_workflowid").val(), jQuery("#workflow2_workflowid option:selected").data('withoutrecord') == '1');
}
function runSidebarWorkflow(crmid) {
    if(jQuery("#workflow2_workflowid").val() == "") {
        return;
    }

    var workflow = new Workflow();
    workflow.execute(jQuery("#workflow2_workflowid").val(), crmid);
}


function WorkflowWidgetLoaded() {
    jQuery('.WFdivider', '#WorkflowDesignerWidgetContainer').each(function(index, element) {
        if(jQuery(element).next().length == 0 || jQuery(element).next().hasClass('WFdivider')) {
            jQuery(element).hide();
        }
    });
}
var WFDvisibleMessages = {};


var WorkflowHandler = {
    startImport : function(moduleName) {
        RedooAjax('Workflow2').postView('ImportModal', {target_module:moduleName}).then(function(response) {
            RedooUtils('Workflow2').hideModalBox();
            RedooUtils('Workflow2').showContentOverlay(response).then(function() {
                RedooUtils('Workflow2').loadScript('modules/Workflow2/js/Importer.js').then(function() {
                    var Import = new Importer();
                    Import.init();
                });
            });
        });

        /*
        jQuery.post('index.php?module=Workflow2&view=ImportStep1', { source_module: source_module, currentUrl: window.location.href },  function(html) {
            app.showModalWindow(html, function(data) {
                jQuery('#modalSubmitButton').removeAttr('disabled');
            });
        });
        */
    }
};
window.WorkflowHandler = WorkflowHandler;

function showEntityData(crmid) {
    jQuery.post('index.php?module=Workflow2&view=EntityData', { crmid:crmid },  function(html) {
        app.showModalWindow(html, function(data) {
            jQuery('.EntityDataDelete').on('click', function(e) {
                var dataid = jQuery(e.currentTarget).data('id');

                jQuery.post('index.php', {
                    'module':'Workflow2',
                    'action':'EntityDataDelete',
                    'dataid':dataid
                }, function() {
                    showEntityData(crmid);
                });
            });
        });
    });
}
var workflowObj;
window.closeForceNotification = function(messageId) {
    jQuery.post('index.php?module=Workflow2&action=MessageClose', { messageId:messageId, force: 1 });
}
var UserQueue = {
    run: function(exec_id, block_id) {
        var Execution = new WorkflowExecution();

        Execution.setContinue(exec_id, block_id);

        Execution.execute();
    }
};

var WorkflowPermissions = {
    returnCounter:0,
    submit: function(execID, confID, hash, result) {
        if(jQuery('#row_' + confID).data('already') == '1') {
            if(!confirm('Permission already set. Set again?')) {
                return;
            }
        }

        var execution = new WorkflowExecution();
        execution.setCallback(function(response) {  });

        execution.setContinue(execID, 0);
        //execution.enableRedirection(false);
        execution.submitRequestFields('authPermission', [{name:'permission', value: result}, {name:'confid', value: confID}, {name:'hash', value: hash}], {}, jQuery('.confirmation_container'));

        var row = jQuery('#row_' + confID);
        jQuery('.btn.decision', row).removeClass('pressed').addClass('unpressed');
        jQuery('.btn.decision_' + result, row).addClass('pressed').removeClass('unpressed');

        return false;
    },
    submitAll:function(blockId, result) {
        /*if(jQuery('table.block' + blockId + ' [data-already="1"]').length > 0) {
         if(!confirm('This will overwrite every already defined value! Continue?')) {
         return;
         }
         }*/

        jQuery('table.block' + blockId + ' .permissionRow input.selectRows:checked').each(function(index, value) {
            var row = jQuery(this).closest('.permissionRow');

            var confId = jQuery(row).data('id');
            var execID = jQuery(row).data('execid');
            var hash = jQuery(row).data('hash');

            WorkflowPermissions.returnCounter++;
            var execution = new WorkflowExecution();
            execution.setCallback(function(response) { WorkflowPermissions.returnCounter--; if(WorkflowPermissions.returnCounter == 0) window.location.reload(); });

            execution.setContinue(execID, 0);
            execution.enableRedirection(false);
            execution.submitRequestFields('authPermission', [{name:'permission', value: result}, {name:'confid', value: confId}, {name:'hash', value: hash}], {}, jQuery('.confirmation_container'));
        });

        return false;
    }
};
window.WorkflowPermissions = WorkflowPermissions;

var WorkflowFrontendTypes = {
    getWorkflows:function(type, module, crmid) {
        if(typeof type === 'undefined') {
            console.error('You do not define a FrontendType. Please check!');
            return;
        }
        if(typeof module === 'undefined') {
            console.error('You do not define a Module of FrontendTypes. Please check!');
            return;
        }

        if(typeof crmid === 'undefined' && typeof WFDFrontendConfig !== 'undefined' && typeof WFDFrontendConfig[type] !== 'undefined' && typeof WFDFrontendConfig[type][module] !== 'undefined') {
            var aDeferred = jQuery.Deferred();

            var result = [];
            jQuery.each(WFDFrontendConfig[type][module], function(index, value) {
                var tmp = value.config;

                tmp.workflow_id = value.workflowid;
                tmp.module = module;
                tmp.label = value.label;
                tmp.color = value.color;
                tmp.textcolor = value.textcolor;
                result.push(tmp);
            });

            aDeferred.resolveWith({}, [result]);

            return aDeferred.promise();
        }

        return FlexAjax('Workflow2').postAction('FrontendLinks', {
            'type'      :   type,
            'target_module'    :   module,
            'target_crmid'     :   crmid
        }, 'json');

    },
    trigggerWorkflow:function(type, workflowid, crmid, envVars) {
        var dfd = jQuery.Deferred();

        var execution = new WorkflowExecution();
        execution.setCallback(function(response) {
            var workflowFrontendActions = new Workflow();
            workflowFrontendActions.checkFrontendActions('init', crmid);

            dfd.resolve();
            return false;
        });

        execution.init(crmid);
        execution.setWorkflowById(workflowid);

        if(typeof envVars === 'undefined') {
            var envVars = {};
        }

        execution.setFrontendType(type);
        execution.setEnvironment(envVars);
        execution.execute();

        return dfd.promise();
    }
};
window.WorkflowFrontendTypes = WorkflowFrontendTypes;
;/*
 * @copyright 2016-2018 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */
!function(l){"use strict";var r="Workflow2",a={postAction:function(e,t,i,n){return void 0===t&&(t={}),t.module=r,t.action=e,void 0===n&&"string"==typeof i&&(n=i,i=!1),void 0!==i&&1==i&&(t.parent="Settings"),a.post("index.php",t,n)},postSettingsView:function(e,t,i){return a.postView(e,t,!0,i)},postSettingsAction:function(e,t,i){return a.postAction(e,t,!0,i)},postView:function(e,t,i,n){return void 0===t&&(t={}),t.module=r,t.view=e,void 0===n&&"string"==typeof i&&(n=i,i=!1),void 0!==i&&1==i&&(t.parent="Settings"),a.post("index.php",t,n)},post:function(e,t,a){var l=jQuery.Deferred();"object"==typeof e&&(t=e,e="index.php"),void 0===t&&(t={}),void 0===a&&void 0!==t.dataType&&(a=t.dataType);var d={url:e,data:t};return void 0!==a&&(d.dataType=a),d.dataType="text",d.type="POST",jQuery.ajax(d).always(function(t){if(void 0!==a&&"json"==a)try{t=jQuery.parseJSON(t)}catch(e){B.unblockUI(),console.error("FlexAjax Error - Should: JSON Response:"),console.log("Request: ",d),console.log(t);var i=10;jQuery(".RedooAjaxError").each(function(e,t){i+=jQuery(t).height()+30});var n="error_"+Math.floor(1e6*Math.random()),o=t.substr(0,500).replace(/</g,"&lt;").replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}});500<t.length&&(o+=" .....<em>shortened</em>....... "+t.substr(-500).replace(/</g,"&lt;").replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}}));var r='<div class="RedooAjaxError" style="word-wrap:break-word;position:fixed;bottom:'+i+'px;box-sizing:border-box;left:10px;padding:10px;width:25%;background-color:#ffffff;z-index:90000;border:2px solid #C9331E;background-color:#D29D96;" id="'+n+'"><i class="icon-ban-circle" style="margin-top:2px;margin-right:5px;"></i><span style="color:#C9331E;font-weight:bold;">ERROR:</span> '+e+'<br/><span style="color:#C9331E;font-weight:bold;">Response:</span>'+o+"</div>";return jQuery("body").append(r),void jQuery("#"+n).on("click",function(){jQuery(this).fadeOut("fast",function(){jQuery(this).remove()})})}if(void 0!==t.success&&0==t.success&&-1!=t.error.code.indexOf("request"))return confirm("Request Error. Reload of Page is required.")&&window.location.reload(),void B.showNotification(t.error.message,!1);l.resolve(t)}),l.promise()},get:function(e,t,i){console.error("Vtiger do not support GET Requests")},request:function(e){return a.post("index.php",e)}},d={get:function(e,t){return void 0!==o[e]?o[e]:t},set:function(e,t){o[e]=t}},n={init:function(e,t){d.set("__translations_"+e,t)},getTranslator:function(){return function(e){return n.__(e)}},__:function(e){var t=app.getUserLanguage(),i=d.get("__translations_"+t,{});return"function"==typeof i?(n.init(t,i()),n.__(e)):void 0!==i[e]?i[e]:e}},B={layout:null,currentLVRow:null,listViewFields:!1,isVT7:function(){return void 0!==app.helper},showRecordInOverlay:function(e,t){window.open("index.php?module="+e+"&view=Detail&record="+t)},showNotification:function(e,t,i){void 0===t&&(t=!0),void 0===i&&(i={}),B.isVT7()&&(i.message=e,!0===t?app.helper.showSuccessNotification(i):app.helper.showErrorNotification(i))},cacheSet:function(e,t){if(B.isVT7())return app.storage.set(e,t)},cacheGet:function(e,t){if(B.isVT7())return app.storage.get(e,t)},cacheClear:function(e){if(B.isVT7())return app.storage.clear(e)},cacheFlush:function(){if(B.isVT7())return app.storage.flush()},getCurrentDateFormat:function(e){if(e=e.toLowerCase(),!1!==d.get("__CurrentDateFormat_"+e,!1))return d.get("__CurrentDateFormat_"+e,!1);var i,t={};switch(e){case"php":t={yyyy:"%Y",yy:"%y",dd:"%d",mm:"%m"};break;case"moment":t={yyyy:"YYYY",yy:"YY",dd:"DD",mm:"MM"}}return B.isVT7()&&(i=app.getDateFormat()),l.each(t,function(e,t){i=i.replace(e,t)}),d.set("__CurrentDateFormat_"+e,i),i},getCurrentCustomViewId:function(){return!0===B.isVT7()?l('input[name="cvid"]').val():jQuery("#customFilter").val()},selectRecordPopup:function(e,t){var i=jQuery.Deferred(),n=Vtiger_Popup_Js.getInstance();return B.isVT7()?("string"==typeof e&&(e={module:e,view:"Popup",src_module:"Emails",src_field:"testfield"}),void 0!==t&&!0===t&&(e.multi_select=1),app.event.off("FlexUtils.SelectRecord"),app.event.one("FlexUtils.SelectRecord",function(e,t){i.resolveWith(window,[jQuery.parseJSON(t)])}),n.showPopup(e,"FlexUtils.SelectRecord",function(e){})):("string"==typeof e&&(e={module:e,view:"Popup",src_module:"Emails",src_field:"testfield"}),void 0!==t&&!0===t&&(e.multi_select=1),n.show(e,function(e){i.resolveWith(e)})),i.promise()},getCurrentLayout:function(){if(null!==B.layout)return B.layout;var e=jQuery("body").data("skinpath").match(/layouts\/([^/]+)/);return 2<=e.length?(B.layout=e[1],e[1]):B.layout="vlayout"},getQueryParams:function(e){var t=window.document.URL.toString();if(0<t.indexOf("?")){var i=t.split("?")[1].split("&"),n=new Array(i.length),o=new Array(i.length),r=0;for(r=0;r<i.length;r++){var a=i[r].split("=");n[r]=a[0],""!=a[1]?o[r]=decodeURI(a[1]):o[r]="No Value"}for(r=0;r<i.length;r++)if(n[r]==e)return o[r]}return!1},onListChange:function(){if(0==d.get("__onListChangeSignal",!1)){var i=new B.Signal;app.event.on("post.listViewFilter.click",function(e,t){i.dispatch(t)}),d.set("__onListChangeSignal",i)}return d.get("__onListChangeSignal")},onRelatedListChange:function(){if(0==d.get("__onRelatedListChangeSignal",!1)){var i=new B.Signal;app.event.on("post.relatedListLoad.click",function(e,t){i.dispatch(t)}),d.set("__onRelatedListChangeSignal",i)}return d.get("__onRelatedListChangeSignal")},UUIDCounter:1,FieldChangeEventInit:!1,onFieldChange:function(e){void 0===e&&(e="div#page"),void 0===jQuery(e).data("fielduid")&&(jQuery(e).data("fielduid","parentEle"+B.UUIDCounter),B.UUIDCounter++);var r,t=jQuery(e).data("fielduid");if(jQuery(e).addClass("RedooFieldChangeTracker"),0==d.get("__onFieldChangeSignal"+t,!1)){if(r=new B.Signal,B.isVT7())!1===B.FieldChangeEventInit&&"undefined"!=typeof Vtiger_Detail_Js&&(app.event.on(Vtiger_Detail_Js.PostAjaxSaveEvent,function(e,t,i,n){var o=t.closest(".RedooFieldChangeTracker").data("fielduid");(r=d.get("__onFieldChangeSignal"+o)).dispatch({name:t.data("name"),new:i[t.data("name")].value},t,i,n)}),B.FieldChangeEventInit=!0);else if("listview"!==B.getViewMode()&&"undefined"!=typeof Vtiger_Detail_Js){var i=Vtiger_Detail_Js.getInstance(),a=i.getContentHolder();a.on(i.fieldUpdatedEvent,function(e,t){var i=l(e.target),n=i.attr("name"),o=i.closest(".RedooFieldChangeTracker").data("fielduid");(r=d.get("__onFieldChangeSignal"+o)).dispatch({name:n,new:t.new},t,{},a)})}d.set("__onFieldChangeSignal"+t,r)}else r=d.get("__onFieldChangeSignal"+t);return d.get("__onFieldChangeSignal"+t)},getRecordLabels:function(e){var t=jQuery.Deferred(),i=[],n=d.get("LabelCache",{});return jQuery.each(e,function(e,t){void 0===n[t]&&i.push(t)}),0<i.length?a.postAction("RecordLabel",{ids:i,dataType:"json"}).then(function(e){jQuery.each(e.result,function(e,t){n[e]=t}),d.set("LabelCache",n),t.resolveWith({},[n])}):t.resolveWith({},[n]),t.promise()},getFieldList:function(t){var i=jQuery.Deferred();return void 0!==o.FieldLoadQueue[t]?o.FieldLoadQueue[t]:(o.FieldLoadQueue[t]=i,void 0!==o.FieldCache[t]?i.resolve(o.FieldCache[t]):a.post("index.php",{module:r,mode:"GetFieldList",action:"RedooUtils",module_name:t},"json").then(function(e){o.FieldCache[t]=e,i.resolve(e.fields)}),i.promise())},filterFieldListByFieldtype:function(e,n){var o={};return jQuery.each(e,function(e,t){var i=[];jQuery.each(t,function(e,t){t.type==n&&i.push(t)}),0<i.length&&(o[e]=i)}),o},fillFieldSelect:function(n,o,e,t){void 0===t&&(t=""),void 0===e&&(e=moduleName),"string"==typeof o&&(o=[o]),B.getFieldList(e,t).then(function(e){""!=t&&(e=B.filterFieldListByFieldtype(e,t));var i="";jQuery.each(e,function(e,t){i+='<optgroup label="'+e+'">',jQuery.each(t,function(e,t){i+='<option value="'+t.name+'" '+(-1!=jQuery.inArray(t.name,o)?'selected="selected"':"")+">"+t.label+"</option>"}),i+="</optgroup>",jQuery("#"+n).html(i),jQuery("#"+n).hasClass("select2")&&jQuery("#"+n).select2("val",o),jQuery("#"+n).trigger("FieldsLoaded")})})},_getDefaultParentEle:function(){return"div#page"},getMainModule:function(e){return B.isVT7()?B._getMainModuleVT7(e):B._getMainModuleVT6(e)},_getMainModuleVT6:function(e){void 0===e&&(e=B._getDefaultParentEle());var t=B.getViewMode(e);if("detailview"==t||"summaryview"==t)return l("#module",e).val();if("editview"==t||"quickcreate"==t)return l('[name="module"]',e).val();if("listview"==t)return l("#module",e).val();if("relatedview"==t){if(0<l('[name="relatedModuleName"]',e).length)return l('[name="relatedModuleName"]',e).val();if(0<l("#module",e).length)return l("#module",e).val()}var i=B.getQueryParams("module");return!1!==i?i:""},_getMainModuleVT7:function(e){void 0===e&&(e=B._getDefaultParentEle());var t=B.getViewMode(e);if(void 0!==l(e).data("forcerecordmodule"))return l(e).data("forcerecordmodule");if("#overlayPageContent.in"!=e&&0<l("#overlayPageContent.in").length)return B._getMainModuleVT7("#overlayPageContent.in");if("undefined"!=typeof _META&&("detailview"==t||"summaryview"==t||"commentview"==t||"historyview"==t||"editview"==t||"listview"==t)&&0==l(e).hasClass("modal"))return _META.module;if("detailview"==t||"summaryview"==t)return l("#module",e).val();if("editview"==t||"quickcreate"==t)return 0<l("#module",e).length?l("#module",e).val():l('[name="module"]',e).val();if("listview"==t)return l("#module",e).val();if("relatedview"==t){if(0<l('[name="relatedModuleName"]',e).length)return l('[name="relatedModuleName"]',e).val();if(0<l("#module",e).length)return l("#module",e).val()}var i=B.getQueryParams("module");return!1!==i?i:""},getMainRecordId:function(){var e="div#page";void 0===e&&(e=B._getDefaultParentEle());B.getViewMode(e);return l("#recordId",e).val()},getRecordIds:function(e){void 0===e&&(e=B._getDefaultParentEle());var i=[],t=B.getViewMode(e);return"detailview"==t||"summaryview"==t?i.push(l("#recordId",e).val()):"quickcreate"==t||("editview"==t?i.push(l('[name="record"]').val()):"listview"==t?l(".listViewEntries").each(function(e,t){i.push(l(t).data("id"))}):"relatedview"==t&&l(".listViewEntries").each(function(e,t){i.push(l(t).data("id"))})),i},onQuickCreate:function(i){jQuery('.quickCreateModule, .addButton[data-url*="QuickCreate"]').on("click",function e(){if(0==jQuery(".quickCreateContent",".modelContainer").length)window.setTimeout(e,200);else{var t=jQuery(".modelContainer");console.log("onQuickCreate Done"),i(t.find('input[name="module"]').val(),t)}})},getViewMode:function(e){return B.isVT7()?B._getViewModeVT7(e):B._getViewModeVT6(e)},_getViewModeVT6:function(e){void 0===e&&(e=B._getDefaultParentEle());var t=l("#view",e);return o.viewMode=!1,0<t.length&&"List"==t[0].value&&(o.viewMode="listview"),0<l(".detailview-table",e).length?o.viewMode="detailview":0<l(".summaryView",e).length?o.viewMode="summaryview":0<l(".recordEditView",e).length&&(0==l(".quickCreateContent",e).length?o.viewMode="editview":o.viewMode="quickcreate"),0<l(".relatedContents",e).length&&(o.viewMode="relatedview",0<l("td[data-field-type]",e).length?o.popUp=!1:o.popUp=!0),!1===o.viewMode&&0<l("#view",e).length&&"Detail"==l("#view",e).val()&&(o.viewMode="detailview"),o.viewMode},_getViewModeVT7:function(e){return void 0===e&&(e=B._getDefaultParentEle()),o.viewMode=!1,0<l(".detailview-table",e).length?o.viewMode="detailview":0<l(".summaryView",e).length?o.viewMode="summaryview":0<l(".recordEditView",e).length?0==l(".quickCreateContent",e).length?o.viewMode="editview":o.viewMode="quickcreate":0<l(".commentsRelatedContainer",e).length?o.viewMode="commentview":0<l(".HistoryContainer",e).length?o.viewMode="historyview":0<jQuery(".relatedContainer",e).find(".relatedModuleName").length?o.viewMode="relatedview":0<jQuery(".listViewContentHeader",e).length&&"undefined"!=typeof _META&&"List"==_META.view&&(o.viewMode="listview"),!1===o.viewMode&&0<l("#view",e).length&&"Detail"==l("#view",e).val()&&(o.viewMode="detailview"),o.viewMode},getContentMaxHeight:function(){if(0!=B.isVT7())return jQuery("#page").height();switch(B.getCurrentLayout()){case"begbie":return jQuery(".mainContainer").height();default:return jQuery("#leftPanel").height()-50}},getContentMaxWidth:function(){if(0==B.isVT7())return jQuery("#rightPanel").width()},hideModalBox:function(e){!0===B.isVT7()?app.helper.hideModal():app.hideModalWindow()},showModalBox:function(e,t){var i=jQuery.Deferred();!1===B.isVT7()?app.showModalWindow(e,function(e){i.resolveWith(window,e)}):(void 0===t&&(t={close:function(){}}),void 0===t.close&&(t.close=function(){}),d.set("__onModalClose",t.close),0<jQuery(".myModal .modal-dialog").length&&0<jQuery(".modal.in").length?(jQuery(".myModal .modal-dialog").replaceWith(e),i.resolveWith(window,jQuery(".modal.myModal")[0])):app.helper.showModal(e,{cb:function(e){i.resolveWith(window,e)}}).off("hidden.bs.modal").on("hidden.bs.modal",function(){t.close()}));return i.promise()},showContentOverlay:function(e,t){if(B.isVT7())return app.helper.loadPageContentOverlay(e,t);0==l("#overlayPageContent").length&&l("body").append("<div id='overlayPageContent' style=\"margin:0;\" class='fade modal content-area overlayPageContent overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>\n        <div class=\"data\">\n        </div>\n        <div class=\"modal-dialog\">\n        </div>\n    </div>");var i=new jQuery.Deferred;t=jQuery.extend({backdrop:!0,show:!0,keyboard:!1},t);var n=l("#overlayPageContent");n.addClass("full-width");var o=!1;return n.hasClass("in")&&(o=!0),n.one("shown.bs.modal",function(){i.resolve(l("#overlayPageContent"))}),n.one("hidden.bs.modal",function(){n.find(".data").html("")}),n.find(".data").html(e),n.modal(t),o&&i.resolve(jQuery("#overlayPageContent")),i.promise()},hideContentOverlay:function(){if(!B.isVT7()){var e=new jQuery.Deferred,t=l("#overlayPageContent");return t.one("hidden.bs.modal",function(){t.find(".data").html(""),e.resolve()}),l("#overlayPageContent").modal("hide"),e.promise()}app.helper.hidePageContentOverlay()},setFieldValue:function(e,t,i){void 0!==i&&null!=i||(i=B._getDefaultParentEle());var n=B.getFieldElement(e,i,!0);switch(n.prop("tagName")){case"INPUT":switch(n.attr("type")){case"text":n.hasClass("dateField")?B.isVT7()?n.datepicker("update",t):""!==t?n.val(t).DatePickerSetDate(t,!0):n.val(t).DatePickerClear():n.val(t);break;case"hidden":if(n.hasClass("sourceField")){var o=Vtiger_Edit_Js.getInstance(),r=n.closest("td");""!=t.id?o.setReferenceFieldValue(r,{id:t.id,name:t.label}):l(".clearReferenceSelection",r).trigger("click")}}break;case"SELECT":n.val(t),!1===B.isVT7()?n.hasClass("chzn-select")&&n.trigger("liszt:updated"):n.hasClass("select2")&&n.trigger("change.select2")}},layoutDependValue:function(e,t){var i=B.getCurrentLayout();return void 0!==e[i]?e[i]:t},getFieldElement:function(e,t,i){if(void 0!==t&&null!=t||(t=B._getDefaultParentEle()),void 0===i&&(i=!1),"object"==typeof e)return e;var n=!1;if("detailview"==B.getViewMode(t))0<l("#"+B.getMainModule(t)+"_detailView_fieldValue_"+e,t).length||0<l("#Events_detailView_fieldValue_"+e,t).length?(n=l("#"+B.getMainModule(t)+"_detailView_fieldValue_"+e),"Calendar"==B.getMainModule(t)&&0==n.length&&(n=l("#Events_detailView_fieldValue_"+e,t))):0<l("#_detailView_fieldValue_"+e,t).length&&(n=l("#_detailView_fieldValue_"+e,t));else if("summaryview"==B.getViewMode(t)){var o;if(0==(o=B.isVT7()?jQuery('[data-name="'+e+'"]',this.parentEle):jQuery('[name="'+e+'"]',this.parentEle)).length)return!1;n=l(o[0]).closest(B.layoutDependValue({vlayout:"td",v7:".row",begbie:"div.mycdivfield"},"td"))}else if("editview"==B.getViewMode(t)||"quickcreate"==B.getViewMode(t)){var r=l('[name="'+e+'"]',t);if(0==r.length)return!1;if(1==i)return r;n=l(r[0]).closest(B.layoutDependValue({vlayout:".fieldValue",v7:".fieldValue",begbie:"div.mycdivfield"},".fieldValue"))}else if("listview"==B.getViewMode(t)){if(!1===B.listViewFields&&(B.listViewFields=B.getListFields(t)),null===B.currentLVRow)return!1;if(void 0===B.listViewFields[e])return!1;n=0<=B.listViewFields[e]?l(l("td.listViewEntryValue",B.currentLVRow)[B.listViewFields[e]]):l(l("td.listViewEntryValue",B.currentLVRow)[-1*Number(B.listViewFields[e]+100)])}else"relatedview"==B.getViewMode(t)&&(!1===B.listViewFields&&(B.listViewFields=B.getListFields(t)),n=0<l("td[data-field-type]",B.currentLVRow).length?l(l("td[data-field-type]",B.currentLVRow)[B.listViewFields[e]]):l(l("td.listViewEntryValue",B.currentLVRow)[B.listViewFields[e]]));return n},refreshContent:function(e,t,i,n){void 0===i&&(n=!1),void 0===i&&(i={}),void 0===t&&(t=!1),i.module=r,i.view=e,!0===t&&(i.parent="Settings");var o=jQuery.Deferred();return B.isVT7()?a.request(i).then(function(e){var t;0<jQuery(".settingsPageDiv").length?(jQuery(".settingsPageDiv").html(e),t=jQuery(".settingsPageDiv")):(jQuery(".ContentReplacement").html(e),t=jQuery(".ContentReplacement")),!0===n&&jQuery(".select2",t).select2(),o.resolve()}):a.request(i).then(function(e){jQuery(jQuery(".contentsDiv")[0]).html(e),!0===n&&jQuery(jQuery(".contentsDiv")[0]).find(".select2").select2(),o.resolve()}),o.promise()},getListFields:function(e){var t;t=B.isVT7()?jQuery(".listview-table .listViewContentHeaderValues",e):jQuery(".listViewEntriesTable .listViewHeaderValues",e);var i={};for(var n in t)if(t.hasOwnProperty(n)&&jQuery.isNumeric(n)){var o=t[n];null==jQuery(o).data("columnname")?i[jQuery(o).data("fieldname")]=n:i[jQuery(o).data("columnname")]=n}return i},loadStyles:function(e,t){"string"==typeof e&&(e=[e]);var i=jQuery.Deferred();return void 0===t&&(t=!1),l.when.apply(l,l.map(e,function(e){return t&&(e+="?_ts="+(new Date).getTime()),l.get(e,function(){l("<link>",{rel:"stylesheet",type:"text/css",href:e}).appendTo("head")})})).then(function(){i.resolve()}),i.promise()},loadScript:function(e,t){var i=jQuery.Deferred();return void 0===d.loadedScript&&(d.loadedScript={}),void 0!==d.loadedScript[e]?(i.resolve(),i):(t=jQuery.extend(t||{},{dataType:"script",cache:!0,url:e}),jQuery.ajax(t))}},o={FieldCache:{},FieldLoadQueue:{},viewMode:!1,popUp:!1};function s(e,t,i,n,o){this._listener=t,this._isOnce=i,this.context=n,this._signal=e,this._priority=o||0}function u(e,t){if("function"!=typeof e)throw Error("listener is a required param of {fn}() and should be a Function.".replace("{fn}",t))}function t(){this._bindings=[],this._prevParams=null;var e=this;this.dispatch=function(){t.prototype.dispatch.apply(e,arguments)}}"undefined"!=typeof console&&void 0!==console.log&&console.log("Initialize FlexUtils "+r+" V2.3.1"),void 0===window.FlexStore&&(window.FlexStore={}),void 0===window.RedooStore&&(window.RedooStore={}),window.RedooStore[r]=window.FlexStore[r]={Ajax:a,Utils:B,Cache:d,Translate:n},void 0===window.FlexAjax&&(window.FlexAjax=function(e){return void 0!==window.FlexStore[e]?window.FlexStore[e].Ajax:void 0!==window.RedooStore[e]?window.RedooStore[e].Ajax:void console.error("FlexAjax "+e+" Scope not found")}),void 0===window.RedooAjax&&(window.RedooAjax=window.FlexAjax),void 0===window.FlexUtils&&(window.FlexUtils=function(e){return void 0!==window.FlexStore[e]?window.FlexStore[e].Utils:void 0!==window.RedooStore[e]?window.RedooStore[e].Utils:void console.error("FlexUtils "+e+" Scope not found")}),void 0===window.RedooUtils&&(window.RedooUtils=window.FlexUtils),void 0===window.FlexCache&&(window.FlexCache=function(e){return void 0!==window.RedooStore[e]?window.RedooStore[e].Cache:void 0!==window.FlexStore[e]?window.FlexStore[e].Cache:void console.error("FlexCache "+e+" Scope not found")}),void 0===window.RedooCache&&(window.RedooCache=window.FlexCache),void 0===window.FlexTranslate&&(window.FlexTranslate=function(e){if(void 0!==window.FlexStore[e])return window.FlexStore[e].Translate;console.error("FlexTranslate "+e+" Scope not found")}),void 0===window.FlexEvents&&(window.FlexEvents=l({})),t.prototype={VERSION:"1.0.0",memorize:!(s.prototype={active:!0,params:null,execute:function(e){var t;return this.active&&this._listener&&(e=this.params?this.params.concat(e):e,t=this._listener.apply(this.context,e),this._isOnce&&this.detach()),t},detach:function(){return this.isBound()?this._signal.remove(this._listener,this.context):null},isBound:function(){return!!this._signal&&!!this._listener},isOnce:function(){return this._isOnce},getListener:function(){return this._listener},getSignal:function(){return this._signal},_destroy:function(){delete this._signal,delete this._listener,delete this.context},toString:function(){return"[SignalBinding isOnce:"+this._isOnce+", isBound:"+this.isBound()+", active:"+this.active+"]"}}),_shouldPropagate:!0,active:!0,_registerListener:function(e,t,i,n){var o=this._indexOfListener(e,i);if(-1!==o){if((e=this._bindings[o]).isOnce()!==t)throw Error("You cannot add"+(t?"":"Once")+"() then add"+(t?"Once":"")+"() the same listener without removing the relationship first.")}else e=new s(this,e,t,i,n),this._addBinding(e);return this.memorize&&this._prevParams&&e.execute(this._prevParams),e},_addBinding:function(e){for(var t=this._bindings.length;--t,this._bindings[t]&&e._priority<=this._bindings[t]._priority;);this._bindings.splice(t+1,0,e)},_indexOfListener:function(e,t){for(var i,n=this._bindings.length;n--;)if((i=this._bindings[n])._listener===e&&i.context===t)return n;return-1},has:function(e,t){return-1!==this._indexOfListener(e,t)},add:function(e,t,i){return u(e,"add"),this._registerListener(e,!1,t,i)},addOnce:function(e,t,i){return u(e,"addOnce"),this._registerListener(e,!0,t,i)},remove:function(e,t){u(e,"remove");var i=this._indexOfListener(e,t);return-1!==i&&(this._bindings[i]._destroy(),this._bindings.splice(i,1)),e},removeAll:function(){for(var e=this._bindings.length;e--;)this._bindings[e]._destroy();this._bindings.length=0},getNumListeners:function(){return this._bindings.length},halt:function(){this._shouldPropagate=!1},dispatch:function(e){if(this.active){var t,i=Array.prototype.slice.call(arguments),n=this._bindings.length;if(this.memorize&&(this._prevParams=i),n)for(t=this._bindings.slice(),this._shouldPropagate=!0;t[--n]&&this._shouldPropagate&&!1!==t[n].execute(i););}},forget:function(){this._prevParams=null},dispose:function(){this.removeAll(),delete this._bindings,delete this._prevParams},toString:function(){return"[Signal active:"+this.active+" numListeners:"+this.getNumListeners()+"]"}};var e=t;e.Signal=t,B.Signal=e.Signal,function(){function e(Q){function i(e,o){var t,i,n,r,a,l,d,s,u,c=e==window,f=o&&void 0!==o.message?o.message:void 0;if(!(o=Q.extend({},B.blockUI.defaults,o||{})).ignoreIfBlocked||!Q(e).data("blockUI.isBlocked")){if(o.overlayCSS=Q.extend({},B.blockUI.defaults.overlayCSS,o.overlayCSS||{}),t=Q.extend({},B.blockUI.defaults.css,o.css||{}),o.onOverlayClick&&(o.overlayCSS.cursor="pointer"),i=Q.extend({},B.blockUI.defaults.themedCSS,o.themedCSS||{}),f=void 0===f?o.message:f,c&&A&&U(window,{fadeOut:0}),f&&"string"!=typeof f&&(f.parentNode||f.jquery)){var h=f.jquery?f[0]:f,v={};Q(e).data("blockUI.history",v),v.el=h,v.parent=h.parentNode,v.display=h.style.display,v.position=h.style.position,v.parent&&v.parent.removeChild(h)}Q(e).data("blockUI.onUnblock",o.onUnblock);var g,p,w,y,m=o.baseZ;g=O||o.forceIframe?Q('<iframe class="blockUI" style="z-index:'+m+++';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+o.iframeSrc+'"></iframe>'):Q('<div class="blockUI" style="display:none"></div>'),p=o.theme?Q('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:'+m+++';display:none"></div>'):Q('<div class="blockUI blockOverlay" style="z-index:'+m+++';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>'),o.theme&&c?(y='<div class="blockUI '+o.blockMsgClass+' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:'+(m+10)+';display:none;position:fixed">',o.title&&(y+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(o.title||"&nbsp;")+"</div>"),y+='<div class="ui-widget-content ui-dialog-content"></div>',y+="</div>"):o.theme?(y='<div class="blockUI '+o.blockMsgClass+' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:'+(m+10)+';display:none;position:absolute">',o.title&&(y+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(o.title||"&nbsp;")+"</div>"),y+='<div class="ui-widget-content ui-dialog-content"></div>',y+="</div>"):y=c?'<div class="blockUI '+o.blockMsgClass+' blockPage" style="z-index:'+(m+10)+';display:none;position:fixed"></div>':'<div class="blockUI '+o.blockMsgClass+' blockElement" style="z-index:'+(m+10)+';display:none;position:absolute"></div>',w=Q(y),f&&(o.theme?(w.css(i),w.addClass("ui-widget-content")):w.css(t)),o.theme||p.css(o.overlayCSS),p.css("position",c?"fixed":"absolute"),(O||o.forceIframe)&&g.css("opacity",0);var b=[g,p,w],_=Q(c?"body":e);Q.each(b,function(){this.appendTo(_)}),o.theme&&o.draggable&&Q.fn.draggable&&w.draggable({handle:".ui-dialog-titlebar",cancel:"li"});var k=P&&(!Q.support.boxModel||0<Q("object,embed",c?null:e).length);if(D||k){if(c&&o.allowBodyStretch&&Q.support.boxModel&&Q("html,body").css("height","100%"),(D||!Q.support.boxModel)&&!c)var C=R(e,"borderTopWidth"),x=R(e,"borderLeftWidth"),V=C?"(0 - "+C+")":0,M=x?"(0 - "+x+")":0;Q.each(b,function(e,t){var i=t[0].style;if(i.position="absolute",e<2)c?i.setExpression("height","Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:"+o.quirksmodeOffsetHack+') + "px"'):i.setExpression("height",'this.parentNode.offsetHeight + "px"'),c?i.setExpression("width",'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"'):i.setExpression("width",'this.parentNode.offsetWidth + "px"'),M&&i.setExpression("left",M),V&&i.setExpression("top",V);else if(o.centerY)c&&i.setExpression("top",'(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"'),i.marginTop=0;else if(!o.centerY&&c){var n="((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "+(o.css&&o.css.top?parseInt(o.css.top,10):0)+') + "px"';i.setExpression("top",n)}})}if(f&&(o.theme?w.find(".ui-widget-content").append(f):w.append(f),(f.jquery||f.nodeType)&&Q(f).show()),(O||o.forceIframe)&&o.showOverlay&&g.show(),o.fadeIn){var S=o.onBlock?o.onBlock:L,j=o.showOverlay&&!f?S:L,F=f?S:L;o.showOverlay&&p._fadeIn(o.fadeIn,j),f&&w._fadeIn(o.fadeIn,F)}else o.showOverlay&&p.show(),f&&w.show(),o.onBlock&&o.onBlock.bind(w)();if(E(1,e,o),c?(A=w[0],N=Q(o.focusableElements,A),o.focusInput&&setTimeout(T,20)):(n=w[0],r=o.centerX,a=o.centerY,l=n.parentNode,d=n.style,s=(l.offsetWidth-n.offsetWidth)/2-R(l,"borderLeftWidth"),u=(l.offsetHeight-n.offsetHeight)/2-R(l,"borderTopWidth"),r&&(d.left=0<s?s+"px":"0"),a&&(d.top=0<u?u+"px":"0")),o.timeout){var I=setTimeout(function(){c?Q.unblockUI(o):Q(e).unblock(o)},o.timeout);Q(e).data("blockUI.timeout",I)}}}function U(e,t){var i,n,o=e==window,r=Q(e),a=r.data("blockUI.history"),l=r.data("blockUI.timeout");l&&(clearTimeout(l),r.removeData("blockUI.timeout")),t=Q.extend({},B.blockUI.defaults,t||{}),E(0,e,t),null===t.onUnblock&&(t.onUnblock=r.data("blockUI.onUnblock"),r.removeData("blockUI.onUnblock")),n=o?Q("body").children().filter(".blockUI").add("body > .blockUI"):r.find(">.blockUI"),t.cursorReset&&(1<n.length&&(n[1].style.cursor=t.cursorReset),2<n.length&&(n[2].style.cursor=t.cursorReset)),o&&(A=N=null),t.fadeOut?(i=n.length,n.stop().fadeOut(t.fadeOut,function(){0==--i&&d(n,a,t,e)})):d(n,a,t,e)}function d(e,t,i,n){var o=Q(n);if(!o.data("blockUI.isBlocked")){e.each(function(){this.parentNode&&this.parentNode.removeChild(this)}),t&&t.el&&(t.el.style.display=t.display,t.el.style.position=t.position,t.el.style.cursor="default",t.parent&&t.parent.appendChild(t.el),o.removeData("blockUI.history")),o.data("blockUI.static")&&o.css("position","static"),"function"==typeof i.onUnblock&&i.onUnblock(n,i);var r=Q(document.body),a=r.width(),l=r[0].style.width;r.width(a-1).width(a),r[0].style.width=l}}function E(e,t,i){var n=t==window,o=Q(t);if((e||(!n||A)&&(n||o.data("blockUI.isBlocked")))&&(o.data("blockUI.isBlocked",e),n&&i.bindEvents&&(!e||i.showOverlay))){var r="mousedown mouseup keydown keypress keyup touchstart touchend touchmove";e?Q(document).bind(r,i,a):Q(document).unbind(r,a)}}function a(e){if("keydown"===e.type&&e.keyCode&&9==e.keyCode&&A&&e.data.constrainTabKey){var t=N,i=!e.shiftKey&&e.target===t[t.length-1],n=e.shiftKey&&e.target===t[0];if(i||n)return setTimeout(function(){T(n)},10),!1}var o=e.data,r=Q(e.target);return r.hasClass("blockOverlay")&&o.onOverlayClick&&o.onOverlayClick(e),0<r.parents("div."+o.blockMsgClass).length||0===r.parents().children().filter("div.blockUI").length}function T(e){if(N){var t=N[!0===e?N.length-1:0];t&&t.focus()}}function R(e,t){return parseInt(Q.css(e,t),10)||0}Q.fn._fadeIn=Q.fn.fadeIn;var L=Q.noop||function(){},O=/MSIE/.test(navigator.userAgent),D=/MSIE 6.0/.test(navigator.userAgent)&&!/MSIE 8.0/.test(navigator.userAgent);document.documentMode;var P=Q.isFunction(document.createElement("div").style.setExpression);B.blockUI=function(e){i(window,e)},B.unblockUI=function(e){U(window,e)},Q.growlUI=function(e,t,i,n){var o=Q('<div class="growlUI"></div>');e&&o.append("<h1>"+e+"</h1>"),t&&o.append("<h2>"+t+"</h2>"),void 0===i&&(i=3e3);var r=function(e){e=e||{},Q.blockUI({message:o,fadeIn:void 0!==e.fadeIn?e.fadeIn:700,fadeOut:void 0!==e.fadeOut?e.fadeOut:1e3,timeout:void 0!==e.timeout?e.timeout:i,centerY:!1,showOverlay:!1,onUnblock:n,css:B.blockUI.defaults.growlCSS})};r(),o.css("opacity"),o.mouseover(function(){r({fadeIn:0,timeout:3e4});var e=Q(".blockMsg");e.stop(),e.fadeTo(300,1)}).mouseout(function(){Q(".blockMsg").fadeOut(1e3)})},Q.fn.block=function(e){if(this[0]===window)return Q.blockUI(e),this;var t=Q.extend({},B.blockUI.defaults,e||{});return this.each(function(){var e=Q(this);t.ignoreIfBlocked&&e.data("blockUI.isBlocked")||e.unblock({fadeOut:0})}),this.each(function(){"static"==Q.css(this,"position")&&(this.style.position="relative",Q(this).data("blockUI.static",!0)),this.style.zoom=1,i(this,e)})},Q.fn.unblock=function(e){return this[0]===window?(Q.unblockUI(e),this):this.each(function(){U(this,e)})},B.blockUI.version=2.7,B.blockUI.defaults={message:"<h1>Please wait...</h1>",title:null,draggable:!0,theme:!1,css:{padding:0,margin:0,width:"30%",top:"40%",left:"35%",textAlign:"center",color:"#000",border:"3px solid #aaa",backgroundColor:"#fff",cursor:"wait"},themedCSS:{width:"30%",top:"40%",left:"35%"},overlayCSS:{backgroundColor:"#000",opacity:.6,cursor:"wait"},cursorReset:"default",growlCSS:{width:"350px",top:"10px",left:"",right:"10px",border:"none",padding:"5px",opacity:.6,cursor:"default",color:"#fff",backgroundColor:"#000","-webkit-border-radius":"10px","-moz-border-radius":"10px","border-radius":"10px"},iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank",forceIframe:!1,baseZ:2e3,centerX:!0,centerY:!0,allowBodyStretch:!0,bindEvents:!0,constrainTabKey:!0,fadeIn:200,fadeOut:400,timeout:0,showOverlay:!0,focusInput:!0,focusableElements:":input:enabled:visible",onBlock:null,onUnblock:null,onOverlayClick:null,quirksmodeOffsetHack:4,blockMsgClass:"blockMsg",ignoreIfBlocked:!1};var A=null,N=[]}"function"==typeof define&&define.amd&&define.amd.jQuery?define(["jquery"],e):e(jQuery)}()}(jQuery);;jQuery(function() {
    var workflowFrontendActions = new Workflow();
    workflowFrontendActions.checkFrontendActions('init');

    var listenCommentWidget = false;

    if(typeof Vtiger_Detail_Js != 'undefined' && jQuery('#recordId').length > 0) {
        var thisInstance = Vtiger_Detail_Js.getInstance();
        var detailContentsHolder = thisInstance.getContentHolder();

        RedooUtils('Workflow2').onFieldChange('div#page').add(function() {
            workflowFrontendActions.checkFrontendActions('edit');
        });
/*
        detailContentsHolder.on(thisInstance.fieldUpdatedEvent, function(e, params){
            var fieldName = jQuery(e.target).attr("name");
            workflowFrontendActions.checkFrontendActions('edit');
        });
*/
        detailContentsHolder.on(thisInstance.widgetPostLoad, function(e, p) {
            if(listenCommentWidget == false) return;

            if(jQuery('.commentContainer', e.target).length > 0) {
                listenCommentWidget = false;
                workflowFrontendActions.checkFrontendActions('edit');
            }
        });

        detailContentsHolder.on('click','.detailViewSaveComment', function(e){
            listenCommentWidget = true;
        });
    }
    /*
    // TODO: UPDATE for VT7
    if(typeof Vtiger_Header_Js != 'undefined') {
        var thisInstance = Vtiger_Header_Js.getInstance();
        thisInstance.registerQuickCreateCallBack(function(e, b, c) {

            var workflowFrontendActions = new Workflow();
            workflowFrontendActions.checkFrontendActions('both', e.data.result['_recordId']);
        });
    }
    */


    jQuery(window).on('workflow.detail.sidebar.ready', function() {
        jQuery('#WorkflowDesignerErrorLoaded').hide();
        var workflow = new Workflow();
        workflowObj = workflow;

    }).on('workflow.list.sidebar.ready', function() {
        jQuery('#WorkflowDesignerErrorLoaded').hide();
        var workflow = new Workflow();
        workflowObj = workflow;

    });
    /* jQuery(window).on('workflow.list.sidebar.ready', function() {
     console.log('List-Sidebar ready');
     }); */

    if(typeof Vtiger_Edit_Js != 'undefined') {
        jQuery('body').on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, b, c, d) {
            var workflowObj = new Workflow();
            workflowObj.setBackgroundMode(true);

            workflowObj.addExtraEnvironment('source_module', jQuery('[name="module"]').val())
            workflowObj.addExtraEnvironment('source_record', jQuery('[name="record"]').val());

            workflowObj.execute('WF_REFERENCE', b.record, function() {
                workflowObj.checkFrontendActions('edit');
            });
        });
    }

    var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

//    if (viewMode == 'detailview' || viewMode == 'summaryview') {
        Workflow2Frontend.TopbuttonHandler(jQuery('div#page'));
//    }

    if (jQuery('#module', jQuery('div#page')).length > 0) {
        if (jQuery('#module', jQuery('div#page')).val() == 'Campaigns' && typeof Campaigns_Detail_Js != 'undefined') {

            // Enable ViewCheck
            if (
                typeof WFDFrontendConfig !== 'undefined' &&
                typeof WFDFrontendConfig['relatedbtn'] !== 'undefined'
            ) {
                var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                if (viewMode == 'relatedview') {
                    Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                }

                jQuery(document).on('postajaxready', function (e) {
                    var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                    if (viewMode == 'relatedview') {
                        Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                    }
                });

                if (jQuery('#module', jQuery('div#page')).length > 0) {
                    if (jQuery('#module', jQuery('div#page')).val() == 'Campaigns' && typeof Campaigns_Detail_Js != 'undefined') {
                        thisInstance = Campaigns_Detail_Js.getInstance();
                        var detailContentsHolder = thisInstance.getContentHolder();
                        var detailContainer = detailContentsHolder.closest('div.detailViewInfo');
                        jQuery('.related', detailContainer).on('click', 'li', function (e, urlAttributes) {
                            window.setTimeout(function () {
                                var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                                if (viewMode == 'relatedview') {
                                    Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                                }
                            }, 1000);
                        });
                    }
                }
            }
        }
    }

    RedooUtils('Workflow2').onRelatedListChange().add(function() {
        var objWorkflow = new Workflow();
        objWorkflow.showInlineButtons();
    });

    jQuery('.quickCreateModule, .addButton[data-url*="QuickCreate"]').on('click', function __check() {
        if(jQuery('.quickCreateContent','.modelContainer').length == 0) {
            window.setTimeout(__check, 200);
        } else  {
            jQuery('#globalmodal form[name="QuickCreate"] .btn[type="submit"]').on('click', function() {
                WorkflowExecution
            });
        }
    });

});;jQuery('#WorkflowDesignerErrorLoaded').hide();
var WFUserIsAdmin;
window.Workflow = function () {
    this.crmid = 0;
    this._allowParallel = 0;
    this._workflowid = null;
    this._workflowTrigger = null;

    this._currentExecId = null;

    this.ExecutionCallback = null;

    this._requestValues = {};
    this._requestValuesKey = null;
    this._backgroundMode = false;
    this._extraEnvironment = {};
    this._ListViewMode = false;

    /**
     *
     * @param workflow WorkflowID or Trigger
     * @param crmid Record to use
     */
    this.execute = function(workflow, crmid, callback, ignoreViewMode) {
        if(typeof ignoreViewMode === 'undefined') ignoreViewMode = false;

        this.crmid = crmid;

        if(FlexUtils('Workflow2').getViewMode() == 'listview' && crmid == 0 && ignoreViewMode === false) {
            runListViewWorkflow(workflow);
        } else {
            if (jQuery.isNumeric(workflow)) {
                this._executeById(workflow, callback);
            } else {
                this._executeByTrigger(workflow, callback);
            }
        }
    };

    this.setListView = function(value) {
        this._ListViewMode = (value == true);
    };

    this.checkFrontendActions = function(step, crmid) {
        WorkflowRecordMessages = [];
        if(typeof crmid == 'undefined') {
            var crmid = 0;

            var recordId;
            if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'detailview' || Workflow2Frontend.getViewMode(jQuery('div#page')) == 'summaryview') {
                recordId = $('#recordId', jQuery('div#page')).val();
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'quickcreate') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'editview') {
                recordId = jQuery('[name="record"]').val();
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'listview') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'relatedview') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'composemail') {
                var ids = jQuery('[name="selected_ids"]').val();
                recordId = jQuery.parseJSON(ids)[0];
            } else {
                recordId = 0;
            }
        } else {
            recordId = Number(crmid);
        }

        if(recordId == 0 || typeof recordId == 'undefined') {
            if( $('#recordId').length > 0) {
                var recordId =  $('#recordId').val();
            }
        }

        /*if(typeof recordId == "undefined" || recordId == 0) {
            return;
        }*/

        if(typeof _META != 'undefined') {
            var moduleName = _META.module;
        } else {
            var moduleName = RedooUtils('Workflow2').getMainModule('div#page');
        }

        RedooAjax('Workflow2').postAction('CheckFrontendActions', {'crmid':recordId, 'step':step, src_module: moduleName}, 'json').then(jQuery.proxy(function(response) {
            if(response.length == 0) return;

            if(typeof response.buttons != 'undefined' && response.buttons.length > 0) {
                this.generateInlineButtons(response.buttons);
            }

            if(typeof response.detailviewtop != 'undefined' && response.detailviewtop.length > 0) {
                this.generateDetailViewTopButtons(response.detailviewtop);
            }

            if(typeof response['btn-list'] == 'object') {
                this.generateBtnTrigger(response['btn-list']);
            }

            if(jQuery('.wfdGeneralButton').length == 0) {
                if(typeof _META != 'undefined') {
                    var moduleName = _META.module;
                } else {
                    var moduleName = RedooUtils('Workflow2').getMainModule(parentEle);
                }
                var recordId = jQuery('[name="record_id"]').val();

                if(response.show_general_button != false) {
                    if(jQuery('.detailview-header-block').length > 0) {
                        var TopButton = '<button class="btn btn-default wfdGeneralButton" data-view="detailview" data-module="' + moduleName + '" data-crmid="' + recordId + '" style="margin-right:5px;font-weight:bold;"><i class="fa fa-location-arrow"></i> ' + response.labels.start_process + '</button>';
                        jQuery('.detailViewButtoncontainer .btn-toolbar .btn-group:first-of-type').prepend('' + TopButton + '');
                    } else {
                        var TopButton = '<button class="btn btn-default wfdGeneralButton module-buttons" data-view="detailview" data-module="' + moduleName + '" data-crmid="' + recordId + '" style="font-weight:bold;"><i class="fa fa-location-arrow"></i> ' + response.labels.start_process + '</button>';
                        jQuery('#appnav .nav ').prepend('<li style="padding-right:20px;">' + TopButton + '</li>');
                    }

                    /*if (jQuery('.WFDetailViewGroupTop').length > 0) {
                        jQuery('.WFDetailViewGroupTop').prepend(TopButton);
                    } else {
                        jQuery('.detailViewButtoncontainer .btn-toolbar ').prepend('<div class="btn-group WFDetailViewGroupTop">' + TopButton + '</div>');
                    }*/

                    jQuery('.wfdGeneralButton').on('click', function (e) {
                        var module = jQuery(e.currentTarget).data('module');
                        var crmid = jQuery(e.currentTarget).data('crmid');
                        var view = jQuery(e.currentTarget).data('view');

                        Workflow2Frontend.showWorkflowPopup(module, crmid, view);
                    });
                }
            }


            WFUserIsAdmin = response.is_admin == true ? true : false;

            jQuery.each(response.actions, function(index, value) {

                switch(value.type) {
                    case 'redirect':
                        if(value.configuration.url == '_internal_reload') {
                            window.location.reload();
                            return false;
                        }
                        if(value.configuration.target == "same") {
                            window.location.href = value.configuration.url;
                            return false;
                        } else {
                            window.open(value.configuration.url);
                            return;
                        }

                        break;
                    case 'confirmation':
                        if(jQuery('.confirmation_container').length == 0) {
                            var html = '<div class="confirmation_container row block" style="background-color:#ffffff;padding-top:10px;margin-top:10px;"></div>';
                            jQuery('div.details').before(html);
                        }

                        var config = value.configuration;
                        var bgColor = config.backgroundcolor;
                        //if(bgColor == '') {
                            bgColor = '#ffffff';
                        //}
                        /*
                        if(config.border != '') {
                            var borderCSS = 'border:2px solid ' + config.border + ';border-top:0;';
                        } else {
                            var borderCSS = '';
                        }*/
                        var borderCSS = '';
                        var html = '<div class="row" style="line-height:24px;' + borderCSS +'background-color:' + bgColor + ';">';
                        html += '<div style="font-weight:bold;margin-bottom:10px;line-height:24px;" class="col-lg-6">' + config.infomessage + ' <div style="font-size:10px;color:#5e5e55;line-height:10px;">' + config.text_eingestellt +': ' + config.first_name + ' ' + config.last_name + ' / ' + config.timestamp +  '</div></div>';
                        html += '<div style="font-weight:bold;margin-bottom:10px;line-height:34px;" class="col-lg-6">';
                        if(config.buttons.btn_accept != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash1 + ' \', \'ok\');" class="btn btn-success decision decision_ok" style="margin-right:5px;min-width:100px;" href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=ok&h=' + config.hash1 + '">' + config.buttons.btn_accept + '</a>';
                        }
                        if(value.configuration.buttons.btn_rework != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash2 + ' \', \'rework\');" class="btn btn-warning decision decision_rework" style="margin-right:5px;min-width:100px;"  href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=rework&h=' + config.hash1 + '">' + config.buttons.btn_rework + '</a>';
                        }
                        if(value.configuration.buttons.btn_decline != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash3 + ' \', \'decline\');" class="btn btn-danger decision decision_decline" style="margin-right:5px;min-width:100px;"  href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=decline&h=' + config.hash1 + '">' + config.buttons.btn_decline + '</a>';
                        }


                        jQuery('.confirmation_container').append(html);
                        jQuery('.confirmation_container').slideDown();
                        break;
                    case 'requestValues':
                        continueWorkflow(value.configuration.execid, value.configuration.crmid, value.configuration.blockid);
                        return false;
                        break;
                    case 'message':
                        WorkflowRecordMessages.push(value.configuration);
                        break;
                }
            });

            this.parseMessages();
        }, this));
    };

    this.generateBtnTrigger = function(buttons) {
        jQuery('.wfdButtonHeaderbutton').remove();

        if(typeof buttons.headerbtn != 'undefined') {
            var html = '';
            jQuery.each(buttons.headerbtn, $.proxy(function(index, value) {
                var rand = Math.floor(Math.random() * 9999999) + 1000000;
                if(value.color != '') {
                    var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                } else {
                    var cssStyle = ''
                }

                html += '<li><button type="button" data-id="' + value.workflow_id + '" class="btn btn-default module-buttons wfdButtonHeaderbutton" style="' + cssStyle + '">' + value.label + '</button></li>';
            }, this));

            jQuery('#appnav .nav ').prepend('' + html + '');

            jQuery('.wfdButtonHeaderbutton').on('click', function(e) {
                var target = jQuery(e.currentTarget);
                var workflowObj = new Workflow();

                if(FlexUtils('Workflow2').getViewMode() == 'listview') {
                    workflowObj.execute(target.data('id'), 0);
                } else {
                    workflowObj.execute(target.data('id'), RedooUtils('Workflow2').getRecordIds()[0]);
                }
            });
        }
    };

    this.generateDetailViewTopButtons = function(buttons) {
        jQuery('.WFDetailViewGroupTop').remove();
        var html = '';
            jQuery.each(buttons, function(index, value) {
                var rand = Math.floor(Math.random() * 9999999) + 1000000;
                if(value.color != '') {
                    var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                } else {
                    var cssStyle = ''
                }

                html += '<button data-id="' + value.workflow_id + '" class="btn btn-default wfdButtonTopbutton" style="' + cssStyle + '">' + value.label + '</button>';
            });
            jQuery('.detailViewButtoncontainer .btn-toolbar ').prepend('<div class="btn-group WFDetailViewGroupTop">' + html + '</div>');

            jQuery('.wfdButtonTopbutton').on('click', function() {
                var workflow = new Workflow();
                workflow.execute(jQuery(this).data('id'), jQuery('#recordId').val());
            });
    };

    this.generateInlineButtons = function(buttons) {
        var final = {};

        jQuery.each(buttons, jQuery.proxy(function (index, button) {
            jQuery.each(button.config.field, jQuery.proxy(function (fieldIndex, fieldName) {
                if (typeof final[fieldName] == 'undefined') {
                    final[fieldName] = {
                        config: button.config,
                        buttons: []
                    };
                }

                final[fieldName]['buttons'].push(button);
            }, this));
            //
        }, this));

        RedooCache('Workflow2').set('currentInlineButtons', final);
        this.showInlineButtons();
    };

    this.showInlineButtons = function() {
        jQuery('.WFInlineButton').remove();
        jQuery('.WFDInlineDropdown').remove();

        jQuery.each(RedooCache('Workflow2').get('currentInlineButtons', []), jQuery.proxy(function(fieldName, fields) {
            var field = RedooUtils('Workflow2').getFieldElement(fieldName);

            if(field != false) {
                var dropdownHTML = '';
                var buttonHTML = '';

                    jQuery.each(fields['buttons'], jQuery.proxy(function (index, button) {
                        if(typeof button.config.dropdown == 'undefined' || button.config.dropdown == '0') {
                            // Buttons shouldn't arranged as DropDown

                            var existingButtons = jQuery('.WFInlineButton[data-wfid="' + button.workflow_id + '"][data-frontendid="' + button.frontend_id + '"][data-fieldname="' + fieldName + '"]');
                            if (existingButtons.length > 0) {
                                jQuery(existingButtons).show().removeClass('tmpbtn');
                            } else {
                                buttonHTML = '<button type="button" data-wfid="' + button.workflow_id + '" data-frontendid="' + button.frontend_id + '" data-fieldname="' + fieldName + '" class="WFInlineButton btn pull-right" style="height:20px;line-height:16px;font-size:10px; padding:1px 10px; background-color:' + button.color + ';color:' + button.textcolor + ';margin-left:5px;">' + button.label + '</button>';
                            }
                        } else {

                            // Buttons shouldn't arranged as DropDown
                            //jQuery.each(fields['buttons'], jQuery.proxy(function (index, button) {
                            dropdownHTML += '<li style=" background-color:' + button.color + ';color:' + button.textcolor + ';" class="dropdown-submenu"><a data-wfid="' + button.workflow_id + '" data-frontendid="' + button.frontend_id + '" data-fieldname="' + fieldName + '" href="#" style="color:' + button.textcolor + ';">' + button.label + '</a></li>';
                            //}, this));
                        }
                    }, this));

                jQuery('.WFDInlineDropdown', field).remove();

                if(RedooUtils('Workflow2').getViewMode() == 'detailview') {
                    if(dropdownHTML != '') {
                        var finalHTML = '<div class="btn-group pull-right WFDInlineDropdown" style="margin-left:5px;"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"  style="height:20px;color:#666666;border:1px solid #666666;line-height:16px;font-size:10px; padding:1px 5px;"><span class="caret"></span></a><ul class="dropdown-menu">' + dropdownHTML + '</ul></div>';
                        field.append(finalHTML);
                    }

                    if(buttonHTML != '') {
                        field.append(buttonHTML);
                    }
                } else if(RedooUtils('Workflow2').getViewMode() == 'summaryview') {
                    if(dropdownHTML != '') {
                        var finalHTML = '<div class="btn-group pull-right WFDInlineDropdown" style="margin-left:5px;"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"  style="font-size:10px; padding:1px 5px;"><span class="caret"></span></a><ul class="dropdown-menu">' + dropdownHTML + '</ul></div>';
                        field.append(finalHTML);
                    }
                    console.log(buttonHTML, field);
                    if(buttonHTML != '') {
                        field.append(buttonHTML);
                    }
                }

            }
        }, this));

        jQuery('.WFInlineButton.tmpbtn').hide();

        jQuery('.WFInlineButton, .WFDInlineDropdown li a').off('click').on('click', function(e) {
            e.stopPropagation();

            var wfId = jQuery(e.currentTarget).data('wfid');

            var workflow = new Workflow();
            workflow.execute(wfId, RedooUtils('Workflow2').getRecordIds()[0], function() {});
        });

        jQuery("div.WFDInlineDropdown").on('click', function(e) {
            e.stopPropagation();

            jQuery(".dropdown-toggle", e.currentTarget).dropdown('toggle');
        });
    };

    this.setBackgroundMode = function(value) {
        this._backgroundMode = value;
    };
    this.setRequestedData = function(values, relatedKey) {
        this._requestValues = values;
        this._requestValuesKey = relatedKey;
    };

    this.allowParallel = function(value) {
        this._allowParallel = value?1:0;
    };

    this.addExtraEnvironment = function(key, value) {
        this._extraEnvironment[key] = value;
    };

    this._executeByTrigger = function(triggerName, ExecutionCallback) {
        var Execution = new WorkflowExecution();
        Execution.init(this.crmid);
        Execution.setRequestedData(this._requestValues, this._requestValuesKey);

        if(this._allowParallel == 1) {
            Execution.allowParallel();
        }
        Execution.enableRedirection(ENABLEredirectionOrReloadAfterFinish);


        if(typeof ExecutionCallback != 'undefined') {
            this._workflowTrigger = triggerName;
        }

        if(typeof ExecutionCallback != 'undefined') {
            Execution.setCallback(ExecutionCallback);
        }

        jQuery.each(this._extraEnvironment, function(index, value) {
            Execution.addEnvironment(index, value);
        });

        Execution.setBackgroundMode(this._backgroundMode);
        Execution.setWorkflowByTrigger(triggerName);
        Execution.execute();
    };

    this._executeById = function(workflow_id, ExecutionCallback) {
        var Execution = new WorkflowExecution();
        Execution.init(this.crmid);
        Execution.setRequestedData(this._requestValues, this._requestValuesKey);

        if(this._allowParallel == 1) {
            Execution.allowParallel();
        }
        Execution.enableRedirection(ENABLEredirectionOrReloadAfterFinish);


        if(typeof ExecutionCallback != 'undefined') {
            this._workflowid = workflow_id;
        }

        if(typeof ExecutionCallback != 'undefined') {
            Execution.setCallback(ExecutionCallback);
        }

        jQuery.each(this._extraEnvironment, function(index, value) {
            Execution.addEnvironment(index, value);
        });

        Execution.setListViewMode(this._ListViewMode);
        Execution.setBackgroundMode(this._backgroundMode);
        Execution.setWorkflowById(workflow_id);
        Execution.execute();

    }; /** ExecuteById **/

    this._submitStartfields = function(fields, urlStr) {
        app.hideModalWindow();
        RedooUtils('Workflow2').blockUI({
            'message' : 'Workflow is executing',
            // disable if you want key and mouse events to be enable for content that is blocked (fix for select2 search box)
            bindEvents: false,

            //Fix for overlay opacity issue in FF/Linux
            applyPlatformOpacityRules : false
        });

        jQuery.post("index.php", {
                "module" : "Workflow2",
                "action" : "Execute",
                "file"   : "ajaxExecuteWorkflow",

                "crmid" : this.crmid,
                "workflow" : this._workflowid,
                allow_parallel: this._allowParallel,
                "startfields": fields
            },
            jQuery.proxy(function(response) {
                RedooUtils('Workflow2').unblockUI();

                try {
                    response = jQuery.parseJSON(response);
                } catch (e) {
                    console.log(response);
                    return;
                }

                if(response["result"] == "ok") {
                    if(ENABLEredirectionOrReloadAfterFinish) {
                        window.location.reload();
                    }
                } else {
                    console.log(response);
                }
            }, this)
        );
    }

    this.closeForceNotification = function(messageId) {
        jQuery.post('index.php?module=Workflow2&action=MessageClose', { messageId:messageId, force: 1 });
    }

    this.parseMessages = function() {
        if(typeof WorkflowRecordMessages != 'object' || WorkflowRecordMessages.length == 0) {
            return;
        }
        RedooUtils('Workflow2').loadScript('modules/Workflow2/views/resources/js/noty/jquery.noty.packaged.min.js').then(jQuery.proxy(function()
        {
            jQuery.each(WorkflowRecordMessages, function(index, value) {

                if(typeof WFDvisibleMessages['workflowMessage' + value['id']] != 'undefined' && WFDvisibleMessages['workflowMessage' + value['id']] == true) {
                    return;
                }

                var type = 'alert';
                switch(value.type) {
                    case 'success':
                        type = 'success';
                        break;
                    case 'info':
                        type = 'alert';
                        break;
                    case 'error':
                        type = 'error';
                        break;
                }
                value.message = '<strong>' + value.subject + "</strong><br/>" + value.message;

                if(value.show_until != '') {
                    value.message += '<br/><span style="font-size:10px;font-style: italic;">' +value.show_until + '</span>';
                }
                if(WFUserIsAdmin == true) {
                    value.message += '&nbsp;&nbsp;<a href="#" style="font-size:10px;font-style: italic;" onclick="closeForceNotification(' + value.id + ');">(Remove Message)</a>';
                }

                WFDvisibleMessages['workflowMessage' + value['id']] = true;
                if(value.position != -1) {
                    noty({
                        text: value.message,
                        id: 'workflowMessage' + value['id'],
                        type: value.type,
                        timeout: false,
                        'layout': value.position,
                        'messageId': value.id,
                        callback: {
                            "afterClose": function () {
                                WFDvisibleMessages['workflowMessage' + this.options.messageId] = false;
                                jQuery.post('index.php?module=Workflow2&action=MessageClose', {messageId: this.options.messageId});
                            }
                        }
                    });
                }
            });
        }), this);
    }

    this.loadCachedScript = function( url, options ) {

        // Allow user to set any option except for dataType, cache, and url
        options = jQuery.extend( options || {}, {
            dataType: "script",
            cache: true,
            url: url
        });

        // Use $.ajax() since it is more flexible than $.getScript
        // Return the jqXHR object so we can chain callbacks
        return jQuery.ajax( options );
    };
}
;var WorkflowRunning = false;

var Workflow2Frontend = {
    viewMode:false,
    runCampaignRealationWF:function(workflow_id) {
        runListViewWorkflow(workflow_id);
    },
    showWorkflowPopup:function(MainModule, RecordIds, MainView) {

        //var MainView = RedooUtils('Workflow2').getViewMode(parentEle);
//        var RecordIds = RedooUtils('Workflow2').getRecordIds(parentEle);

        RedooAjax('Workflow2').postView('WorkflowPopup', {
            'target_module': MainModule,
            'target_view': MainView,
            'target_record': RecordIds
        }).then(function(response) {
            RedooUtils('Workflow2').showModalBox(response).then(function(data) {
                jQuery('[type="submit"]', data).on('click', function(e) {
                    if(jQuery('#workflow2_workflowid').val() == '' || jQuery('#workflow2_workflowid').val() == null) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    RedooUtils('Workflow2').hideModalBox();

                    var crmid = jQuery('.WorkflowPopupCRMID', data).val();
                    var workflow = new Workflow();
                    workflow.execute(jQuery('#workflow2_workflowid').val() , crmid);
                });
            });
        });
    },
    TopbuttonHandler:function(parentEle) {
        var MainModule = RedooUtils('Workflow2').getMainModule(parentEle);
        var CurrentViewMode = FlexUtils('Workflow2').getViewMode();

        if (
            typeof WFDFrontendConfig !== 'undefined' &&
            typeof WFDFrontendConfig['morebtn'] !== 'undefined' &&
            typeof WFDFrontendConfig['morebtn'][MainModule] !== 'undefined'
        ) {
            if(jQuery('.detailViewButtoncontainer ul.dropdown-menu').hasClass('WFDAddHandler') === false) {
                var html = '';
                jQuery.each(WFDFrontendConfig['morebtn'][MainModule], function(index, value) {
                    var rand = Math.floor(Math.random() * 9999999) + 1000000;
                    html += '<li data-id="' + value.workflowid + '" class="wfdButtonMoreBtn" style="' + (value.color != '' ? 'color:' + value.textcolor + ';background-color: ' + value.color + ';':'') + '"><a href="#" style="' + (value.color != '' ? 'color:' + value.textcolor + ';':'') + '">' + value.label + '</a></li>';
                });
                jQuery('.detailViewButtoncontainer ul.dropdown-menu').addClass('WFDAddHandler');
                jQuery('.detailViewButtoncontainer ul.dropdown-menu').append(html);

                jQuery('.wfdButtonMoreBtn a').on('click', function(e) {
                    e.preventDefault();
                    return false;
                });
                jQuery('.wfdButtonMoreBtn').on('click', function() {
                    var workflow = new Workflow();
                    workflow.execute(jQuery(this).data('id'), jQuery('#recordId').val());
                });
            }
        }

        if (
            CurrentViewMode == 'listview' &&
            typeof WFDFrontendConfig !== 'undefined' &&
            typeof WFDFrontendConfig['listviewbtn'] !== 'undefined' &&
            typeof WFDFrontendConfig['listviewbtn'][MainModule] !== 'undefined'
        ) {

            if(jQuery('.detailViewButtoncontainer').hasClass('WFDAddHandler') === false) {
                var html = '';
                jQuery.each(WFDFrontendConfig['listviewbtn'][MainModule], function(index, value) {
                    var rand = Math.floor(Math.random() * 9999999) + 1000000;

                    if(value.color != '') {
                        var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                    } else {
                        var cssStyle = ''
                    }

                    html += '<button type="button" data-id="' + value.workflowid + '" class="btn btn-default wfdButtonTopbutton" style="' + cssStyle + '">' + value.label + '</button>';
                });

                jQuery('.detailViewButtoncontainer').addClass('WFDAddHandler');
                jQuery('.listViewActionsContainer').append(html);

                jQuery('.wfdButtonTopbutton').on('click', function(e) {
                    e.preventDefault();
                    var workflow = new Workflow();
                    workflow.execute(jQuery(this).data('id'), 0);
                });
            }
        }
    },
    RelatedListHandler:function(parentEle) {

        if(typeof WFDFrontendConfig !== 'undefined' && typeof WFDFrontendConfig['relatedbtn'] !== 'undefined') {
            var MainModule = Workflow2Frontend.getMainModule(parentEle);
            if(typeof WFDFrontendConfig['relatedbtn'][MainModule] !== 'undefined') {
                var btnHtml = '';
                for(var index in WFDFrontendConfig['relatedbtn'][MainModule] ) {
                    if (WFDFrontendConfig['relatedbtn'][MainModule].hasOwnProperty(index) && jQuery.isNumeric(index)) {
                        var value = WFDFrontendConfig['relatedbtn'][MainModule][index];

                        btnHtml += '<button type="button" class="btn CampaignRelationBtn" onclick="Workflow2Frontend.runCampaignRealationWF(' + value['workflowid'] +');" style="background-color:' + value['color'] +';">' + value['label'] +'</button>';
                    }
                }

                var parent = jQuery(jQuery('div.relatedHeader .btn')[0]).closest('.btn-group').parent();
                parent.append('<div class="btn-group">' + btnHtml + '</div>');
            }
        }


    },
    getMainModule:function (parentEle) {
        var viewMode = Workflow2Frontend.getViewMode(parentEle);

        if (viewMode == 'detailview' || viewMode == 'summaryview') {
            return jQuery('#module', parentEle).val();
        } else if (viewMode == 'editview' || viewMode == 'quickcreate') {
            return jQuery('[name="module"]', parentEle).val();
        } else if (viewMode == 'listview') {
            return jQuery('#module', parentEle).val();
        } else if (viewMode == 'relatedview') {
            if (jQuery('[name="relatedModuleName"]', parentEle).length > 0) {
                return jQuery('[name="relatedModuleName"]', parentEle).val();
            }
            if (jQuery('#module', parentEle).length > 0) {
                return jQuery('#module', parentEle).val();
            }
        }
        return '';
    },
    getViewMode: function(parentEle, obj) {
        var viewEle = jQuery("#view", parentEle);

        if(viewEle.length > 0 && viewEle[0].value == "List") {
            Workflow2Frontend.viewMode = "listview";
        }

        if(jQuery(".detailview-table", parentEle).length > 0) {
            Workflow2Frontend.viewMode = "detailview";
        } else if(jQuery(".summary-table", parentEle).length > 0) {
            Workflow2Frontend.viewMode = "summaryview";
        } else if(jQuery(".recordEditView", parentEle).length > 0) {
            if(jQuery('.quickCreateContent', parentEle).length == 0) {
                Workflow2Frontend.viewMode = "editview";
            } else {
                Workflow2Frontend.viewMode = "quickcreate";
            }
        }

        if(jQuery('.relatedContents', parentEle).length > 0) {
            Workflow2Frontend.viewMode = "relatedview";

            if(jQuery('td[data-field-type]', parentEle).length > 0) {
                Workflow2Frontend.popUp = false;
            } else {
                Workflow2Frontend.popUp = true;
            }
        }

        if(Workflow2Frontend.viewMode === false) {
            if(jQuery('#view', parentEle).length > 0) {
                if(jQuery('#view', parentEle).val() == 'Detail') {
                    Workflow2Frontend.viewMode = 'detailview';
                }
            }
        }

        return Workflow2Frontend.viewMode;
    }
};;window.WorkflowExecution = function() {
    this._crmid = null;
    this._execId = null;

    this._workflowId = null;
    this._workflowTrigger = null;

    this._execId = null;
    this._blockID = null;

    this._requestValues = {};
    this._requestValuesKey = null;

    this._callback = null;
    this._allowParallel = false;
    this._allowRedirection = true;
    this._backgroundMode = false;
    this._extraEnvironment = {};
    this._ListViewMode = false;
    this._FrontendType = undefined;

    this.setFrontendType = function(type) {
        this._FrontendType = type;
    };

    this.setEnvironment = function(envVars) {
        this._extraEnvironment = envVars;
    };

    this.addEnvironment = function(key, value) {
        this._extraEnvironment[key] = value;
    };

    this.setRequestedData = function(values, relatedKey) {
        this._requestValues = values;
        this._requestValuesKey = relatedKey;
    };

    this.allowParallel = function() {
        this._allowParallel = true;
    };
    this.init = function(crmid) {
        this._crmid = crmid;
    };

    this.setWorkflowByTrigger = function(triggerName) {
        this._workflowTrigger = triggerName;
        this._workflowId = undefined;
    };

    this.setWorkflowById = function(workflow_id) {
        this._workflowId = workflow_id;
        this._workflowTrigger = undefined;
    };

    this.setBackgroundMode = function(value) {
        this._backgroundMode = value;
    };
    this.setCallback = function(callback) {
        this._callback = callback;
    };
    this.setListViewMode = function(listView) {
        this._ListViewMode = listView == true;
    };

    this.enableRedirection = function(value) {
        this._allowRedirection = value ? true : false;
    };

    this._handleDownloads = function(response) {
        var html = '<p>' + response.download_text + '</p>';
        html += '<ul style="list-style:none;">';
        $.each(response.downloads, function(index, data) {
            html += '<li style="margin-bottom:5px;"><a href="' + data.url + '" target="_blank"><i class="fa fa-download" style="margin-right:10px;" aria-hidden="true"></i> <strong>' + data.filename + '</strong></a></li>';
        });
        html += '</ul>';

        bootbox.dialog({
            message:html,
            closeButton:true,
            buttons: {
                ok: {
                    label: 'Ok',
                    className: 'btn-success'
                }
            }
        });
    };

    this._handleRedirection = function(response) {
        if(this._allowRedirection === true) {
            if(response["redirection_target"] == "same") {
                window.location.href = response["redirection"];
                return true;
            } else {
                window.open(response["redirection"]);
                return true;
            }
        }
        return false;
    };

    this.setContinue = function(execID, blockID) {
        this._execId = execID;
        this._blockID = blockID;
    };

    this.executeWithForm = function(form) {
        if(typeof jQuery(form).ajaxSubmit == 'undefined') {
            console.error('jquery.forms plugin requuired!');
            return;
        }

        WorkflowRunning = true;
        RedooUtils('Workflow2').blockUI({ message: '<h4 style="padding:5px 0;"><img src="modules/Workflow2/icons/sending.gif" style="margin-bottom:20px;" /><br/>Please wait ...</h4>' });

        jQuery(form).ajaxSubmit({
            'url' : "index.php",
            'type': 'post',
            data: {
                "module" : "Workflow2",
                "action" : "ExecuteNew",

                'crmid' : this._crmid,

                'workflowID' : this._workflowId === null ? undefined : this._workflowId,
                'allowParallel': this._allowParallel ? 1 : 0,

                'continueExecId': this._execId === null ? undefined : this._execId,
                'continueBlockId': this._blockID === null ? undefined : this._blockID,
                'requestValues': this._requestValues === null ? undefined : this._requestValues,
                'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                'extraEnvironment': this._extraEnvironment,
                'listviewmode': this._ListViewMode ? 1 : 0
            },
            success:jQuery.proxy(this.executionResponse, this),
            error:jQuery.proxy(this.executionResponse, this)
        });

    };

    this.frontendWorkflows = function(workflowIDs, record) {
        var dfd = jQuery.Deferred();

        RedooAjax('Workflow2').post('index.php', {
            'module': 'Workflow2',
            'action': 'FrontendWorkflowExec',
            'workflow_ids': workflowIDs,
            'record': record,
            'dataType': 'json'
        }).then($.proxy(function(data) {
            //this.executionResponse(data);

            dfd.resolve( data );
        }, this));

        return dfd.promise();
    };

    this.execute = function() {
        if(this._backgroundMode === false) {
            RedooUtils('Workflow2').blockUI({message: '<h4 style="padding:5px 0;"><img src="modules/Workflow2/icons/sending.gif" style="margin-bottom:20px;"/><br/>Please wait ...</h4>'});
        }

        WorkflowRunning = true;
        jQuery.post("index.php", {
                "module" : "Workflow2",
                "action" : "ExecuteNew",
                //XDEBUG_PROFILE:1,

                'frontendtype': this._FrontendType,
                'crmid' : this._crmid,

                'workflowID' : this._workflowId === null ? undefined : this._workflowId,
                'triggerName' : this._workflowTrigger === null ? undefined : this._workflowTrigger,

                'allowParallel': this._allowParallel ? 1 : 0,

                'continueExecId': this._execId === null ? undefined : this._execId,
                'continueBlockId': this._blockID === null ? undefined : this._blockID,
                'requestValues': this._requestValues === null ? undefined : this._requestValues,
                'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                'extraEnvironment': this._extraEnvironment,
                'listviewmode': this._ListViewMode ? 1 : 0
            }
        ).always(jQuery.proxy(this.executionResponse, this));
    };

    this.executionResponse = function(responseTMP) {
        if(typeof responseTMP == 'object' && typeof responseTMP.responseText != 'undefined') {
            responseTMP = responseTMP.responseText;
        }

        if(responseTMP.indexOf('Invalid request') !== -1) {
            alert('You did not do any action in VtigerCRM since a long time. The page needs to be reloaded, before you could use the Workflow Designer.');
            window.location.reload();
            return;
        }

        if(this._backgroundMode === false) {
            RedooUtils('Workflow2').unblockUI();
        }

        WorkflowRunning = false;

        var response;
        try {
            response = jQuery.parseJSON(responseTMP);
        } catch(exp) {
            console.log(exp);
            console.log(responseTMP);
            return;
        }

        if(response !== null && response["result"] == "ready") {
            if(typeof this._callback == 'function') {
                var retVal = this._callback.call(this, response);

                if(typeof retVal != 'undefined' && retVal === false) {
                    return;
                }
            }

            if(typeof response["redirection"] != "undefined" && typeof response["downloads"] != "undefined") {
                this._handleDownloads(response);
                this._handleRedirection(response);
                return;
            } else if(typeof response["redirection"] != "undefined") {
                this._handleRedirection(response);
                return;
            } else if(typeof response["downloads"] != "undefined") {
                this._handleDownloads(response);
                return;
            }

            if(this._allowRedirection === true && this._backgroundMode === false && typeof response["prevent_reload"] === 'undefined') {
                window.location.reload();
            }
        } else if(response !== null && response["result"] == "asktocontinue") {
            jQuery('body').append('<style type="text/css">.bootbox.modal {z-index: 9999 !important;}</style>');
            bootbox.confirm({
                message: response['question'],
                buttons: {
                    confirm: {
                        label: response['LBL_YES'],
                        className: 'btn-success'
                    },
                    cancel: {
                        label: response['LBL_NO'],
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if(result === true) {
                        FlexUtils('Workflow2').hideModalBox();
                        var Execution = new WorkflowExecution();
                        Execution.setContinue(response['execid'], response['blockid']);
                        Execution.execute();
                    }
                }
            });
        } else if(response !== null && response["result"] == "requestForm") {
            this._requestValuesKey = response['fields_key'];
            this._execId = response['execId'];

            if(typeof RequestValuesForm2 == 'undefined') {
                jQuery.getScript('modules/Workflow2/views/resources/js/RequestValuesForm2.js', jQuery.proxy(function() {

                    var requestForm = new RequestValuesForm2(response['fields_key'], response);
                    requestForm.setCallback(jQuery.proxy(this.submitRequestFields, this));
                    requestForm.show(response.html, response.script);

                    //response, this._requestValuesKey, response['request_message'], , response['stoppable'], response['pausable'], response['options']);
                }, this));
            } else {
                var requestForm = new RequestValuesForm2(response['fields_key'], response);
                requestForm.setCallback(jQuery.proxy(this.submitRequestFields, this));
                requestForm.show(response.html, response.script);
            }
        } else if(response !== null && response["result"] == "reqvalues") {
            this._requestValuesKey = response['fields_key'];
            this._execId = response['execId'];

            if(typeof RequestValuesForm == 'undefined') {
                jQuery.getScript('modules/Workflow2/views/resources/js/RequestValuesForm.js', jQuery.proxy(function() {
                    var requestForm = new RequestValuesForm();
                    requestForm.show(response, this._requestValuesKey, response['request_message'], jQuery.proxy(this.submitRequestFields, this), response['stoppable'], response['pausable'], response['options']);
                }, this));
            } else {
                var requestForm = new RequestValuesForm();
                requestForm.show(response, this._requestValuesKey, response['request_message'], jQuery.proxy(this.submitRequestFields, this), response['stoppable'], response['pausable'], response['options']);
            }
        } else if(response !== null && response["result"] == "error") {
            console.log('Errorcode: ' + response.errorcode);
            app.showModalWindow('<div style="padding:10px 50px;text-align:center;">' + response.message + '</div>');
        } else {
            console.log(response);
        }
    };

    this.submitRequestFields = function(key, values, value2, form) {

        this._requestValues = {};
        this._requestValuesKey = key;

        var html = '';
        jQuery.each(values, jQuery.proxy(function(index, value) {
            if(value.name.substr(-2) != '[]') {
                this._requestValues[value.name] = value.value;
            } else {
                var varName = value.name.substr(0, value.name.length - 2);
                if(typeof this._requestValues[varName] === 'undefined') {
                    this._requestValues[varName] = [];
                }

                this._requestValues[varName].push(value.value);
            }
        }, this));

        if(jQuery('[type="file"]', form).length > 0) {
            var html = '<form action="#" method="POST" onsubmit="return false;">';
            jQuery('input, select, button', form).attr('disabled', 'disabled');
            jQuery('[type="file"]', form).removeAttr('disabled').each(jQuery.proxy(function(index, ele) {
                var name = jQuery(ele).attr('name');
                jQuery(ele).attr('name', 'fileUpload[' + name + ']');

                this._requestValues[name] = jQuery(ele).data('filestoreid');
            }, this));
            html += '</form>';
            this.executeWithForm(form);
            return;
        }

        this.execute();
    }
};
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImZyb250ZW5kLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImZyb250ZW5kLmpzIiwic291cmNlc0NvbnRlbnQiOltdfQ==
}())
/** HANDLER START **/
var WFDFrontendConfig = [];
var WFDLanguage = {"These Workflow requests some values":"These Workflow requests some values","Execute Workflow":"Execute Workflow","enter values later":"enter values later","stop Workflow":"stop Workflow","Executing Workflow ...":"Executing Workflow ..."};
/* Render take 0.01s */

/** MODULELANGUAGESTRINGS START **/
if(typeof FLEXMODLANGUAGE == "undefined") var FLEXMODLANGUAGE = {};
if(typeof FLEXLANG == "undefined") var FLEXLANG = function(key, module) { var lang = app.getUserLanguage(); if(typeof FLEXMODLANGUAGE[module] != "undefined" && typeof FLEXMODLANGUAGE[module][lang] != "undefined" &&  typeof FLEXMODLANGUAGE[module][lang][key] != "undefined") { return FLEXMODLANGUAGE[module][lang][key]; } return key; };
FLEXMODLANGUAGE["Workflow2"] = {"ru_ru":{"LBL_GET_KNOWN_ENVVARS":"\u041e\u0431\u043d\u0430\u0440\u0443\u0436\u0435\u043d\u043d\u044b\u0435 \u043f\u0435\u0440\u0435\u043c\u0435\u043d\u043d\u044b\u0435 \u043e\u043a\u0440\u0443\u0436\u0435\u043d\u0438\u044f","LBL_DUPLICATE_BLOCK":"\u0414\u0443\u0431\u043b\u0438\u0440\u043e\u0432\u0430\u0442\u044c \u0431\u043b\u043e\u043a","LBL_DELETE_BLOCK":"\u0423\u0434\u0430\u043b\u0438\u0442\u044c \u0431\u043b\u043e\u043a","LBL_CHANGE_BLOCKCOLOR":"\u0418\u0437\u043c\u0435\u043d\u0438\u0442\u044c \u0446\u0432\u0435\u0442","LBL_REMOVE_BLOCKCOLOR":"\u0423\u0434\u0430\u043b\u0438\u0442\u044c \u0446\u0432\u0435\u0442","HEAD_USAGE_OF_THIS_CONNECTION":"\u041f\u0443\u0442\u044c","LBL_DATE":"\u0414\u0430\u0442\u0430","TXT_CHOOSE_VALID_FIELD":"\u0412\u044b\u0431\u0435\u0440\u0438\u0442\u0435 \u043f\u043e\u043b\u0435","LBL_MANAGE_SIDEBARTOOGLE":"\u041a\u043e\u043d\u0441\u0442\u0440\u0443\u043a\u0442\u043e\u0440 \u0431\u0438\u0437\u043d\u0435\u0441-\u043f\u0440\u043e\u0446\u0435\u0441\u0441\u043e\u0432 \u043e\u0431\u0440\u0430\u0431\u0430\u0442\u044b\u0432\u0430\u0435\u0442 \u0432\u0430\u0448\u0438 \u0434\u0430\u043d\u043d\u044b\u0435","LBL_CREATE_TYPE":"\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u043d\u043e\u0432\u044b\u0439 \u0431\u043b\u043e\u043a \u0432\u0440\u0443\u0447\u043d\u0443\u044e","LBL_SAVED_SUCCESSFULLY":"\u0423\u0441\u043f\u0435\u0448\u043d\u043e \u0441\u043e\u0445\u0440\u0430\u043d\u0435\u043d\u043e","page":"\u0421\u0442\u0440\u0430\u043d\u0438\u0446\u0430","select all of this type":"\u0412\u044b\u0431\u0440\u0430\u0442\u044c \u0432\u0441\u0435 \u044d\u0442\u043e\u0433\u043e \u0442\u0438\u043f\u0430","LBL_PASTE_BLOCK":"\u0412\u0441\u0442\u0430\u0432\u0438\u0442\u044c \u0431\u043b\u043e\u043a\u0438","LBL_COPY_BLOCK":"\u0421\u043a\u043e\u043f\u0438\u0440\u043e\u0432\u0430\u0442\u044c \u0431\u043b\u043e\u043a\u0438","Reset value":"\u0421\u0431\u0440\u043e\u0441\u0438\u0442\u044c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435","Empty field":"\u041f\u0443\u0441\u0442\u043e\u0435 \u043f\u043e\u043b\u0435","Reference Field":"\u0418\u0441\u0445\u043e\u0434\u043d\u043e\u0435 \u043f\u043e\u043b\u0435","Available fields":"\u0414\u043e\u0441\u0442\u0443\u043f\u043d\u044b\u0435 \u043f\u043e\u043b\u044f","Quantity":"\u041a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e","Unit Price":"\u0426\u0435\u043d\u0430 \u0435\u0434\u0438\u043d\u0438\u0446\u044b","Product Description":"\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435","Product":"\u0422\u043e\u0432\u0430\u0440","Export Blocks by Text":"\u042d\u043a\u0441\u043f\u043e\u0440\u0442\u0438\u0440\u043e\u0432\u0430\u0442\u044c \u0417\u0430\u0434\u0430\u0447\u0438 \u0432 \u0442\u0435\u043a\u0441\u0442","Import Blocks by Text":"\u0418\u043c\u043f\u043e\u0440\u0442\u0438\u0440\u043e\u0432\u0430\u0442\u044c \u0437\u0430\u0434\u0430\u0447\u0438 \u0438\u0437 \u0442\u0435\u043a\u0441\u0442\u0430","Expression-Errors found":"\u041e\u0431\u043d\u0430\u0440\u0443\u0436\u0435\u043d\u044b \u043e\u0448\u0438\u0431\u043a\u0438 \u0432 \u0412\u044b\u0440\u0430\u0436\u0435\u043d\u0438\u044f\u0445","Name of new Folder?":"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u043d\u043e\u0432\u043e\u0439 \u043f\u0430\u043f\u043a\u0438","Filter Workflows":"\u0424\u0438\u043b\u044c\u0442\u0440\u043e\u0432\u0430\u0442\u044c \u0411\u0438\u0437\u043d\u0435\u0441-\u041f\u0440\u043e\u0446\u0435\u0441\u0441\u044b","Please wait":"Please wait","WF_DELETE_CONFIRM":"\u0412\u044b \u0434\u0435\u0439\u0441\u0442\u0432\u0438\u0442\u0435\u043b\u044c\u043d\u043e \u0445\u043e\u0442\u0438\u0442\u0435 \u0443\u0434\u0430\u043b\u0438\u0442\u044c \u044d\u0442\u043e\u0442 \u0431\u0438\u0437\u043d\u0435\u0441-\u043f\u0440\u043e\u0446\u0435\u0441\u0441?\n\u0412 \u0441\u043b\u0443\u0447\u0430\u0435 \u0443\u0434\u0430\u043b\u0435\u043d\u0438\u044f, \u0432\u0441\u0435 \u0437\u0430\u043f\u0443\u0449\u0435\u043d\u043d\u044b\u0435 \u044d\u043a\u0437\u0435\u043c\u043f\u043b\u044f\u0440\u044b \u044d\u0442\u043e\u0433\u043e \u0431\u0438\u0437\u043d\u0435\u0441-\u043f\u0440\u043e\u0446\u0435\u0441\u0441\u0430 \u0431\u0443\u0434\u0443\u0442 \u043d\u0435\u043c\u0435\u0434\u043b\u0435\u043d\u043d\u043e \u043e\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d\u044b."}};
/** MODULELANGUAGESTRINGS END **/