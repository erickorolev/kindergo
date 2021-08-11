var currentCol = 0;
var setFieldValueSelectvalue = false;

function addField(value) {
    var newColNumber = currentCol + 1;
    if(typeof value == "undefined") {
        value = {
            'mode' : 'field',
            'value' : '',
            'label' : '',
        };
    }

    var HTML = jQuery('#staticFieldsContainer').html();
    HTML = HTML.replace(/##SETID##/g, currentCol);
    HTML = jQuery(HTML);

    HTML.find(":disabled").removeAttr("disabled");

    jQuery("#fieldlist").append(HTML).removeClass('hide');

    jQuery("#staticfields_" + currentCol + "_value").val(value.value);
    jQuery("#staticfields_" + currentCol + "_mode").val(value.mode);

    jQuery("#staticfields_" + currentCol + "_mode").on('change', onChangeField);

    jQuery("#staticfields_" + currentCol + "_label").val(value.label);

    jQuery("#staticfields_" + currentCol + "_mode").trigger('change');

    jQuery('#fieldlist .MakeSelect2').removeClass('MakeSelect2').select2();
    currentCol++;

    return newColNumber;
}

function onChangeField(event) {
    parts = jQuery(this).attr("id").split("_");
    rowID = parts[1];

    var inputHTML = getValueInput(rowID);
    jQuery("#value_" + rowID + "_container").html(inputHTML);

    if(setFieldValueSelectvalue !== false)  {
        jQuery("select#staticfields_" + rowID + "_value").val(setFieldValueSelectvalue);
        setFieldValueSelectvalue = false;
    }

    jQuery("select#setter_" + rowID + "_value").select2();

    jQuery("#setter_" + rowID + "_field_chosen").attr("alt", jQuery("#setter_" + rowID + "_field").val());
    jQuery("#setter_" + rowID + "_field_chosen").attr("title", jQuery("#setter_" + rowID + "_field").val());
}

function addAllFields() {
    var stop = false;

    var already = {};
    jQuery('select.selectFields').each(function(index, value) {
        already[value.value] = true;
    });

    jQuery("#fieldlist").hide();

    jQuery.each(fromFields, function(index, blockFields) {
        jQuery.each(blockFields, function(indexField, field) {
            if(field.name.indexOf('(') === 0) {
                return false;
            }

            if(typeof already['$' + field.name] == 'undefined') {
                addField({
                    'mode' : 'field',
                    'value' : '$' + field.name,
                    'label' : field.label
                });
            }
        });

        if(stop == true) {
            return false;
        }
    });

    jQuery("#fieldlist").show();

}
function delField(id) {
    jQuery('#setterRow_' + id).remove();
}

function initRecordListFields(fields) {

    jQuery.each(fields, function(index, value) {
        addField(value);
    });

    jQuery("#fieldlist").show();
}

function getValueInput(rowID, current_value) {
    var mode = jQuery("#staticfields_" + rowID + "_mode").val();
        // function getConditionInput(recordId, recordName, recordIndex, field, value) {

    var currentValue;
    if(current_value === undefined && jQuery("#staticfields_" + rowID + "_value") !== undefined) {
        currentValue = jQuery("#staticfields_" + rowID + "_value").val();
    } else {
        currentValue = current_value;
    }

    var fieldId = "staticfields_" + rowID + "_value";
    var fieldName = "task["+StaticFieldsField+"][" + rowID + "][value]";

    if(mode == "function") {
        var html = "<textarea class='textfield customFunction' style='width:300px;' name='" + fieldName + "' id='" + fieldId + "'>" + currentValue + "</textarea>";
        html += "<img src='modules/Workflow2/icons/templatefieldPHP.png' style='margin-bottom:-2px;cursor:pointer;' onclick=\"insertTemplateField('" + fieldId + "', '[source]->[module]->[destination]', true)\">";
        return html;
    } else if(mode == "column") {
        var html = '<input type="text" style="width:300px;" min=0 step="1" name="' + fieldName + '" id="' + fieldId + '" value="' + currentValue + '" />';
        return html;
    } else if(mode == "field") {
        setFieldValueSelectvalue = currentValue;

        var html = jQuery('#fromFieldsFieldValues').html();
        return html.replace(/##FIELDNAME##/g, fieldName).replace(/##FIELDID##/g, fieldId);
    }

    return createTemplateTextfield(fieldName, fieldId, currentValue, {module: target_module_name, refFields: true});

}