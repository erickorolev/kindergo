<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
            <td class="dvtCellInfo" align="left">
                <select class="select2" name='task[search_module]' style="width:350px;" onchange="jQuery('#search_module_hidden').val(jQuery(this).val());document.forms['hidden_search_form'].submit();">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$related_modules item=module key=tabid}
                        <option {if $related_tabid == $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_EXEC_FOR_THIS_NUM_ROWS}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="defaultTextfield" id='found_rows' value="{$task.found_rows}" style="width:50px;"> ({$MOD.LBL_EMPTY_ALL_RECORDS})
            </td>
        </tr>
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.SORT_RESULTS_WITH}</td>
            <td class='dvtCellInfo'>
                <select name="task[sort_field]" class="select2" style="width:350px;">
                    <option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                    {foreach from=$sort_fields item=block key=blockLabel}
                        <optgroup label="{$blockLabel}">
                            {foreach from=$block item=field key=fieldLabel}
                                <option value="{$field->name}" {if $field->name eq $task.sort_field}selected='selected'{/if}>{$field->label}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}


                </select>
                <select class="select2" style="width:100px;"name="task[sortDirection]"><option value="ASC"{if $task.sortDirection eq "ASC"}selected='selected'{/if}>ASC</option><option value="DESC"{if $task.sortDirection eq "DESC"}selected='selected'{/if}>DESC</option></select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('$env Variable to be saved to', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[envvar]' class="defaultTextfield" id='found_rows' value="{$task.envvar}" style="width:150px;"> ({vtranslate('Result of Calculation will be available in this $env[...] variable.', 'Settings:Workflow2')})
            </td>
        </tr>

    </table>
</div>
{if !empty($related_tabid)}
<div class="Fields">
        <h4>Fields to calculate</h4>
        <hr/>
    <div style="overflow:hidden">
        <div style="text-align:left;float:right;">
        <select id="calcfields" class="select2" style="width:300px;text-align:left;">
            {foreach from=$sort_fields item=block key=blockLabel}
                <optgroup label="{$blockLabel}">
                    {foreach from=$block item=field key=fieldLabel}
                        <option value="{$field->name}">{$field->label}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
        <input type="button" class="btn btn-default AddCalcField" value="{vtranslate('Add Field', 'Settings:Workflow2')}" />
        </div>
    </div>
    <div style="border:1px solid #eee; margin:5px;padding:5px;" id="fieldContainer"></div>
</div>
    <h4>Selection of Records</h4>
    <hr/>

    {$recordsources}
    {*<h4><input type="radio" name="task[recordsource]" class="recordSourceSelection" value="condition" {if empty($task.recordsource) || $task.recordsource eq 'condition'}checked="true"{/if} />{vtranslate('get Records by Condition','Settings:Workflow2')}</h4>*}
    {*<hr/>*}
    {*<div class="recordSource recordSource_condition">*}
        {*{$conditionalContent}*}
    {*</div>*}
    {*<br/>*}

    <script type="text/javascript">
        var deposit = document.getElementById("found_rows");
        var productCache = {$productCache|@json_encode};

        deposit.onkeyup = function() {ldelim}
            var PATTERN = /\d$/;

            if (!deposit.value.match(PATTERN)) {ldelim}
                deposit.value = deposit.value.replace(deposit.value.slice(-1), "");
                {rdelim}
            {rdelim}

        var CalcFieldsCounter = 0;

        jQuery(function() {
            function addField(fieldname, fieldlabel, type, envvar) {
                if(typeof type == 'undefined') {
                    type = 'SUM';
                }
                if(typeof envvar == 'undefined') {
                    envvar = fieldname + '_' + type.toLowerCase();
                }
                currentIndex = CalcFieldsCounter;
                CalcFieldsCounter++;

                var html = '<div class="CalcField" style="display:flex;font-size:14px;line-height:28px;" data-index="' + currentIndex + '">';
                        html += '<img style="margin-top:5px;width:16px;height:16px;margin-right:10px;cursor:pointer;" src="modules/Workflow2/views/resources/img//cross-button.png" class="RemoveCalcField" />';
                        html += '<input type="hidden" name="task[fields][' + currentIndex + '][fieldname]" value="' + fieldname + '" />';
                        html += '<input type="hidden" name="task[fields][' + currentIndex + '][fieldlabel]" value="' + fieldlabel + '" />';
                        html += '<div style="width:15%;flex-grow:0;" class="fieldName">' + fieldlabel + '</div>';

                        html += '<select class="MakeSelect2" style="margin:0;text-align:left;width:200px;" name="task[fields][' + currentIndex + '][operation]">';
                        html += '<option value="SUM" ' + (type == 'SUM'?'selected="selected"':'') + '>Summarize</option>';
                        html += '<option value="SUMCURR" ' + (type == 'SUMCURR'?'selected="selected"':'') + '>Summarize currencies</option>';
                        html += '<option value="AVG" ' + (type == 'AVG'?'selected="selected"':'') + '>Average</option>';
                        html += '<option value="MAX" ' + (type == 'MAX'?'selected="selected"':'') + '>Maximum</option>';
                        html += '<option value="MIN" ' + (type == 'MIN'?'selected="selected"':'') + '>Minimum</option>';
                        html += '<option value="Count" ' + (type == 'Count'?'selected="selected"':'') + '>Count</option>';
                        html += '<option value="CountDistinct" ' + (type == 'CountDistinct'?'selected="selected"':'') + '>Count Unique</option>';
                        html += '</select>';
                        html += '<span style="height:30px;">=> $env[...][</span><input type="text" class="defaultTextfield" style="margin:0;text-transform:lowercase;" name="task[fields][' + currentIndex + '][envvar]" value="' + envvar + '" />]';

                html += '</div>';

                jQuery('#fieldContainer').append(html);

                jQuery('.MakeSelect2').removeClass('MakeSelect2').select2();

                jQuery('.RemoveCalcField', 'div.CalcField[data-index="' + currentIndex + '"]').on('click', function(e) {
                    jQuery(e.currentTarget).closest('.CalcField').slideUp('fast', function() {
                        jQuery(this).remove();
                    });
                });

                return currentIndex;
            }
            jQuery('.AddCalcField').on('click', function() {
                addField(jQuery('#calcfields').val(), jQuery('#calcfields option:selected').text());
            });

            checkVisibility(true);

            jQuery('.recordSourceSelection').on('click', function() { checkVisibility(false); });
            if(typeof oldTask.fields !== 'undefined') {
                jQuery.each(oldTask.fields, function(index, field) {
                    addField(field.fieldname, field.fieldlabel, field.operation, field.envvar);
                });
            }
        });
        function checkVisibility(init) {
            var source= jQuery('.recordSourceSelection:checked').val();
            if(init == true) {
                jQuery('.recordSource').hide();
                jQuery('.recordSource_' + source).show();

            } else {
                jQuery('.recordSource').slideUp();
                jQuery('.recordSource_' + source).slideDown();

            }
        }



    </script>

{/if}
</form>

<form method="POST" name="hidden_search_form" action="#">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>