<datalist id="seriesList">
    {foreach from=$series key=serieKey item=serie}
    <option value='{$serieKey}' label="{vtranslate('LBL_NEXT_NUMBER','Settings:Workflow2')}: {$serie.current}" />
    {/foreach}
</datalist>
<div>
    <input type="hidden" name="task[crmidCol]" value="{$crmidCol}" />
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_WRITE_NUMBER_IN_FIELD','Settings:Workflow2')}</td>
            <td width="15"></td>
       		<td class='dvtCellInfo'>
       			<select name="task[field]" class="chzn-select" style="width:350px;">
       				<option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                       {foreach from=$fields item=block key=blockLabel}
                       <optgroup label="{$blockLabel}">
                            {foreach from=$block item=field key=fieldLabel}
                                {if $field->name != 'crmid' AND $field->name != 'assigned_user_id' AND $field->name != 'createdtime' AND $field->name != 'modifiedby' AND $field->name != 'modifiedtime'}
                                    <option value="{$field->name}" {if $field->name eq $task.field}selected='selected'{/if}>{$field->label}</option>
                                {/if}
                            {/foreach}
                       </optgroup>
                       {/foreach}
       			</select>
       		</td>
       	</tr>
        <tr>
            <td colspan=3><hr/></td>
        </tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_COUNT_SERIE','Settings:Workflow2')}</td>
            <td width="15"></td>
       		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[serie]" data-datalist="seriesList" data-id="serieField">{$task.serie}</div>
       		</td>
       	</tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_SERIE_STRLENGTH','Settings:Workflow2')}</td>
            <td width="15"></td>
       		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[serie_length]" data-id="serie_length" {if $lockFields eq true}readonly="readonly"{/if}>{$task.serie_length}</div>
       		</td>
       	</tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_SERIE_NEXT','Settings:Workflow2')}</td>
            <td width="15"></td>
       		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[serie_start]" data-id="serie_start">{$task.serie_start}</div>
       		</td>
       	</tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_SERIE_PREFIX','Settings:Workflow2')}</td>
            <td width="15"></td>
       		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[serie_prefix]" data-id="serie_prefix" {if $lockFields eq true}readonly="readonly"{/if}>{$task.serie_prefix}</div>
       		</td>
       	</tr>
    </table>
</div>

<script type="text/javascript">
    var seriesData = {$series|json_encode};

</script>