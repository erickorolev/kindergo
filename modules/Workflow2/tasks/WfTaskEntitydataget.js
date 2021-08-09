var currentCol = 0;
function addCol(oldKey, oldValue) {
    var newColNumber = currentCol + 1;

    if(typeof oldKey == "undefined") {
        oldKey = "Key-" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }

    var html = "<div class='overflow:hidden;' style='height:35px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
        html += "<span style='display:block;float:left;'><input type='text' id='colVariable_" + newColNumber+"' class='defaultTextfield' name='task[cols][key][]' value='" + oldKey + "'></span>";
        html += "<span style='display:block;float:left;width:40px;'>=&gt;"+"</span>";
    html += "<span style='display:block;float:left;'>$env[<input type='text' id='colVariable_" + newColNumber+"' class='defaultTextfield' name='task[cols][value][]' value='" + oldValue + "'>]</span>";
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