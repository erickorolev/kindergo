<input type="hidden" name="changeModule" id="changeModule" value="0" />

{vtranslate('This Block works only if you previously store some recordIds in the Environment. (Could be done in "global Search" or "exist Related record")', 'Settings:Workflow2')}

<table width="100%" cellspacing="0" cellpadding="5" class="newTable">
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Records are in Module', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left">
                <select class="chzn-select" name='task[search_module]' style="width:250px;" onchange="jQuery('#changeModule').val('1');jQuery('#save').trigger('click');">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                {foreach from=$related_modules item=module key=tabid}
                    <option {if $related_tabid == $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                {/foreach}
                </select>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Environment ID of records', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left">
            <input type='text' name='task[envId]' class="defaultTextfield" id='envId' value="{$task.envId}" style="width:350px;">
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Show total row for number fields', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left">
            <input type='checkbox' name='task[totalrow]' class="defaultTextfield" id='totalrow' {if $task.totalrow eq '1'}checked="checked"{/if} value="1" >
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Use translated values', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left">
            <select name="task[translated]" class="select2" style="width:350px;" placeholder="{vtranslate('values from database')}">
                <option value="">No localization</option>
                {foreach from=$languages key=LANG item=TITLE}
                    <option value="{$LANG}" {if $task.translated eq $LANG}selected="selected"{/if}>{$TITLE} ({$LANG})</option>
                {/foreach}
            </select>
        </td>
    </tr>
    {if $task.envId neq ''}
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('to use this list use the following function', 'Settings:Workflow2')}:</td>
        <td class="dvtCellInfo" align="left" style="font-family: 'Courier New'">
            ${ldelim} return wf_recordlist("{$task.envId}"); {rdelim}{rdelim}>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('to use this list in PDFMaker/PDFGenerator use the following function', 'Settings:Workflow2')}:</td>
        <td class="dvtCellInfo" align="left" style="font-family: 'Courier New'">
            [CUSTOMFUNCTION|pdfmaker_recordlist|{$task.envId}|CUSTOMFUNCTION]
        </td>
    </tr>
    {/if}
</table>
<div id="fieldlist"></div>
<input type="button" class="btn btn-primary" onclick="addField()" value="add Field" />
<div id="staticFieldsContainer" style="display:none;">
    <div class="rowConfig" style="margin:5px 0px;" id="setterRow_##SETID##">
        <select style="vertical-align:top;width:300px;" name='task[{$StaticFieldsField}][##SETID##][field]' id='staticfields_##SETID##_field' class="fieldSelect MakeSelect2">
            <option value=''>{vtranslate('LBL_CHOOSE', 'Workflow2')}</option>
            <option value=';;;delete;;;' class='deleteRow'>{vtranslate('LBL_DELETE_SET_FIELD', 'Workflow2')}</option>
            <option value='link'>{vtranslate('Link to the Record', 'Workflow2')}</option>
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

        <input type='text' style="margin-bottom:0;" class="defaultTextfield" id='staticfields_##SETID##_label' name='task[{$StaticFieldsField}][##SETID##][label]' value='' placeholder="Headline of the column" />
        <input type='text' style="margin-bottom:0;" class="defaultTextfield" id='staticfields_##SETID##_width' name='task[{$StaticFieldsField}][##SETID##][width]' value='' placeholder="Width of the column" />
        <input type='text' style="margin-bottom:0;width:200px;"  class="defaultTextfield" id='staticfields_##SETID##_value' name='task[{$StaticFieldsField}][##SETID##][value]' value='' placeholder="Modify value" />
    </div>
</div>
<script type="text/javascript">jQuery(function() { initRecordListFields({$fields|@json_encode}); });</script>