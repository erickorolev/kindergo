<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="dvtCellLabel" width="45%" align="right">Write to Record:</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select class="chzn-select" name="task[srcrecord]">
                <option value="crmid">{vtranslate('LBL_ID_OF_CURRENT_RECORD', 'Settings:Workflow2')}</option>
                {foreach from=$reference item=field}
                    <option value="{$field.fieldname}" {if $task.srcrecord eq $field.fieldname}selected="selected"{/if}>{$field.fieldlabel}</option>
                {/foreach}
            </select>
        </td>
    </tr>
</table>

<br/>
<button type="button" onclick="addCol();" class="btn btn-primary">add Value</button>

<div id="rows"></div>

<script type="text/javascript">
    var cols = {$cols|@json_encode}
</script>