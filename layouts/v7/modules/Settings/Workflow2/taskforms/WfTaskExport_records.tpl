<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">Records are in Module</td>
        <td class="dvtCellInfo" align="left">
                <select class="select2" id="search_module"  name='task[search_module]' style="width:250px;" onchange="jQuery('#changeModule').val('1');jQuery('#save').trigger('click');">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                {foreach from=$related_modules item=module key=tabid}
                    <option {if $related_tabid == $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                {/foreach}
                    <option value="Custom" {if $task.search_module eq 'Custom'}selected='selected'{/if}>{vtranslate('Custom Data', 'Settings:Workflow2')}</option>
                </select>
        </td>
    </tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Environment ID with Records', 'Workflow2')}&nbsp;&nbsp;<input type="radio" name="task[source]" {if empty($task.source) || $task.source eq 'envid' || $task.search_module eq 'Custom'}checked="checked"{/if}value="envid"/>:</td>
		<td class='dvtCellInfo'>
            <div class="insertTextfield" data-name="task[recordlistid]" data-id="recordlistid">{$task.recordlistid}</div>
		</td>
	</tr>
    {if $task.search_module neq 'Custom'}
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('CustomView to Export', 'Workflow2')}&nbsp;&nbsp;<input type="radio" name="task[source]" {if $task.source eq 'customview'}checked="checked"{/if}value="customview"/>:</td>
		<td class='dvtCellInfo'>
            <select class="select2" name="task[customviewsource]" data-placeholder="{vtranslate('select customview', 'Settings:Workflow2')}" style="width:300px;">
                <option value=""></option>
                {foreach from=$customviews key=cvid item=viewname}
                <option value="{$cvid}" {if $task.customviewsource eq $cvid}selected="selected"{/if}>{$viewname}</option>
                {/foreach}
            </select>
		</td>
	</tr>
    {/if}
    <tr>
   		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('filename of generated file', 'Workflow2')}:</td>
   		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[filename]" data-id="filename">{$task.filename}</div>
   		</td>
   	</tr>
    <tr>
        <td valign="top" class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('export this fileformat', 'Workflow2')}</td>
        <td>
            <select name="task[fileformat]">
                <option value="csv">CSV</option>
                <option value="excel" {if $task.fileformat eq excel}selected="selected"{/if}>Excel</option>
            </select>
        </td>
    </tr>
    <tr>
        <td valign="top" class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('insert Headline into first row', 'Workflow2')}</td>
        <td>
            <input type="checkbox" name="task[insertheadline]" value="1" {if $task.insertheadline eq '1'}checked="checked"{/if}>
        </td>
    </tr>
    <tr>
        <td valign="top" class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('What to to with the file', 'Workflow2')}</td>
        <td>
            {$fileactions_resultaction}
        </td>
    </tr>
</table>
<h4>{vtranslate('export this fields', 'Workflow2')}</h4>
<hr/>
<div id="fieldlist" class="hide"></div>
<input type="button" class="btn btn-primary" onclick="addField();" value="add Field" />&nbsp;&nbsp;&nbsp;<input type="button" class="btn btn-primary" onclick="addAllFields();" value="add all Fields of main module" />
<div id="staticFieldsContainer" style="display:none;">
    <div style="margin:5px 0px;" id="setterRow_##SETID##">
        <img src="modules/Workflow2/icons/cross-button.png" style="vertical-align:top;margin-top:5px;" onclick="delField(##SETID##)" />

        <input type='text' class="defaultTextfield" style="margin-bottom:0;vertical-align:top;" id='staticfields_##SETID##_label' name='task[{$StaticFieldsField}][##SETID##][label]' value='' placeholder="Headline of the column" />

        <select style="vertical-align:top;width: 150px;" disabled="disabled" name="task[{$StaticFieldsField}][##SETID##][mode]" id="staticfields_##SETID##_mode" class="MakeSelect2">
            <option value="value">{vtranslate("LBL_STATIC_VALUE", "Settings:Workflow2")}</option>
            {if $task.search_module neq 'Custom'}
                <option value="field">{vtranslate("LBL_FIELD_VALUE", "Settings:Workflow2")}</option>
            {else}
                <option value="column">{vtranslate("column in array", "Settings:Workflow2")}</option>
            {/if}
            <option value="function">{vtranslate("LBL_FUNCTION_VALUE", "Settings:Workflow2")}</option>
        </select>
        <div style="display:inline-block; width:400px;" id='value_##SETID##_container'>
            <input type='text'  disabled="disabled" name='task[{$StaticFieldsField}][##SETID##][value]' id='staticfields_##SETID##_value'>
        </div>

    </div>
</div>
<script type="text/javascript">
    jQuery(function() { initRecordListFields({$fields|@json_encode}); });
    var fromFields = {$fromFields|@json_encode};
    var StaticFieldsField = "{$StaticFieldsField}";
    var target_module_name = "{$target_module_name}";
</script>
<script type="text/dummy" id='fromFieldsFieldValues'>
<select style="vertical-align:top;width:300px;" class="MakeSelect2 selectFields" name='##FIELDNAME##' id='##FIELDID##'>
    <option value=''>{vtranslate('LBL_CHOOSE', 'Workflow2')}</option>
    <option value=';;;delete;;;' class='deleteRow'>{vtranslate('LBL_DELETE_SET_FIELD', 'Workflow2')}</option>
    {foreach from=$fromFields key=label item=block}
        <optgroup label="{$label}">
        {foreach from=$block item=field}
            {if $field->name neq "smownerid"}
                <option value='${$field->name}'>{$field->label}</option>
            {else}
                <option value='$assigned_user_id'>{$field->label}</option>
            {/if}
        {/foreach}
        </optgroup>
    {/foreach}
</select>
</script>