/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 19.12.13 11:08
 * You must not use this file without permission.
 */
var endpoints = {};
var topAnchor;
var leftAnchor = [
    [0, 0.5,    -1, 0,  0, 0],
    /*[1, 0.5,    1, 0,  2, 0],*/
    [0, 0,    -0.5, 1,  -2, 0],
    [0, 1,    -0.5, 1,  -2, 0],
    [1, 1,    0.5, 1,  2, 0],
    [1, 0,    0.5, 1,  2, 0]
];
var bottomAnchor = [
    [0.5,   1,  0,  1,  -5, 0]
];

var rightAnchor = {
    "0":[
        [
            [1, 0.5,   1,  0,  0,  0]
        ]
    ],
    "1":[
        [
            [1, 0.75,   1,  0,  2,  0], [0, 0.75,   -1,  0,  0, 0]
        ]
    ],
    "2":[
        [
            [1, 0.25,   1,  0,  2,  0], [0, 0.25,   -1,  0,  0, 0]
        ],[
            [1, 0.75,   1,  0,  2,  0], [0, 0.75,   -1,  0,  0, 0]
        ]
    ],
    "3":[
        [1, 0.2,    1,  0,  2,  0],
        [1, 0.5,    1,  0,  2,  0],
        [1, 0.8,    1,  0,  2,  0]
    ]
};

var statColor = [
    [""],
    ["#31be00"],
    ["#da9a00", "#31be00"],
    ["#da6700", "#da9a00", "#31be00"]
];
var inputPointOptions  = { anchor:leftAnchor, maxConnections:25};

var currentColorPicker = false;
var lastColors = [];
var lastColorsInit = [];

var _listeners = function(e) {
    e.bind("mouseover", function(ep) {
        ep.showOverlay("lbl");
    });
    e.bind("mouseout", function(ep) {
        ep.hideOverlay("lbl");
    });
};


function setLicense() {
    var license = prompt("Your License-Code:");
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
}

function connectEndpoints(sourceID, targetID) {

    console.log(sourceID, targetID);
    jsPlumbInstance.connect({
        source: endpoints[sourceID],
        target: endpoints[targetID]
    });
}
function initInputPoint(pointID) {
    endpoints[pointID + "__input"] = jsPlumbInstance.addEndpoint(pointID,
        {
            ConnectorZIndex:10,
            uuid:pointID + "__input",
            anchor:leftAnchor,
            maxConnections:maxConnections
        },
        jQuery.extend(getInput("modules/Workflow2/icons/input.png", "flowChart", false, true, false), {parameters:{ "in":pointID + "__input" }})
    );
}
function initOutputPoint(pointID, key, label, ConnectorLabel, counter, index) {
    endpoints[pointID + "__" + key] =
        jsPlumbInstance.addEndpoint(
            pointID, {
                anchor:rightAnchor[label=="Start"?0:counter][index],
                maxConnections:maxConnections,
                uuid:pointID + "__" + key,
                connectorOverlays:[[ "Label", { location:0.5, cssClass:"connectionLabel noselect",visible:(ConnectorLabel!='Next'&&ConnectorLabel!='Start'?true:false),label:ConnectorLabel, id:"label"}]],
                overlays:getOverlay(label)
            },
            jQuery.extend(getInput("modules/Workflow2/icons/output.png", "flowChart", true, false, false, undefined, label), { parameters:{ out:pointID + "__" + key }})
        );


    _listeners(endpoints[pointID + "__" + key]);
}
function initPersonInputPoint(pointID, key, label, counter, index) {
    endpoints[pointID + "__" + key] =
        jsPlumbInstance.addEndpoint(
            pointID, {
                anchor:topAnchor[counter][index],
                maxConnections:maxConnections,
                overlays:getOverlay(label, "personLabel")
            },
            jQuery.extend(getInput("modules/Workflow2/icons/peopleInput.png", "person", false, true, true), {parameters:{ "in":pointID + "__" + key }}));
    _listeners(endpoints[pointID + "__" + key]);
}

function addBlock(block, styleClass, duplicateId) {
    if(duplicateId === undefined) duplicateId = 0;

    var params = {
        module: 'Workflow2',
        action: 'BlockAdd',
        parent: 'Settings',
        dataType: 'json',
        workflow:workflow_id,
        duplicateId: duplicateId,
        blockid:block,
        left:350,
        top:80 + jQuery(window).scrollTop()
    };

    RedooAjax('Workflow2').post(params).then(function(response) {
        if(response == false) {
            return;
        }
        if(typeof response == 'string' && response.indexOf('Invalid') != -1) {
            alert('Your Security Token was expired. The site needs to be reloaded at first.');
            window.location.reload();
            return;
        }

        var html = response.html;

        jQuery("#WFTaskContainer").append(html);

        jQuery("#block__" + response.blockID).fadeIn("fast");
        jQuery('.WorkflowTypeContainer[data-type="'+block+'"]').effect('transfer', { to: jQuery("#block__" + response.blockID), className: "ui-effects-transfer" });

        initBlockEvents(response.blockID, response.outputPoints, response.personPoints);

        /*jQuery("#block__" + response.blockID).bind( "dblclick", onDblClickBlock);

         jQuery("#block__" + response.blockID + ' .colorLayer').bind( "dblclick", function(event) { jQuery(event.target).parent().trigger("dblclick"); });

         initInputPoint("block__" + response.blockID);
         jQuery.each(response.outputPoints, function(index, value) {
         initOutputPoint("block__" + response.blockID, value[0],value[1],typeof value[2] != "undefined"?value[2]:"", jQuery(response.outputPoints).length, index);
         });
         jQuery.each(response.personPoints, function(index, value) {
         initPersonInputPoint("block__" + response.blockID,value[0],value[1], jQuery(response.personPoints).length, index);
         });

         initDragBlock('.wfBlock#block__' + response.blockID);*/
        /*
         var styleClass = response.styleClass;
         var element_id = response.element_id;
         var topPos = response.topPos;
         var leftPos = response.leftPos;
         var styleExtra = response.styleExtra;
         var typeText = response.typeText;

         var blockText = (response["blockText"].length > 2 ?'<br><span style="font-weight:bold;">' + response["blockText"] + '</span>':'');
         var html = '<div class="context-wfBlock wfBlock ' + styleClass + '" id="' + element_id+ '" style="display:none;top:' + topPos + 'px;left:' + leftPos + 'px;' + styleExtra + '"><span class="blockDescription">' + typeText + blockText + '</span><div data-color="" class="colorLayer">&nbsp;</div><img class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';

         jQuery("#workflowDesignContainer").append(html);



         endpoints[element_id + "__input"] = jsPlumb.addEndpoint(element_id, inputPointOptions, jQuery.extend(getInput('modules/Workflow2/icons/input.png', "flowChart", false, true, false), {parameters:{ "in":element_id + '__input' }}));

         for(var i = 0; i < response.personPoints.length; i++) {
         var pointKey = response.personPoints[i][0];
         endpoints[element_id + "__" + pointKey] = jsPlumb.addEndpoint(element_id, { anchor:topAnchor[response.personPoints.length][i], maxConnections:maxConnections, overlays:getOverlay(response.personPoints[i][1], 'personLabel')  }, jQuery.extend(getInput('modules/Workflow2/icons/peopleInput.png', "person", false, true, true), {parameters:{ "in":element_id + '__' + pointKey }}));
         _listeners(endpoints[element_id + "__" + pointKey]);
         }
         for(var i = 0; i < response.outputPoints.length; i++) {
         var pointKey = response.outputPoints[i][0];
         endpoints[element_id + "__" + pointKey] = jsPlumb.addEndpoint(element_id, { anchor:rightAnchor[response.outputPoints.length][i], maxConnections:maxConnections, overlays:getOverlay(response.outputPoints[i][1]) }, jQuery.extend(getInput('modules/Workflow2/icons/output.png', "flowChart", true, false, false), {parameters:{ out:element_id + '__' + pointKey }}));

         _listeners(endpoints[element_id + "__" + pointKey]);
         }

         */
    }).fail(function(response) {
        if(typeof response == 'string' && response == 'parsererror') {
            alert('Your Security Token was expired. The site needs to be reloaded at first.');
            window.location.reload();
            return;
        }
    }) ;
}

function initBlockEvents(blockID, outputPoints, personPoints) {
    jQuery("#block__" + blockID).bind( "dblclick", onDblClickBlock);

    jQuery("#block__" + blockID + ' .colorLayer').bind( "dblclick", function(event) { jQuery(event.target).parent().trigger("dblclick"); });

    initInputPoint("block__" + blockID);
    jQuery.each(outputPoints, function(index, value) {
        initOutputPoint("block__" + blockID, value[0], value[1],typeof value[2] != "undefined"?value[2]:"", jQuery(outputPoints).length, index);
    });
    jQuery.each(personPoints, function(index, value) {
        initPersonInputPoint("block__" + blockID,value[0], value[1], jQuery(personPoints).length, index);
    });

    initDragBlock('.wfBlock#block__' + blockID);
}

function getOverlay(label, cls) {
    if(cls === undefined) cls = "";

    return [
        [ "Label", { cssClass:"labelClass noselect" + cls, label:label, id:"lbl" } ]
    ];
}
function refreshWorkflowList() {
    var params = {
        module: 'Workflow2',
        view: 'Index',
        parent: 'Settings'
    };
    RedooAjax('Workflow2').post(params).then(function(data) {
        jQuery(jQuery(".contentsDiv")[0]).html(data);
    });
}



function setAllPermissionsTo(className, value) {
    jQuery('.' + className + ' select').select2('val', value);
}

function toggleSidebar(moduleName, ele) {
    RedooUtils('Workflow2').blockUI({
        'message' : app.vtranslate('LBL_MANAGE_SIDEBARTOOGLE') + '...'
    });

    jQuery.post('index.php', {module:'Workflow2', parent: 'Settings', action:'SidebarToggle', 'workflowModule': moduleName}, function(response) {
        RedooUtils('Workflow2').unblockUI();
        jQuery('#' + ele).html(response);
    });
}


jQuery(function() {
    jQuery('.contentsDiv').addClass('noselect');
});

/**
 * @target "../Workflow2.js";
 * @depend "ElementSelection.jst";
 * @depend "Utils.jst";
 */
;var ElementSelection = {
    container:null,
    selectionBoxPosition:{},
    parentOffset:{},
    boxDrawn:false,
    removeSelection:true,
    elements:true,
    lastDragElements:[],
    mustDrawBox:false,
    lastSelected:null,
    initEvent:function(container, elements) {
        ElementSelection.container = jQuery(container);
        ElementSelection.container.css('position', 'relative');
        ElementSelection.parentOffset = ElementSelection.container.offset(); //position();

        ElementSelection.elements = jQuery(elements, container);

        jQuery(elements, container).on('click', ElementSelection.onClick);
        jQuery(elements, container).on('contextmenu', ElementSelection.onContextMenu);

        jQuery(container).on('click', function(e) {
            ElementSelection.container.off('mousemove', ElementSelection.drawBox);

            if(ElementSelection.startDragBoxTimeout !== null) {
                window.clearTimeout(ElementSelection.startDragBoxTimeout);
            }
            ElementSelection.startDragBoxTimeout = null;
        });

        jQuery(container).on('mousedown', function(e) {
            if(jQuery(e.target).closest('.wfBlock').length > 0) {
                return;
            }

            ElementSelection.selectionBoxPosition.left = e.pageX - ElementSelection.parentOffset.left;
            ElementSelection.selectionBoxPosition.width = 0;
            ElementSelection.selectionBoxPosition.top = e.pageY - ElementSelection.parentOffset.top;
            ElementSelection.selectionBoxPosition.height = 0;

            ElementSelection.container.on('mousemove', ElementSelection.drawBox);

            if(e.ctrlKey == false) {
                ElementSelection.clearSelection();
            }
        });

        jQuery(ElementSelection.container).on('block:dragstop', function(e, element) {
            ElementSelection.container.css('cursor', 'default');
            ElementSelection.stopDragging(element);
        });
    },
    stopDrawBox:function(e) {
        ElementSelection.container.off('mouseup', ElementSelection.stopDrawBox);
        ElementSelection.container.off('mousemove', ElementSelection.drawBox);

        if(ElementSelection.boxDrawn == false) return;

        ElementSelection.finishDrawBox(e);
    },
    finishDrawBox:function(e) {
        ElementSelection.boxDrawn = false;

        jQuery('.selectionBox', ElementSelection.container).remove();

        jQuery('.insideSelectionBox').each(function(index, ele) {
            ElementSelection.select(ele, true);
        });
        jQuery('.insideSelectionBox').removeClass('insideSelectionBox');
    },
    clearSelection:function() {
        jQuery('.ele-selection .selection-border', ElementSelection.container).remove();
        jQuery('.ele-selection', ElementSelection.container).removeClass('ele-selection');
    },
    savePositionsAfterDragging:function() {
        jQuery('.ele-selection', ElementSelection.container).each(function(index, ele) {
            var position = jQuery(ele).position();
            var params = {
                module: 'Workflow2',
                action: 'BlockMove',
                parent: 'Settings',
                workflow:workflow_id,
                blockid:jQuery(ele).attr("id"),
                left:position.left,
                top:position.top
            };

            RedooAjax('Workflow2').post('index.php', params);
            //AppConnector.request(params);
        });
    },
    drawBox:function(e) {
        var mouseX = e.pageX - ElementSelection.parentOffset.left;
        var mouseY = e.pageY - ElementSelection.parentOffset.top;

        var setValues = {'left': ElementSelection.selectionBoxPosition.left, 'top': ElementSelection.selectionBoxPosition.top, 'height':ElementSelection.selectionBoxPosition.height, 'width':ElementSelection.selectionBoxPosition.width};

        if(mouseX < ElementSelection.selectionBoxPosition.left) {
            setValues.width = ElementSelection.selectionBoxPosition.left - mouseX - 5;
            setValues.left = mouseX;
        } else {
            setValues.width = mouseX - ElementSelection.selectionBoxPosition.left - 5;
        }

        if(mouseY < ElementSelection.selectionBoxPosition.top) {
            setValues.height = ElementSelection.selectionBoxPosition.top - mouseY - 5;
            setValues.top = mouseY;
        } else {
            setValues.height = mouseY - ElementSelection.selectionBoxPosition.top - 5;
        }

        if (ElementSelection.boxDrawn == false) {
            if (setValues.width > 5 || setValues.height > 5) {
                ElementSelection.container.css('cursor', 'pointer');

                ElementSelection.boxDrawn = true;
                ElementSelection.container.append('<div class="selectionBox" style="left:' + ElementSelection.selectionBoxPosition.left + 'px;top:' + ElementSelection.selectionBoxPosition.top + 'px;"></div>');

                ElementSelection.container.on('mouseup', ElementSelection.stopDrawBox);
            } else {
                return;
            }
        }

        jQuery('.selectionBox', ElementSelection.container).css({
            'width': setValues.width + 'px',
            'height': setValues.height + 'px',
            'left': setValues.left + 'px',
            'top': setValues.top + 'px'
        });

        var boxPosition = jQuery('.selectionBox', ElementSelection.container).position();

        var boxSize = {'width': boxPosition.left + jQuery('.selectionBox', ElementSelection.container).width(), 'height': boxPosition.top + jQuery('.selectionBox', ElementSelection.container).height() };

        var target = ElementSelection.elements.filter(function() {
            var position = jQuery(this).position();

            if(position.left > boxPosition.left && position.left < boxSize.width && position.top > boxPosition.top && position.top < boxSize.height) {
                return true;
            }
        });

        jQuery('.insideSelectionBox').removeClass('insideSelectionBox');
        target.addClass('insideSelectionBox');
    },
    onContextMenu:function(e) {
        if(typeof e.ctrlKey != 'undefined') {
            if(jQuery(e.currentTarget).hasClass('ele-selection')) {
                return;
            } else {
                ElementSelection.onClick(e);
            }
        }
    },
    onClick:function(e) {
        if(ElementSelection.removeSelection == false) {
            ElementSelection.removeSelection = true;
            return;
        }

        if(e.shiftKey == true && ElementSelection.lastSelected !== null) {
            var path = ElementSelection.findPath(ElementSelection.lastSelected, e.currentTarget);
            if(e.ctrlKey == false) {
                ElementSelection.clearSelection();
            }

            jQuery.each(path, function(index, ele) {
                ElementSelection.select(jQuery('#' + ele), true);
            });
            return;
        }

        if(e.ctrlKey == true) {
            if(ElementSelection.selected(e.currentTarget)) {
                ElementSelection.unselect(e.currentTarget);
            } else {
                ElementSelection.select(e.currentTarget, true);
            }

        } else {
            ElementSelection.select(e.currentTarget);
        }
        ElementSelection.lastSelected = e.currentTarget;
    },
    stack:[],
    findPath:function(fromEle, toEle) {
        var fromId = jQuery(fromEle).attr('id');
        var toId = jQuery(toEle).attr('id');

        ElementSelection.stack = [];
        ElementSelection.getSiblings(fromId, toId);
        var path = ElementSelection.stack;
        if(path.length == 0) {
            ElementSelection.stack = [];
            ElementSelection.getSiblings(toId, fromId);
            path = ElementSelection.stack;
        }
        if(path.length == 0) {
            return [];
        }

        return path;
    },
    getSiblings:function(id, needId) {
        var found = false;

        if(typeof endpoints[id + '__input'] == 'undefined') {
            return false;
        }

        ElementSelection.stack.push(id);

        jQuery.each(endpoints[id + '__input'].connections, function(index, connection) {
            if(needId == connection.sourceId) {
                ElementSelection.stack.push(connection.sourceId);
                found = true;
                return false;
            } else {
                found = ElementSelection.getSiblings(connection.sourceId, needId);

                if(found == true) {
                    return false;
                }
            }
        });

        if(found === false) {
            ElementSelection.stack.pop();
        }

        return found;
    },
    selected:function(ele) {
        if(jQuery(ele).hasClass('ele-selection')) {
            return true;
        }
        return false;
    },
    unselect:function(ele) {
        jQuery(ele, ElementSelection.container).removeClass('ele-selection');
        jQuery('.selection-border', ele).remove();
    },
    select:function(ele, append) {
        if(typeof append == 'undefined') append = false;
        if(append == false) {
            ElementSelection.clearSelection();
        }

        var height = jQuery('.blockDescription', ele).height();
        jQuery(ele, ElementSelection.container).addClass('ele-selection');
        jQuery(ele, ElementSelection.container).append('<div style="height:' + (height + 60) + 'px;" class="selection-border"></div>');
    },
    doDragging:function(container, x, y) {
        var differenceX = ElementSelection.dragstartX - x;
        var differenceY = ElementSelection.dragstartY - y;

        ElementSelection.dragElements.each(function(index, ele) {
            var leftPos = (Number(jQuery(ele).data('posx')) - differenceX);
            var topPos = (Number(jQuery(ele).data('posy')) - differenceY);
            jQuery(ele).css({'left': leftPos + 'px', 'top': topPos + 'px'});

            jsPlumbInstance.repaint(ele, {left:leftPos,top:topPos});
        });
    },
    stopDragging:function(startElement) {
        var position = jQuery(startElement).position();

        if(Math.abs(ElementSelection.dragstartX - position.left) > 0 || Math.abs(ElementSelection.dragstartY - position.top > 0)) {
            ElementSelection.removeSelection = false;
        }

        startElement.on('click', ElementSelection.onClick);

        ElementSelection.lastDragElements = jQuery('.drag-elements', ElementSelection.container);

        jQuery('.drag-elements', ElementSelection.container).removeClass('drag-elements');

        ElementSelection.dragElements = [];
    },
    startDragging:function(container, startElement, event) {
        var position = jQuery(startElement).position();
        ElementSelection.dragstartX = position.left;
        ElementSelection.dragstartY = position.top;

        if(!jQuery(startElement).hasClass('ele-selection')) {
            if (event.ctrlKey == true) {
                ElementSelection.select(startElement, true);
            } else {
                ElementSelection.select(startElement, false);
            }
        }


        jQuery(startElement, container).off('click', ElementSelection.onClick);

        /*if(jQuery('.ele-selection', container).length == 0) {
         ElementSelection.select(startElement);
         }*/

        jQuery('.ele-selection', container).addClass('drag-elements');

        if(jQuery('.ele-selection', container).length > 1) {
            jQuery(startElement).addClass('drag-start').removeClass('drag-elements');
        }

        ElementSelection.dragElements = jQuery('.drag-elements', container);

        ElementSelection.dragElements.each(function(index, ele) {
            var position = jQuery(ele).position();

            jQuery(ele).data('posx', position.left);
            jQuery(ele).data('posy', position.top);
        })
    },
    getSelectedBlocks:function() {
        return jQuery('.wfBlock.ele-selection', ElementSelection.container);
    }
};
;/**
 * Created by Stefan on 22.12.2016.
 */
var WFBackendUtils = {
    fillSelectWithPicklistvalues:function(srcPicklist, targetSelect, currentValue) {
        jQuery(targetSelect).parent().hide();
        jQuery(srcPicklist).on('change FieldsLoaded', function(e) {
            var value = $(e.currentTarget).val();
            RedooAjax('Workflow2').postAction('GetPicklistValues', { picklist:value,'dataType':'json' }).then(function(response) {
                var html = '';
                jQuery.each(response, function(key, value) {
                    console.log(key, currentValue, $.inArray(key, currentValue));
                    var selected = '';
                    if(typeof currentValue == 'object' && $.inArray(key, currentValue) != -1) {
                        selected = 'selected="selected"';
                    }
                    if(typeof currentValue == 'string' && key == currentValue) {
                        selected = 'selected="selected"';
                    }

                    html += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                });
                jQuery(targetSelect).html(html).select2('val', currentValue);
                jQuery(targetSelect).parent().show();

            });
        });

        if(!jQuery(srcPicklist).hasClass('AsyncLoaded')) {
            RedooAjax('Workflow2').postAction('GetPicklistValues', { picklist:jQuery(srcPicklist).val(),'dataType':'json' }).then(function(response) {
                var html = '';
                jQuery.each(response, function(key, value) {
                    console.log(key, currentValue, $.inArray(key, currentValue));
                    var selected = '';
                    if(typeof currentValue == 'object' && $.inArray(key, currentValue) != -1) {
                        selected = 'selected="selected"';
                    }
                    if(typeof currentValue == 'string' && key == currentValue) {
                        selected = 'selected="selected"';
                    }

                    html += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
                });
                jQuery(targetSelect).parent().show();
            });
        }

    }
};