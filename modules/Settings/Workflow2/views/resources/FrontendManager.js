/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 14.01.15 20:25
 * You must not use this file without permission.
 */

var InitProcess = false;
jQuery(function() {
    initEvents();

    InitProcess = true;
    jQuery('select[data-field="position"]').trigger('change');
    InitProcess = false;
});
function SaveOnBlurHandler(e) {
    if(InitProcess === true) return;
    var element = jQuery(this);
    var val = element.val();

    if(element.attr('type') == 'checkbox') {
        val = element.prop('checked') ? 1 : 0;
    } else {
        val = element.val();
    }

    showConfigurations(element.closest('tr'));
    var field = jQuery(this).data('field');
    if(jQuery(this).hasClass('SelectReplacement')) {
        element = jQuery(this).parent().find('.select2-container ul');
    }

    jQuery.post(
        'index.php',
        {
            module: 'Workflow2',
            parent: 'Settings',
            action: 'FrontendSetValue',
            id: jQuery(this).data('id'),
            field: field,
            value: val
        }, function() {
            element.effect( 'highlight', { color: '#00df25' }, 500 );
        }
    );
}
function showConfigurations(parent) {
    var parent = jQuery(parent);
    var value = jQuery('[data-field="position"]', parent).val();

    parent.find('.ConfigContainer').hide();

    parent.find('.ConfigContainer[data-types*="' + value + '"]').show();
}

function initEvents() {
    jQuery('#addWorkflow').select2();

    jQuery('select[data-field="position"]').on('change', function(e) {
        var value = jQuery(e.currentTarget).val();

        showConfigurations(jQuery(e.currentTarget).closest('tr'));

        if(jQuery(e.currentTarget).closest('tr').find('.ConfigContainer[data-types*="' + value + '"] .FieldListing').length > 0) {
            var id = jQuery(e.currentTarget).closest('tr').data('index');
            var field = jQuery(e.currentTarget).closest('tr').find('.ConfigContainer[data-types*="' + value + '"] .FieldListing').data('field');
            if(field == null) field = 'field';

            RedooUtils('Workflow2').getFieldList(jQuery(e.currentTarget).closest('table').data('module')).then(function(fields) {
                var selected = [];

                if(typeof configurations[id][field]) {
                    selected = configurations[id][field];
                }

                var html = '<select data-field="config-' + field + '" style="width:400px;" data-id="' + id + '" class="saveOnBlur EnableSelect2 SelectReplacement" multiple="multiple">';
                jQuery.each(fields, function(blockLabel, fields) {
                    html += '<optgroup label="' + blockLabel + '">';
                    jQuery.each(fields, function(index, field) {
                        html += '<option value="' + field.name + '" ' + (jQuery.inArray(field.name, selected) != -1 ? 'selected="selected"' : '') + '>' + field.label + '</option>';
                    });
                    html += '</optgroup>';
                });
                html += '</select>';

                jQuery(e.currentTarget).closest('tr').find('.FieldListing').html(html);
                jQuery(e.currentTarget).closest('tr').find('.FieldListing .EnableSelect2').removeClass('EnableSelect2').select2();

                jQuery(e.currentTarget).closest('tr').find('.FieldListing .saveOnBlur').on('change', SaveOnBlurHandler);
            });
        }
    });

    jQuery('.addSpecialObjectButton').on('click', function(e) {
        var type = jQuery(this).parent().find('.separatorChooser').val();
        var moduleName = jQuery(this).closest('.frontendManagerTable').data('module');

        jQuery.post('index.php', {
            module: 'Workflow2',
            parent: 'Settings',
            action: 'FrontendAddObject',
            moduleName: moduleName,
            type: type
        }, function() {
            reloadFrontendManager(moduleName);
        });
    });

    jQuery('#addWorkflowButton').on('click', function() {
        jQuery.post('index.php', {
                module: 'Workflow2',
                parent: 'Settings',
                action: 'FrontendAddWorkflow',
                workflowId: jQuery('#addWorkflow').val()
            }, function(response) {
                reloadFrontendManager(response.module);
            }, 'json');
    });

    jQuery('.SaveConfigOnBlur').on('change', function(e) {
        if(InitProcess === true) return;
        var ele = jQuery(this);

        if(ele.attr('type') == 'checkbox') {
            val = ele.prop('checked') ? 1 : 0;
        } else {
            val = ele.val();
        }

        jQuery.post('index.php',
            {
                module: 'Workflow2',
                parent: 'Settings',
                action: 'FrontendSetConfigValue',
                moduleName: ele.data('module'),
                field: ele.data('field'),
                value: val
            }, function() {
                ele.effect( 'pulsate', {times:3}, 100 );
            });

    });

    jQuery('.saveOnBlur').on('change', SaveOnBlurHandler);

    jQuery('.removeFrontendManagerOnClick').on('click', function() {
        jQuery.post('index.php',
            {
                module: 'Workflow2',
                parent: 'Settings',
                action: 'FrontendDelWorkflow',
                id: jQuery(this).data('id'),
            });
        jQuery(this).closest('tr').fadeOut('fast');

    });
    jscolor.init();

    jQuery('.modHead').on('click', function() {
        var header = jQuery(this).closest('.block');
        var target = header.data('target');
        var visible = jQuery('#workflowList' + target).css('display') != 'none';

        if(visible == false) {
            jQuery('#workflowList' + target).show();
            jQuery('.toggleImageExpand', header).hide();
            jQuery('.toggleImageCollapse', header).show();
        } else {
            jQuery('#workflowList' + target).hide();
            jQuery('.toggleImageExpand', header).show();
            jQuery('.toggleImageCollapse', header).hide();
        }

    });

    jQuery('.frontendManagerTable tbody').sortable({
          forcePlaceholderSize:true,
          distance:15,
          containment: "parent",
          update:function() {
              var sorted = jQuery( this ).sortable( "toArray", { 'attribute':'data-index' });

              jQuery.post('index.php', { 'module': 'Workflow2', 'parent':'Settings', 'action':'FrontendSaveOrder', 'indexes[]': sorted});

              Vtiger_Helper_Js.showPnotify({type:'success', text:app.vtranslate('LBL_SAVED_SUCCESSFULLY')});
          }

      });
      jQuery( "ImageManagerInteral" ).disableSelection();

}

function reloadFrontendManager(openModule) {
    if(typeof openModule == 'undefined') {
        openModule = false;
    }

    RedooUtils('Workflow2').refreshContent('FrontendManager', true).then(function() {
        initEvents();
        if(openModule !== false) {
            jQuery('.blockHeader[data-target="' + openModule + '"]').trigger('click');
        }
    });


}

