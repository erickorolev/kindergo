var currentCol = 0;
function addCol() {
    var newColNumber = currentCol + 1;

    var html = "<div class='overflow:hidden;' style='height:30px;line-height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
        html += "<span style='display:block;float:left;width:100px;'>Column " + newColNumber+"</span>";
        html += "<span style='display:block;float:left;width:40px;'>=&gt;"+"</span>";
        html += "$env[csv][<input type='text' id='colVariable_" + newColNumber+"' name='task[cols][]' value='col" + newColNumber + "'>]";
    html += "</div>";

    jQuery("#rows").append(html);

    cols.push("col" + newColNumber);

    currentCol++;

    return newColNumber;
}

function initCols() {
    jQuery.each(cols, function(index, value) {
        var colNumber = addCol();
        jQuery("#colVariable_" + colNumber).val(value);
    });
}
initCols();