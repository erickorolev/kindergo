var currentCol = 0;
function addCol(oldKey, oldValue) {
    var newColNumber = currentCol + 1;

    if(typeof oldKey == "undefined") {
        oldKey = "Key-" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }

    var html = "<div class='overflow:hidden;' style='display:flex;clear:both;height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
        html += "<span style='display:block;float:left;'><input class='defaultTextfield'  type='text' id='colVariable_" + newColNumber+"' name='task[cols][key][]' value='" + oldKey + "'></span>";
        html += "<span style='display:block;float:left;width:40px;text-align:center;'>=&gt;"+"</span>";
        html += createTemplateTextfield("task[cols][value][]", "cols_value_" + newColNumber, oldValue, {module: workflowModuleName, refFields: true});
    html += "</div>";

    jQuery("#rows").append(html);

    currentCol++;

    return newColNumber;
}

function initCols() {
    jQuery.each(cols.key, function(index, value) {
        var colNumber = addCol(cols.key[index], cols.value[index]);
    });
}
jQuery(function() {
    initCols();
    InitAutocompleteText();
});
