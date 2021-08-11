var currentCol = 0;
function addField(field, label, width, value) {
    var newColNumber = currentCol + 1;
    if(typeof field == "undefined") {
        field = '';
    }
    if(typeof label == "undefined") {
        label = "";
    }
    if(typeof width == "undefined") {
        width = "150px";
    }
    if(typeof value == "undefined") {
        value = "$value";
    }

    var HTML = jQuery('#staticFieldsContainer').html();
    HTML = HTML.replace(/##SETID##/g, currentCol);
    HTML = jQuery(HTML);

    HTML.find(":disabled").removeAttr("disabled");

    jQuery("#fieldlist").append(HTML);

    jQuery("#staticfields_" + currentCol + "_field").val(field);
    jQuery("#staticfields_" + currentCol + "_label").val(label);
    jQuery("#staticfields_" + currentCol + "_width").val(width);
    jQuery("#staticfields_" + currentCol + "_value").val(value);

    currentCol++;

    jQuery('.fieldSelect').off('change', checkField);
    jQuery('.fieldSelect').on('change', checkField);

    jQuery('#fieldlist .MakeSelect2').removeClass('MakeSelect2').select2();

    return newColNumber;
}

function initRecordListFields(fields) {

    jQuery.each(fields, function(index, value) {
        addField(value.field, value.label, value.width, value.value);
    });

    jQuery('.fieldSelect').on('change', checkField);
}

function checkField() {
    console.log(jQuery(this).val());
    if(jQuery(this).val() == ';;;delete;;;') {
        jQuery(this).closest('.rowConfig').remove();
    }
}