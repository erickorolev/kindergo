<div id="FormContainer">
    <div id="FormDesigner">
        <button type="button" class="FormSettingsBtn btn btn-default"><i class="fa fa-cog" aria-hidden="true"></i> {vtranslate('Form Settings', 'Settings:Workflow2')}</button>
        <button type="button" class="addRowBtn pull-right btn btn-default"><i class="fa fa-plus-square" aria-hidden="true"></i> {vtranslate('Add Row', 'Settings:Workflow2')}</button>

        <h5>Form Builder</h5>
        <hr/>
        <div id="FormDesignContainer"></div>
    </div>

    <div id="FormSettings">
        <div class="FormBuilderTabs" id="FieldSettingsTab" style="display:none;">
            <i class="pull-right fa fa-trash DeleteFieldBtn" data-text="{vtranslate('Please confirm to delete field', "Settings:Workflow2")}" style="font-size:18px;margin-right:10px;" aria-hidden="true"></i>
            <h5>Field Settings</h5>
            <hr/>
            <div class="group materialstyle">
                <select id="type_selector" class="select2 used ConfigTypeValue">
                    {foreach from=$fieldTypes key=type item=label}
                        <option value="{$type}">{$label}</option>
                    {/foreach}
                </select>
                <label>{vtranslate('Inputtype', 'Settings:Workflow2')}</label>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigLabelValue" name="__data_value" data-id="task_data_name_value"></div>
                        <label>{vtranslate('Label', 'Settings:Workflow2')}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigNameValue" name="__data_name" data-id="task_data_name_value"></div>
                        <label>{vtranslate('Variablename to access', 'Settings:Workflow2')}</label>
                    </div>
                </div>
            </div>
            <div id="FormSettingsContainer"></div>
        </div>
        <div class="FormBuilderTabs" id="FormSettingsTab" style="display:none;">
            <h5>Form Settings</h5>
            <hr/>
            <div class="group materialstyle">
                <div class="configField insertTextfield ConfigHeadlineValue" name="__headline" data-id="__headline"></div>
                <label>{vtranslate('Headline of Popup', 'Settings:Workflow2')}</label>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigWidthValue" name="__width" data-id="__width"></div>
                        <label>{vtranslate('Width of Popup', 'Settings:Workflow2')}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigScopeValue" name="__scope" data-id="__scope"></div>
                        <label>{vtranslate('Variable scope to access fields', 'Settings:Workflow2')}</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigContinueText" name="__width" data-id="__width"></div>
                        <label>{vtranslate('Text on continue Button', 'Settings:Workflow2')}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="group materialstyle">
                        <div class="configField insertTextfield ConfigStopText" name="__scope" data-id="__scope"></div>
                        <label>{vtranslate('Text on stop Button', 'Settings:Workflow2')}</label>
                    </div>
                </div>
            </div>
            <div id="FormSettingsContainer"></div>
        </div>
    </div>
</div>

{foreach from=$fields item=fieldConfig}
    <script type="text/template" id="fieldtemplate_{$fieldConfig.id|@strtolower}">
        {foreach from=$fieldConfig.config key=variable item=config}
            <div class="group materialstyle {if !empty($config.description)}WithDescription{/if}" data-key="{$variable}">
                {if $config.type == 'checkbox'}
                    {*<div style="float:left;padding-right:10px;">*}
                        <input class="used configField rcSwitch doInit" type="checkbox" data-type="{$config.type}" data-variable="{$variable}" name="task[{$field}][##FIELDNAME##][config][{$variable}]"  data-id="task_{$field}_##FIELDNAME##_config_{$variable}" id="task_{$field}_##FIELDNAME##_config_{$variable}" value="{$config.value}">
                    {*</div>*}

                    &gt;script type="text/javascript">
                        window.FormBuilder.registerInit('{$variable}', function(key) {
                            var group = window.FormBuilder.getGroup(key);

                            jQuery('.rcSwitch', group).on('toggle.rcSwitcher', function() {
                                group.trigger('change');
                            });
                        });

                        window.FormBuilder.registerFieldValueGetter('{$variable}', function(key) {
                            var ele = $('input.rcSwitch[data-variable="' + key + '"]');
                            return (ele.prop('checked') ? ele.attr('value') : '');
                        });

                        window.FormBuilder.registerFieldValueSetter('{$variable}', function(key, value) {
                            var group = window.FormBuilder.getGroup(key);

                            jQuery('.rcSwitch', group).prop('checked', value == jQuery('.rcSwitch', group).attr('value'));
                        });
                    &gt;/script>
                {/if}

                {*{if $config.type != 'condition'}*}
                {*<div style='padding:0;padding-right:5px;line-height:{if $config.type != 'checkbox'}20{else}30{/if}px;font-size:11px;{if $config.type != 'label'}font-weight:bold;{else}font-style:italic;{/if}'>{vtranslate($config.label, 'Settings:Workflow2')}</div>*}
                {*{/if}*}

                {if $config.type != 'label'}
                    {if $config.type eq 'templatefield' || $config.type eq 'templatearea'}
                        &gt;script type="text/javascript">
                        window.FormBuilder.registerInit('{$variable}', function(key) {
                            var group = window.FormBuilder.getGroup(key);

                            jQuery('.templateField', group).on('change', function() {
                                console.log('changed');
                            });
                        });

                        window.FormBuilder.registerFieldValueGetter('{$variable}', function(key) {
                            var group = window.FormBuilder.getGroup(key);

                            return jQuery('.templateField', group).val();
                        });

                        window.FormBuilder.registerFieldValueSetter('{$variable}', function(key, value) {
                            var group = window.FormBuilder.getGroup(key);

                            jQuery('.templateField', group).val(value);
                            jQuery('.templateField', group).trigger('blur');
                        });
                        &gt;/script>
                    {/if}
                    {*<div style="{if $variable eq 'mandatory'}display:inline;{/if}">*}
                        {if $config.type eq 'templatefield'}
                            <div class="configField insertTextfield" data-style="width:{$width}px;" data-type="{$config.type}" data-name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-id="task_config_{$variable}" data-placeholder="{$config.placeholder}"></div>
                        {elseif $config.type eq 'templatearea'}
                            <div class="configField insertTextarea" data-type="{$config.type}" data-name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-placeholder="{$config.placeholder}" data-id="task_config_{$variable}" data-options='{ldelim}"height":"100px"{rdelim}'>{$value}</div>
                        {elseif $config.type eq 'picklist'}
                            <select name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-nomodify="{$config.nomodify}" data-variable="{$variable}" data-id="task__config_{$variable}" id="task_config_{$variable}" data-type="{$config.type}" style="width:100%;height:30px;" class="used configField MakeSelect2" >
                                {html_options options=$config.options}
                            </select>

                            &gt;script type="text/javascript">
                            window.FormBuilder.registerFieldValueGetter('{$variable}', function(key) {
                            var group = window.FormBuilder.getGroup(key);

                            return jQuery('select', group).select2('val');
                            });

                            window.FormBuilder.registerFieldValueSetter('{$variable}', function(key, value) {
                            var group = window.FormBuilder.getGroup(key);

                            return jQuery('select', group).select2('val', value);
                            });
                            &gt;/script>


                        {elseif $config.type eq 'condition'}
                            <input type="hidden" class="configField SelectConditionValue" name="task[{$variable}][config][{$variable}]" data-variable="{$variable}" data-id="task_config_{$variable}" id="task_config_{$variable}" data-type="hidden" />
                            <button class="btn btn-primary SelectConditionBtn" type="button" id="record_confition_btn_{$variable}">{vtranslate($config.label, 'Settings:Workflow2')}</button>

                            &gt;script type="text/javascript">
                            window.FormBuilder.registerInit('{$variable}', function(key) {
                                var group = window.FormBuilder.getGroup(key);

                                jQuery('.SelectConditionBtn', group).on('click', function() {
                                    ConditionPopup.open('#task_config_{$variable}', '#task_config_{$config.moduleField}', 'LBL_FILTER_RECORDS_2_SELECT');
                                });
                                jQuery('.SelectConditionValue', group).on('change', function() {
                                    group.trigger('change');
                                });
                            });

                            window.FormBuilder.registerFieldValueGetter('{$variable}', function(key) {
                                var group = window.FormBuilder.getGroup(key);

                                return jQuery('.SelectConditionValue', group).val();

                            });

                            window.FormBuilder.registerFieldValueSetter('{$variable}', function(key, value) {
                                var group = window.FormBuilder.getGroup(key);

                                jQuery('.SelectConditionValue', group).val(value);
                            });
                            &gt;/script>

                        {/if}
                    {*</div>*}
                {/if}
                {if $config.type neq 'condition'}
                    <label>{vtranslate($config.label, 'Settings:Workflow2')}</label>
                {/if}
                {if !empty($config.description)}<em>{vtranslate($config.description, 'Settings:Workflow2')}</em>{/if}

            </div>
        {/foreach}
    </script>
{/foreach}
<script type="text/javascript">
    var AvailableFieldTypes = {$fieldTypes|json_encode};
</script>
<div id="HiddenFormData"></div>
<style type="text/css">
    #FormContainer {
        display:flex;
        flex-direction: row;
        min-height: 500px;
    }
    #FormDesigner {
        flex-basis:50%;
        flex-grow:1;
        border-right:2px solid #d1d7e6;
        padding-right:5px;
    }
    #FormSettings {
        flex-basis:50%;
        flex-grow:1;
        padding-left:5px;
    }
    .FormBuilderRowContainer {
        display:flex;
        flex-direction:row;
        min-height:40px;
    }
    .FormBuilderFieldContainer {
        border: 3px solid #e9efff;
        border-radius: 3px;
        font-weight: bold;
        font-size: 12px;
        height: 50px;
        flex-grow: 1;
        margin: 2px;
        color: #ccc;
        padding: 8px;
        background-color:#ffffff;
        cursor:pointer;
        width:100%;
    }
    .FormBuilderFieldContainer .ExtraInformation {
        text-align:right;
    }
    .FormBuilderFieldContainer.ActiveField {
        border-color:#3bb72c;
        color:#000000;
    }
    .RowActions {
        margin:5px 0 0 5px;
        text-align:center;
    }
    .FormBuilderOuterRow {
        padding:5px 5px 5px 0;
        overflow:hidden;
        clear:both;
        min-height:66px;
        width:100%;
        margin-bottom:0px;
        border:1px solid transparent;
    }
    .FormBuilderOuterRow:hover {
        border:1px solid #d1d7e6;
    }
    .addRowBtn, .addFieldBtn {
        cursor:pointer;
    }
    i.addFieldBtn {
        font-size:16px;
    }
    i.RowMoveHandler {
        cursor:pointer;
    }
    i.DeleteFieldBtn {
        cursor:pointer;
    }
    body {
        padding-right:0 !important;
    }
    body.modal-open {
        overflow:hidden;
    }
</style>
