var currentCol = 0;
function addCol(oldKey, oldValue) {
    var newColNumber = currentCol + 1;

    if(typeof oldKey == "undefined") {
        oldKey = "Param" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }

    var html = "<div class='overflow:hidden;' style='display:flex;clear:both;height:30px;line-height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
        html += "<span style='display:block;float:left;'><input type='text' class='defaultTextfield' id='colVariable_" + newColNumber+"' name='task[cols][key][]' value='" + oldKey + "'></span>";
        html += "<span style='display:block;float:left;width:40px;text-align:center;'>=&gt;"+"</span>";
        html += createTemplateTextfield("task[cols][value][]", "cols_value_" + newColNumber, oldValue, {module: workflowModuleName, refFields: true});
    html += "</div>";

    jQuery("#rows").append(html);

    currentCol++;

    return newColNumber;
}
var currentHeaderCol = 0;
function addHeaderCol(oldKey, oldValue) {
    var newColNumber = currentHeaderCol + 1;

    if(typeof oldKey == "undefined") {
        oldKey = "Param" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }

    var html = "<div class='overflow:hidden;' style='display:flex;clear:both;height:30px;line-height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
        html += "<span style='display:block;float:left;'><input  class='defaultTextfield'type='text' id='colVariable_" + newColNumber+"' name='task[header][key][]' value='" + oldKey + "'></span>";
        html += "<span style='display:block;float:left;width:40px;text-align:center;'>=&gt;"+"</span>";
        html += createTemplateTextfield("task[header][value][]", "header_value_" + newColNumber, oldValue, {module: workflowModuleName, refFields: true});
    html += "</div>";

    jQuery("#header_rows").append(html);

    currentHeaderCol++;

    return newColNumber;
}

function initCols() {
    if(typeof cols.key != 'undefined') {
        jQuery.each(cols.key, function(index, value) {
            var colNumber = addCol(cols.key[index], cols.value[index]);
        });
    }
    if(typeof header.key != 'undefined') {
        jQuery.each(header.key, function(index, value) {
            var colNumber = addHeaderCol(header.key[index], header.value[index]);
        });
    }
}
jQuery(function() {
    initCols();
    InitAutocompleteText();
});
