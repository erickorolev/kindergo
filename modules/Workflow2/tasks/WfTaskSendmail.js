/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

function VTEmailTask($){
	var vtinst = new VtigerWebservices("webservice.php");
	var desc = null;
	var accessibleModulesInfo = null;

	//Display an error message.
	function errorDialog(message){
		alert(message);
	}

	//This is a wrapper to handle webservice errors.
	function handleError(fn){
		return function(status, result){
			if(status){
				fn(result);
			}else{
				console.log('Failure:', result);
			}
		};
	}

	//Insert text at the cursor
	function insertAtCursor(element, value){
		//http://alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript
		if (document.selection) {
			element.focus();
			var sel = document.selection.createRange();
			sel.text = value;
			element.focus();
		}else if (element.selectionStart || element.selectionStart == '0') {
			var startPos = element.selectionStart;
			var endPos = element.selectionEnd;
			var scrollTop = element.scrollTop;
			element.value = element.value.substring(0, startPos)
				+ value
				+ element.value.substring(endPos,
				element.value.length);
			element.focus();
			element.selectionStart = startPos + value.length;
			element.selectionEnd = startPos + value.length;
			element.scrollTop = scrollTop;
		}	else {
			element.value += value;
			element.focus();
		}
	}

	//Convert user type into reference for consistency in describe objects
	//This is done inplace
	function referencify(desc){
		var fields = desc['fields'];
		for(var i=0; i<fields.length; i++){
			var field = fields[i];
			var type = field['type'];
			if(type['name']=='owner'){
				type['name']='reference';
				type['refersTo']=['Users'];
			}
		}
		return desc;
	}

	//Get an array containing the the description of a module and all modules
	//refered to by it. This is passed to callback.
	function getDescribeObjects(accessibleModules, moduleName, callback){
		vtinst.describeObject(moduleName, handleError(function(result){
			var parent = referencify(result);
			var fields = parent['fields'];

            var referenceFields = jQuery(fields).filter(function(index){
                return fields[index]['type']['name']=='reference';
            });


			var referenceFieldModules =
				jQuery.map(
                    referenceFields,
					function(e){
						return e['type']['refersTo'];
					}
				);

			function union(a, b){
			  var newfields = filter(function(e){return !contains(a, e);}, b);
			  return a.concat(newfields);
			}

			var relatedModules = jQuery.unique(referenceFieldModules);

			// Remove modules that is no longer accessible
			relatedModules = diff(accessibleModules, relatedModules);

			function executer(parameters){
				var failures = filter(function(e){return e[0]==false;}, parameters);

				if(failures.length!=0){
					var firstFailure = failures[0];
					callback(false, firstFailure[1]);
				}else{
					var moduleDescriptions = map(function(e){
						return referencify(e[1]);},
					  parameters);
					var modules = dict(map(function(e){
						  return [e['name'], e];},
						moduleDescriptions));
					callback(true, modules);
				}
			}

			var p = parallelExecuter(executer, relatedModules.length);
			$.each(relatedModules, function(i, v){
				p(function(callback){vtinst.describeObject(v, callback);});
			});
		}));
	}

	function fillSelectBox(id, modules, parentModule, filterPred){
		if(filterPred==null){
			filterPred = function(){
				return true;
			};
		}
		var parent = modules[parentModule];
		var fields = parent['fields'];

		function filteredFields(fields){
			return filter(
				function(e){
					var fieldCheck = !contains(['autogenerated', 'reference', 'owner', 'multipicklist', 'password'], e.type.name);
					var predCheck = filterPred(e);
					return fieldCheck && predCheck;
				},
				fields
			);
		}
		var parentFields = map(function(e){return[e['name'],e['label']];}, filteredFields(parent['fields']));

		var referenceFieldTypes = filter(function(e){
				return (e['type']['name']=='reference');
			},parent['fields']
		);

		var moduleFieldTypes = {};
		$.each(modules, function(k, v){
				moduleFieldTypes[k] = dict(map(function(e){return [e['name'], e['type']];},filteredFields(v['fields'])));
			}
		);

		function getFieldType(fullFieldName){
			var group = fullFieldName.match(/(\w+) : \((\w+)\) (\w+)/);
			if(group==null){
				var fieldModule = moduleName;
				var fieldName = fullFieldName;
				}else{
					var fieldModule = group[2];
				var fieldName = group[3];
			}
			return moduleFieldTypes[fieldModule][fieldName];
		}

		function fieldReferenceNames(referenceField){
			var name = referenceField['name'];
			var label = referenceField['label'];

			function forModule(moduleName){

				// If module is not accessible return no field information
				if(!contains(accessibleModulesInfo, moduleName)) return [];

				return map(function(field){
					return ['('+name+' : '+'('+moduleName+') '+field['name']+')',label+' : '+'('+modules[moduleName]['label']+') '+field['label']];
					},
					filteredFields(modules[moduleName]['fields']));
			}

			return reduceR(concat,map(forModule,referenceField['type']['refersTo']),[]);
		}

		var referenceFields = reduceR(concat,map(fieldReferenceNames,referenceFieldTypes), []);
		var fieldLabels = dict(parentFields.concat(referenceFields));
		var select = $('#'+id);
		var optionClass = id+'_option';

		jQuery.each(fieldLabels, function(k, v){
			select.append('<option class="'+optionClass+'" '+ 'value="'+k+'">' + v + '</option>');
		});
        select.chosen();
	}

	$(document).ready(function(){
        $('#btn_insert_variable').on('click',function(e) {
            insertTemplateField('templateVarContainer','([source]: ([module]) [destination])', true, true,
                {
                    callback: function(text, param) {
                        var textarea = CKEDITOR.instances.save_content;
                        textarea.insertHtml(text);
                    }
                }
            );
        });

		//Setup the validator
		validator.mandatoryFields.push('recepient');
		validator.mandatoryFields.push('subject');
	});
}

/*function addPDFTemplate() {
    if(attachmentFiles == null) {
        attachmentFiles = {};
    }

    var templateID = jQuery("#task-template").val();
    if(templateID == "") return;

    attachmentFiles[templateID] = ["dummy", false];
    repaintAttachments();
}*/
/*
var Attachments = {
     addAttachment: function(id, title, filename, options) {
        if(typeof options == 'undefined') {
            options = {};
        }
        if(typeof filename == 'undefined') {
            filename = title;
        }

        attachmentFiles[id] = [title, filename, options];
        repaintAttachments();
    }
}*/

/*function removeAttachment(index) {
    attachmentFiles[index] = false;
    repaintAttachments();
}*/
/*
function repaintAttachments() {
    var html = "";

    var result = {};
    jQuery.each(attachmentFiles, function(index, value) {
        if(value == false) return;
        if(typeof(value) == 'string') { value = [value, false]; }

        result[index] = value;

        if(typeof available_attachments[index] != 'undefined') {
            fileTitle = available_attachments[index];
        } else {
            fileTitle = value[0];
        }
        html += "<div style='padding:2px 0;'><img src='modules/Workflow2/icons/cross-button.png' style='margin-bottom:-3px;' onclick='removeAttachment(\"" + index + "\")'>&nbsp;" + fileTitle + "</div>";
    });

    jQuery("#mail_files").html(html);
    jQuery("#task-attachments").val(JSON.stringify(result));
}

function selectDocumentsAttachment() {
    var popupInstance = Vtiger_Popup_Js.getInstance();
    var params={};
    params['module'] = 'Documents';
    params['src_module'] = 'Workflow2';

    popupInstance.show(params, function(responseString) {
        var file = jQuery.parseJSON(responseString);

        jQuery.each(file, function(index, value) {
            addDocumentAttachment(index, value.info.filename);
        });


        console.log(responseString);
    });

}
jQuery(function() {

    if(attachmentFiles != null) {
        repaintAttachments();
    }
});*/
vtEmailTask = VTEmailTask(jQuery);

jQuery(function() {
	jQuery('#use_mailserver_from').on('change', function() {
		console.log(jQuery(this).prop('checked'));
		if(jQuery(this).prop('checked')) {
			jQuery('#from_row').hide();
		} else {
			jQuery('#from_row').show();
		}
	});

	if(jQuery('#use_mailserver_from').prop('checked')) {
		jQuery('#use_mailserver_from').trigger('change');
	}
});