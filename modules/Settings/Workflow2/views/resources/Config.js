var isReadonly = false;

jQuery('#workflowObjectsContainer').on('block:dragstop', function(event, ele, ui) {
    ElementSelection.savePositionsAfterDragging();
});

jQuery('#workflowObjectsContainer').on('block:dblclick', function(event, taskId) {
    window.open("index.php?module=Workflow2&parent=Settings&view=TaskConfig&taskid=" + taskId, "Config", "width=1154,height=750").focus();
});

jQuery('#workflowObjectsContainer').on('object:dblclick', function(event, module, taskId) {

    jQuery.post("index.php?module=Workflow2&action=RecordList&parent=Settings" , { module_name:module, objectID: taskId }, function(response) {

        app.showModalWindow(response, function() {
            jQuery('#modalSubmitButton').on('click', function() {
                app.hideModalWindow();
                saveObjectRecord(jQuery('#workflowObjectId').val());
                jQuery( this ).dialog( "close" );
                return false;
            });
        });
        return;
    });

});

var wfMousePos = {x:0, y:0};
jQuery(function() {
    jQuery('#workflowDesignContainer').mousemove(function(e) {
        wfMousePos.x = e.pageX;
        wfMousePos.y = e.pageY;
    });
});

function initDragBlock(ele) {
    ElementSelection.initEvent('.contentsDiv', '.wfBlock');

    jsPlumbInstance.draggable(jsPlumb.getSelector(ele), {
        start:function(params) {
            jQuery('span.blockDescription', params.el).hide();
            jQuery('img.settingsIcon', params.el).hide();

            ElementSelection.startDragging('#workflowDesignContainer', params.el, params.e);
            //jsPlumbInstance.setSuspendDrawing(true);
        },
        drag:function(params) {
            ElementSelection.doDragging('#workflowDesignContainer', params.pos[0], params.pos[1]);
            if(params.pos[1] > currentWorkSpaceHeight - 150) {
                jQuery('#mainWfContainer').css('height', (currentWorkSpaceHeight + 100) + 'px');
                currentWorkSpaceHeight = currentWorkSpaceHeight + 100;
            }
            if(params.pos[0] > currentWorkSpaceWidth - 200) {
                jQuery('#mainWfContainer').css('width', (currentWorkSpaceWidth + 250) + 'px');
                currentWorkSpaceWidth = currentWorkSpaceWidth + 100;
            }
        },
        stop:onDragStopBlock
    });
}

jQuery('#workflowObjectsContainer').on('designer:init', function() {
    if(jQuery(".wfBlock").length > 0) {
        initDragBlock('.wfBlock');
    }

    jsPlumbInstance.bind("click", function(c, origEvent) {
        jsPlumbInstance.deleteConnection(c);
    });

    // jsPlumb.bind("connectionDetached", function(c, origEvent) {
    //     console.log('detach');
    // });
    /*
    jsPlumb.bind("connectionDetached", function(c, origEvent) {
        // console.log('del');
        var parameters = c.connection.getParameters();
// console.log(c, parameters);
        var params = {
            module: 'Workflow2',
            action: 'ConnectionDel',
            parent: 'Settings',
            workflow: workflow_id,
            destination:c.targetId + '__input',
            source:parameters["out"]
        };

        RedooAjax('Workflow2').post('index.php', params);
        //AppConnector.request(params);
    });
*/
    jsPlumbInstance.bind("connection", function(params) {
        console.log('newCOnnection');
        if(jsPlumbInit === true) return;
        var parameters = params.connection.getParameters();

        var params = {
            module: 'Workflow2',
            action: 'ConnectionAdd',
            parent: 'Settings',
            workflow: workflow_id,
            destination:parameters["in"],
            source:parameters["out"]
        };

        RedooAjax('Workflow2').post('index.php', params);
        //AppConnector.request(params);
    });

    jsPlumbInstance.bind("connectionDetached", function(params) {
        var parameters = params.connection.getParameters();

        var params = {
            module: 'Workflow2',
            action: 'ConnectionDel',
            parent: 'Settings',
            workflow: workflow_id,
            destination:parameters["in"],
            source:parameters["out"]
        };

        RedooAjax('Workflow2').post('index.php', params);
        //AppConnector.request(params);
    });

    jQuery(".workflowDesignerObject_text").bind("dblclick", editTextObject);
    jQuery(".workflowDesignerObject_text").bind("contextmenu", removeObject);
});

jsPlumb.bind("ready", function() {
    jQuery(".colorLayer.colored").each(function(index, value) {
         var ele = jQuery(value);
         var color = ele.data("color").substr(1);

         if(color != '' && color != 'FFFFFF' && lastColors.indexOf(color) == -1) {
             lastColors.push(color);
         }

         if(lastColors.length == 6) {
             return false;
         }
     });

     jQuery.contextMenu({
         selector: '.context-wfBlock',
         callback: function(key, options) {
             switch(key) {
                 case "config":
                     onDblClickBlock({target:{id:options.$trigger.get(0).id}});
                     break;
                 case "selectall":
                     var type = jQuery(options.$trigger.get(0)).data('type');
                     ElementSelection.clearSelection();

                     jQuery('.wfBlock[data-type="' + type + '"]').each(function(index, ele) {
                         ElementSelection.select(ele, true);
                    });
                     break;
                 case "duplicate":
                     jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
                         if(jQuery(ele).data('type') != 'start') {
                             addBlock(0, 0, jQuery(ele).attr('id').replace("block__", ""));
                         }
                     });
                     break;
                 case 'export':
                     var blockIds = [];
                     jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
                         if(jQuery(ele).data('type') != 'start') {
                             blockIds.push(jQuery(ele).attr('id').replace("block__", ""));
                         }
                     });

                     jQuery.post('index.php', {module:'Workflow2',parent:'Settings','view':'BlockTextExportPopup', workflow_id:workflow_id, block_ids:blockIds}, function(response) {
                         app.showModalWindow(response);
                     });
                     break;
                 case 'import':
                     var position = wfMousePos;
                     var elePosition = jQuery('#workflowDesignContainer').offset();
                     position.y -= elePosition.top;
                     position.x -= elePosition.left;
                     jQuery.getScript('modules/Workflow2/views/resources/js/jquery.form.min.js');
                     jQuery.post('index.php', {module:'Workflow2',parent:'Settings','view':'BlockTextImportPopup', workflow_id:workflow_id, position:wfMousePos}, function(response) {
                         app.showModalWindow(response, function() {
                             jQuery('#blockImportPopupForm').on('submit', function(e) {
                                 e.preventDefault();
                                 e.stopPropagation();

                                 var options = {
                                     success:function(response) {
                                         if(typeof response.success != 'undefined' && response.success == false) {
                                             alert('Error: ' + response.error.code);
                                             return;
                                         }
                                         jQuery.each(response.blocks, function(index, blockData) {

                                             var html = blockData.html;

                                             jQuery("#WFTaskContainer").append(html);

                                             jQuery("#block__" + blockData.blockID).fadeIn("fast");
                                             jQuery('.WorkflowTypeContainer[data-type="'+blockData.type+'"]').effect('transfer', { to: jQuery("#block__" + blockData.blockID), className: "ui-effects-transfer" });

                                             initBlockEvents(blockData.blockID, blockData.outputPoints, blockData.personPoints);
                                         });
                                         jQuery.each(response.connections, function(index, connection) {
                                             connectEndpoints('block__' + connection.source_id + "__" + connection.source_key,'block__' +  connection.destination_id + "__" + connection.destination_key);
                                         });

                                         app.hideModalWindow();
                                   },
                                     'dataType':'json'
                                 };

                                 jQuery('#blockImportPopupForm').ajaxSubmit(options);
                             });
                         });
                     });
                     break;
                 case "copy":
                     var blockIds = [];
                     jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
                         if(jQuery(ele).data('type') != 'start') {
                             blockIds.push(jQuery(ele).attr('id').replace("block__", ""));
                         }
                     });

                     copyBlocks(blockIds);
                     break;
                 case 'paste':
                     pasteBlocks();
                     break;
                 case "removecolor":
                     jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
                         setColorLayer(jQuery(ele).attr('id'), "FFFFFF");
                     });
                         break;
                 case "color":
                     currentColorPicker = [options.$trigger.get(0).id];
                     var position = options.$menu.position();

                     jQuery("#colorPickerElement").css({"position":"absolute", "left": "300px", "top":"200px"}).unbind("change").unbind("removepicker").bind("change", function() {
                      setColorLayer(currentColorPicker[0], this.value);

                     }).bind("removepicker", function() {
                         if(this.value != 'FFFFFF' && lastColors.indexOf(this.value) == -1) {
                             if(lastColors.length >= 6) {
                                 lastColors.shift();
                             }
                             lastColors.push(this.value);
                         }
                     });

                     var myPicker = new jscolor.color(document.getElementById('colorPickerElement'), {pickerClosable:true, showlast:lastColors});
                     myPicker.fromString(jQuery("#" + options.$trigger.get(0).id + " .colorLayer").data("color").substr(1))  // now you can access API via 'myPicker' variable
                     myPicker.drawPicker2(position.left, position.top);
                     currentColorPicker[1] = myPicker;

                     break;
                 case "delete":
                     if(confirm("Realy delete selected blocks?") == false)
                         return;

                     jQuery.each(ElementSelection.getSelectedBlocks(), function(index, ele) {
                         removeBlock(jQuery(ele).attr('id'));
                     });

                     break;
             }
         },
         items: {
             "config": {name: "Config", icon: "edit"},
             "sep1A": "---------",
             "selectall": {name: app.vtranslate('select all of this type'), icon: "select"},
             "sep1A2": "---------",
             "duplicate": {name: app.vtranslate('LBL_DUPLICATE_BLOCK'), icon: "copy"},
             "copy": {name: app.vtranslate('LBL_COPY_BLOCK'), icon: "copy"},
             "paste": {name: app.vtranslate('LBL_PASTE_BLOCK'), icon: "paste", disabled:function() { return copyHash === null }},
             "export": {name: app.vtranslate('Export Blocks by Text')},
             "import": {name: app.vtranslate('Import Blocks by Text')},
             "sep1": "---------",
             "color": {name: app.vtranslate('LBL_CHANGE_BLOCKCOLOR'), icon: "color"},
             "removecolor": {name: app.vtranslate('LBL_REMOVE_BLOCKCOLOR'), icon: "colorremove"},
             "sep2": "---------",
             "delete": {name: app.vtranslate('LBL_DELETE_BLOCK'), icon: "delete"}
         }
     });
});

function removePerson(person_id) {
    jQuery.post("index.php" ,  {module:'Workflow2', parent:'Settings', action:'PersonRemove', block_id:person_id, workflow_id:workflow_id });

    jsPlumb.removeAllEndpoints(person_id);
    jQuery("#" + person_id).remove();
}

function removeBlock(block_id) {
    if(jQuery('#' + block_id).data('type') == 'start') return;

    var params = {
        module: 'Workflow2',
        action: 'BlockDel',
        parent: 'Settings',
        workflow: workflow_id,
        blockid: block_id
    };

    RedooAjax('Workflow2').post('index.php', params);
    //AppConnector.request(params);

    jsPlumbInstance.removeAllEndpoints(block_id);
    jQuery("#" + block_id).fadeOut("fast", function() {
        jQuery(this).remove();
    });
}

function pasteBlocks() {
    var position = wfMousePos;
    var elePosition = jQuery('#workflowDesignContainer').offset();
    position.y -= elePosition.top;
    position.x -= elePosition.left;

    jQuery.post('index.php', { module:'Workflow2', 'parent' : 'Settings', 'action':'BlockPaste',  workflow:workflow_id, 'hash': copyHash, position:wfMousePos}, function(response) {
        jQuery.each(response.blocks, function(index, blockData) {

            var html = blockData.html;

            jQuery("#WFTaskContainer").append(html);

            jQuery("#block__" + blockData.blockID).fadeIn("fast");
            jQuery('.WorkflowTypeContainer[data-type="'+blockData.type+'"]').effect('transfer', { to: jQuery("#block__" + blockData.blockID), className: "ui-effects-transfer" });

            initBlockEvents(blockData.blockID, blockData.outputPoints, blockData.personPoints);
        });
        jQuery.each(response.connections, function(index, connection) {
            connectEndpoints('block__' + connection.source_id + "__" + connection.source_key,'block__' +  connection.destination_id + "__" + connection.destination_key);
        });
    }, 'json');
}

function copyBlocks(blockIds) {
    jQuery.post('index.php', { module:'Workflow2', 'action':'BlockCopy', 'parent':'Settings', workflow:workflow_id, blockids:blockIds }, function(response) {
        copyHash = response;
    });
}
function addRecord(module_name) {
    jQuery.post("index.php", { module:'Workflow2', parent:'Settings', action:'PersonAdd',workflow:workflow_id, module_name:module_name }, function(response) {
        var element_id = response.element_id;
        var topPos = response.topPos;
        var leftPos = response.leftPos;

        var html = '<div class="wfBlock wfPerson" id="' + element_id + '" style="top:' + topPos + 'px;left:' + leftPos + 'px;">Not connected<img src="modules/Workflow2/icons/cross-button.png" class="removePersonIcon" onclick="removePerson(\'' + element_id + '\');"></div>';

        jQuery("#workflowDesignContainer").append(html);

        endpoints[element_id + "__person"] = jsPlumb.addEndpoint(element_id, { anchor:bottomAnchor, maxConnections:maxConnections }, jQuery.extend(getInput('modules/Workflow2/icons/peopleOutput.png', "person", true, false, true), {parameters:{ out:element_id + '__person' }}));

        jsPlumb.setDraggable("#" + element_id, true);
        jQuery("#" + element_id).bind( "dblclick", onDblClickBlock);

        jQuery("#" + element_id).bind( "dragstop", onDragStopBlock);
    }, 'json');
}
function addObject(type) {
    var html = false;
    type = type.toLowerCase();
    workflowDesignerObjectCounter = Number(workflowDesignerObjectCounter) + 1;

    jQuery.post("index.php", {module:'Workflow2', parent:'Settings', action:'ObjectAdd',type:type, workflow: workflow_id}, function(response) {
        html = response["content"];
        id = response["id"];
//        console.log(html);
        if(html !== false) {
            jQuery("#workflowObjectsContainer").append(html);
            initTextDrag("#" + id);

            jQuery("#" + id).bind("dblclick", editTextObject);
            jQuery("#" + id).bind("contextmenu", removeObject);

        }
    }, "json");
}


function removeObject(event) {
    var currentCKEditorObjectId = this.id.substr(this.id.indexOf("_") + 1);

    jQuery.post("index.php", {module:'Workflow2', parent:'Settings', action:'ObjectRemove', id: currentCKEditorObjectId});
    jQuery(this).fadeOut("fast")
    return false;
}
function editTextObject(id)  {
    var currentCKEditorObjectId = this.id.substr(this.id.indexOf("_") + 1);

    jQuery( "#workflowDesignerObject_" + currentCKEditorObjectId).draggable("destroy");
    jQuery("#workflowDesignerObject_"  + currentCKEditorObjectId).attr("contenteditable", "true");

    var editor = CKEDITOR.inline( document.getElementById( 'workflowDesignerObject_'  + currentCKEditorObjectId) , {startupFocus  :true});
    editor.on("blur", function() {
        editor.destroy();
        jQuery("#workflowDesignerObject_"  + currentCKEditorObjectId).removeAttr("contenteditable");
        initTextDrag("#workflowDesignerObject_" + currentCKEditorObjectId);
        jQuery.post("index.php", { module:'Workflow2', parent:'Settings', action:'ObjectSetText', id: currentCKEditorObjectId, text: jQuery("#workflowDesignerObject_"  + currentCKEditorObjectId).html()});
    });
}

function initTextDrag(objectSelector) {
    jQuery(objectSelector).draggable({
        stop: function(event, ui) {
            var currentCKEditorObjectId = this.id.substr(this.id.indexOf("_") + 1);

            jQuery.post("index.php", { module:'Workflow2', parent:'Settings', action:'ObjectSetPos', id: currentCKEditorObjectId, y: ui.position.top, x: ui.position.left });
        }
    });
}

function saveObjectRecord(objectID) {
    var selected = jQuery('#recordSelector').val();
    jQuery.post("index.php",  {module:'Workflow2', parent:'Settings', action:'ObjectRecordConnection', objectID:objectID, recordID: selected}, function(response) {
        jQuery('#person__' + objectID + " span").html(jQuery('#person__' + objectID + " span").html().replace(jQuery('#person__' + objectID + " span").text(), response));
    });
}

initTextDrag(".workflowDesignerObject_text");

