<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
            <td class="dvtCellInfo" align="left">
                    <select class="chzn-select" name='task[search_module]' style="width:350px;" onchange="jQuery('#search_module_hidden').val(jQuery(this).val());document.forms['hidden_search_form'].submit();">
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
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">Treffer sortieren nach</td>
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
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">Folgende Expression ausführen</td>
       		<td class='dvtCellInfo'>
       			<textarea name="task[expression]" id="custom_expression" rows="6" style='width:80%;' class='span6 customFunction textfield'>{$task.expression}</textarea>
                   <span id='task_condition_iconspan'><img src='modules/Workflow2/icons/templatefieldPHP.png' style='margin-bottom:-2px;cursor:pointer;' onclick="insertTemplateField('custom_expression', '[source]->[module]->[destination]', false, false,  {ldelim}module: '{$search_module}'{rdelim});">
       		</td>
       	</tr>
    </table>
</div>

{if !empty($related_tabid)}
{$recordsources}
{/if}
</form>

<form method="POST" name="hidden_search_form" action="#">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>