<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
       		<td class='dvtCellLabel' align="right" width=20% nowrap="nowrap">Treffer sortieren nach</td>
       		<td class='dvtCellInfo'>
       			<select name="task[sort_field]" class="chzn-select" style="width:350px;">
       				<option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                       {foreach from=$sort_fields item=block key=blockLabel}
                       <optgroup label="{$blockLabel}">
                            {foreach from=$block item=field key=fieldLabel}
                                <option value="{$field->name}" {if $field->name eq $task.sort_field}selected='selected'{/if}>{$field->label}</option>
                            {/foreach}
                       </optgroup>
                       {/foreach}
       			</select>
               <select class="chzn-select-nosearch" style="width:100px;"name="task[sortDirection]"><option value="ASC"{if $task.sortDirection eq "ASC"}selected='selected'{/if}>ASC</option><option value="DESC"{if $task.sortDirection eq "DESC"}selected='selected'{/if}>DESC</option></select>
       		</td>
       	</tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="20%">{vtranslate('check this number of mails', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="textfield" id='found_rows' value="{$task.found_rows}" style="width:50px;"> ({$MOD.LBL_EMPTY_ALL_RECORDS})
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="20%">{vtranslate('mails related to this record', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" style="display: inline-block;" data-name="task[recordid]" data-id="recordid">{$task.recordid}</div> (Default: {vtranslate('LBL_ID_OF_CURRENT_RECORD', 'Workflow2')})
            </td>
        </tr>
    </table>
</div>
<p>{vtranslate('If one related of the checked mails was already accessed, this block continue with the yes path, otherwise with the no path.', 'Settings:Workflow2')}</p>
<p>{vtranslate('If no related mail has match your condition, the block will take the "no_record_found" path', 'Settings:Workflow2')}</p>

{$conditionalContent}