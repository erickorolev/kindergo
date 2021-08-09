var jsPlumbInit = false;
var jsPlumbInstance = null;
function initJsPlumb(afterFunction) {

    jsPlumbInstance = jsPlumb.getInstance({
        // default drag options
        DragOptions : { cursor: "pointer", zIndex:2000 },
        ConnectionOverlays: [
            [ "Arrow",
                {
                    location: 0.7,
                    width:15,
                    length:15,
                    paintStyle: {
                        fil:'black',
                        strokeWidth:3,
                        stroke:'#ffffff'
                    },
                    cssClass:'directionArrow noselect'
                }
            ],
            [ "Arrow",
                {
                    location: 0.3,
                    width:15,
                    length:15,
                    paintStyle: {
                        fil:'black',
                        strokeWidth:3,
                        stroke:'#ffffff'
                    },
                    cssClass:'directionArrow noselect'
                }
            ]
        ]
    });

    jsPlumbInstance.importDefaults({
        ConnectorZIndex:5
      });

    jQuery('#workflowObjectsContainer').trigger('designer:init', [jsPlumbInstance]);

    jsPlumbInit = true;
    jsPlumbInstance.batch(function() {
        afterFunction(jsPlumbInstance);
    });
    jsPlumbInit = false;

    jsPlumbInstance.repaintEverything();

    //jsPlumbInstance
    jQuery('.wfBlock').bind( "dblclick", onDblClickBlock);

    jQuery('#workflowActiveSwitch').on('change', function(e) {
        var checked = jQuery(this).prop('checked');
        saveWorkflowActivationStatus(checked);
    });
    //jQuery('.colorLayer, .idLayer').bind( "dblclick", function(event) { jQuery(event.target).parent().trigger("dblclick"); });

    jQuery(".wfBlock img.settingsIcon").bind( "click", function() {
        onDblClickBlock({target:{id:jQuery(this).parent().attr("id")}});
    });

    jQuery("#mainModelWindow").hide();

    jQuery('#stopAllRunningInstances').on('click', function() {
        jQuery.post('index.php', {parent:'Settings', module:'Workflow2', action:'StopAllRunning', workflowID:workflow_id}, function() {
            jQuery('#runningWarning').slideUp('fast');
            jQuery('.overviewStatisticNumber').html("0");
        });
    });

}

/* Event Handler */
function onDblClickBlock(event) {
    var targetID = event.currentTarget.id;

    parts = targetID.split("__");

    if(parts[0] == "block") {
        jQuery('#workflowObjectsContainer').trigger('block:dblclick', [parts[1], targetID]);
    } else if(parts[0] == "person") {
        jQuery('#workflowObjectsContainer').trigger('object:dblclick', ['Users', parts[1], targetID]);
    }
}

function onDragStopBlock(params) {
    var ele = params.el;
    if(jQuery(ele).hasClass("colorLayer")) {
        ele = jQuery(ele).parent();
    } else {
        ele = jQuery(ele);
    }

    jQuery('span.blockDescription', ele).show();
    jQuery('img.settingsIcon', ele).show();

    jQuery('#workflowObjectsContainer').trigger('block:dragstop', [jQuery(ele), params]);
}

function filterGetInput(value) { return value }
function getInput(iconSrc, scope, isSource, isTarget, isPerson, backgroundColor, label) {
    gradients = backgroundColor !== undefined ? backgroundColor : {stops:[[0, "#0060bf"], [1, "#aac6e2"]]};
//            gradients = backgroundColor !== undefined ? backgroundColor : {stops:[[0, "#f00"], [1, "#0f0"]]};
    dashStyle = "2 0";
    lineWidth = 5;

    if(isPerson === false) {
        if(isTarget === false) {
            console.log(label);
            var lowerLabel = label.toLowerCase();
            var isSuccess = (lowerLabel == 'true' || lowerLabel == 'yes' || lowerLabel == 'ok');
            // Output
            var paintStyle = {
                gradient : {
                    stops:isSuccess ? [ [0, "#89e35b"], [1, "#89e35b"] ] : [ [0, "#e69e99"], [1, "#e69e99"] ],
                    offset:37.5,
                    innerRadius:2
                },
                radius:6,
                strokeWidth:4
            };
        } else {

            // Input
            var paintStyle = {
                gradient : {
                    stops:[ [0, "#aac6e2"], [1, "#aac6e2"] ],
                    offset:37.5,
                    innerRadius:2
                },
                radius:6,
                strokeWidth:4
            };
        }

        var endPoint = '';
    }

    return filterGetInput({
        endpoint:endPoint,
        // endpoint:'Rectangle',
        paintStyle:paintStyle,
        hoverClass:'hoverClass',
        // cssClass:'defaultClass' + (isPerson?" personConnector":"") + (isTarget?" InputEndpoint":"") + (isSource?" OutputEndpoint":""),
        isSource:(isReadonly ? false : isSource),
        reattach:(isReadonly ? false : isSource),
        isTarget:(isReadonly ? false : isTarget),
        scope:scope,
        connector:[
            "Bezier", {
                curviness:70,
                stub:100
            }
        ],
        connectorStyle : {
            gradient:gradients,
            stroke:5,
            strokeStyle:"#00f",
            strokeWidth:5
        },
        hoverPaintStyle:{
    		fillStyle:"#216477",
    		strokeStyle:"#216477",
            zIndex:9999
        },
        connectorHoverStyle:{
            gradient:{stops:[[0, '#216477']]},
            lineWidth:4,
            strokeStyle:"#216477",
            outlineWidth:5,
            outlineColor:"fafafb",
            zIndex:9999
        },
        beforeDrop:function(params) {
            return true;
        }
    });
}

function saveWorkflowActivationStatus(isActive) {
    jQuery.post('index.php', { module:'Workflow2', parent:'Settings', action:'WorkflowStatus', value:isActive?1:0, workflow:workflow_id }, function(response) {
        if(response.show_warning == '1') {
            jQuery('#runningWarning').slideDown('fast');
        } else {
            jQuery('#runningWarning').slideUp('fast');
        }
    }, 'json');
}
function setColorLayer(id, color) {
    jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
        var id = jQuery(ele).attr('id');
        var ele = jQuery(".colorLayer", ele);
        jQuery("#" + id + " .colorLayer").data("color", color);
        jQuery.post("index.php?module=Workflow2&action=ColorSet&parent=Settings", {block_id:id.replace("block__", ""), color:color});
        if(color == "FFFFFF") {
            ele.hide();
            ele.removeClass("colored");
        } else {
            ele.show().css("backgroundColor", "#" + color);
            ele.addClass("colored");
        }

    });
}
function setTaskText(block_id, value) {
    var ele = jQuery("#block__" + block_id + "_description");

    ele.html("<br>" + value);
}
function setBlockActive(block_id, value) {
    var ele = jQuery("#block__" + block_id);

    if(value == true) {
        ele.removeClass("wfBlockDeactive");
    } else {
        ele.addClass("wfBlockDeactive");
    }
}
function saveWorkflowTitle() {
    jQuery("#workflow_title").attr("disabled", "disabled");
    var title = jQuery("#workflow_title").val();

    jQuery.post("index.php", { module: 'Workflow2', action: 'WorkflowSetTitle', 'parent': 'Settings', workflow:workflow_id, title:title }, function() {
        jQuery("#workflow_title").removeAttr("disabled");
    });

}

var workflowDesignerObjects = {};
var workflowDesignerObjectCounter = 0;

function showOptionsContainer() {
    jQuery("#optionsContainer").slideToggle("fast");
}
function refreshBlockIDs() {
    var showBlockIds = jQuery("#optionShowBlockId").prop("checked");
    document.getElementsByClassName = null;

    if(showBlockIds == true) {
        jQuery(".idLayer").show();
    } else {
        jQuery(".idLayer").hide();
    }
}

var currentWorkSpaceWidth = jQuery('#mainWfContainer').width();
function resizeWorkspace() {
    currentWorkSpaceHeight = jQuery('body').height() - 70;
    jQuery('#mainWfContainer').css('height', (jQuery('body').height() - 70) + 'px');
}

var Dnd = false;
jQuery(function() {
    jQuery('.accordion-heading').on('click', resizeWorkspace);
    jQuery('.typeContainer').sortable({
        connectWith: ".typeContainer",
        delay:200,
        distance: 20,
        handle: '.moveTypes',
        placeholder: 'placeholderType',
        helper: 'helperType',
        axis: "y",
        zIndex: 9999,
        stop: function( event, ui ) {
            var block = jQuery(ui.item[0]).closest('.typeContainer').data('block');
            Dnd.finishBlock = block;

            if(Dnd.finishBlock != Dnd.startBlock) {
                reorderCategory(Dnd.startBlock);
            }
            reorderCategory(Dnd.finishBlock);

            Dnd = false;
        },
        start: function(event, ui) {
            Dnd = {};
            var block = jQuery(ui.item[0]).closest('.typeContainer').data('block');
            Dnd.startBlock = block;
        }
    });

    jQuery('.typeSearchBox').on('click', function(e) {
        jQuery(this).select();
    });
    jQuery('.typeSearchBox').on('keyup', function(e) {
        var value = this.value;
        if(value.length >= 3) {
            jQuery('div.taskWidgetContainer').hide();
            jQuery('div.taskWidgetContainer .WorkflowTypeContainer').hide();
            jQuery('div.taskWidgetContainer .settingsgroup').addClass('in');
            jQuery('div.taskWidgetContainer .WorkflowTypeContainer[data-search*="' + value.toLowerCase() + '"]:not(.Hidden)').show().closest('.taskWidgetContainer').show().find('.accordion-body').addClass('in');
        } else {
            jQuery('div.taskWidgetContainer .WorkflowTypeContainer:not(.Hidden)').show();
            jQuery('div.taskWidgetContainer').each(function(index, ele) {
                if(jQuery('.WorkflowTypeContainer:not(.Hidden)', ele).length > 0) {
                    jQuery(ele).show();
                }
            });

        }
    });
});

function reorderCategory(blockKey) {
    var sort = jQuery('.typeContainer[data-block="' + blockKey + '"]').sortable('toArray', { 'attribute': 'data-type'});
    jQuery.post('index.php', {module:'Workflow2', 'parent': 'Settings', action:'TypesSort', sort:sort, block:blockKey});
}

function refreshTypeList() {
    if(jQuery('#WorkflowModule').length == 0) return;

    var IsInventory = jQuery('#IsInventory').val() == '1';
    var Trigger = jQuery('#WorkflowTrigger').val();
    var Module = jQuery('#WorkflowModule').val().toLowerCase();

    jQuery('.WorkflowTypeContainer').each(function(index, ele) {
        ele = jQuery(ele);
        singleModule = ',' + ele.data('singlemodule') + ',';
        if(singleModule == ',,' ) return;

        if(singleModule.indexOf(',inventory,') != -1) {
            if(IsInventory == true) {
                ele.removeClass('Hidden').show();
                return;
            } else {
                ele.addClass('Hidden').hide();
                return;
            }
        }

        if(singleModule.indexOf(',frontendworkflow,') != -1) {
            if(Trigger != 'WF2_FRONTENDTRIGGER') {
                ele.addClass('Hidden').hide();
                return;
            } else {
                ele.removeClass('Hidden').show();
                return;
            }
        }

        if(singleModule.indexOf(',csvimport,') != -1) {
            if (Trigger != 'WF2_IMPORTER') {
                ele.addClass('Hidden').hide();
                return;
            } else {
                ele.removeClass('Hidden').show();
                return;
            }
        }

        if(singleModule.indexOf(',' + Module + ',') != -1) {
            ele.removeClass('Hidden').show();
            return;
        } else {

            if(singleModule.indexOf(',' + Trigger.toLowerCase() + ',') != -1) {
                ele.removeClass('Hidden').show();
                return;
            } else {
                ele.addClass('Hidden').hide();
                return;
            }
        }

        ele.addClass('Hidden').hide();
    });

    jQuery('.taskWidgetContainer').each(function(index, ele) {
        if(jQuery('.WorkflowTypeContainer:not(.Hidden)', ele).length == 0) {
            jQuery(ele).hide();
        } else {
            jQuery(ele).show();
        }
    });
}
window.setWorkflowTrigger = function(trigger) {
    jQuery('#WorkflowTrigger').val(trigger);
    refreshTypeList();
};

jQuery(function() {
    refreshTypeList();
});