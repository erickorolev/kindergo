var currentCol = 0;

function addCol(oldKey, oldValue, oldUpdate, eleId, variableKey) {
    var newColNumber = jQuery('.colKey_' + variableKey).length;

    if(typeof oldKey == "undefined") {
        oldKey = "Key-" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }

    var html = "<div class='colKey_" + variableKey + "' style='display:flex;clear:both;line-height:30px;height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
    html += "<span style='display:block;float:left;'><input type='text' class='defaultTextfield' id='colVariable_" + newColNumber+"' name='task[" + variableKey + "][key]["+newColNumber+"]' value='" + oldKey + "'></span>";
    html += "<span style='display:block;float:left;width:40px;'>=&gt;"+"</span>";

    if(variableKey == 'cols')
        html += "<span style='display:block;float:right;width:200px;'>set on Update: <input type='checkbox' " + (oldUpdate == '1' ? 'checked="checked"' : '') + "name='task[" + variableKey + "][update]["+newColNumber+"]' value='1' />"+"</span>";

    html += createTemplateTextfield("task[" + variableKey + "][value]["+newColNumber+"]", variableKey + "_value_" + newColNumber, oldValue, {module: workflowModuleName, refFields: true, style:"width:300px;"});

    html += "</div>";

    jQuery("#" + eleId).append(html);

    currentCol++;

    return newColNumber;
}

function initCols() {
    jQuery.each(cols.key, function(index, value) {
        var colNumber = addCol(cols.key[index], cols.value[index], typeof cols.update != 'undefined' && typeof cols.update[index] != 'undefined' ? cols.update[index] : '0', 'rows', 'cols');
    });
    jQuery.each(colsWhere.key, function(index, value) {
        var colNumber = addCol(colsWhere.key[index], colsWhere.value[index], 0, 'rowsWhere', 'colsWhere');
    });
}

jQuery(function() {
    initCols();

    jQuery('#loadStructureBtn').on('click', loadStructure);
});

function loadStructure() {
    if(!confirm('This will replace the complete column configuration. Continue?')) return;

    jQuery('#loadStructure').val(1);
    submitConfigForm();
}