<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="30%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
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
            <td class="dvtCellLabel" align="right" width="30%">{vtranslate('Selection chain ID', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[chainid]' class="defaultTextfield" id='found_rows' required="chainid" value="{$task.chainid}" style="width:250px;">
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="30%">{vtranslate('AND / OR the other selections', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <select name="task[combine]">
                    <option value="AND" {if $task.combine eq 'AND'}selected="selected"{/if}>{vtranslate('LBL_AND')|strtoupper}</option>
                    <option value="OR" {if $task.combine eq 'OR'}selected="selected"{/if}>{vtranslate('LBL_OR')|strtoupper}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="30%">{vtranslate('Include or Exclude selected Records from chain', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <select name="task[includemode]">
                    <option value="include" {if $task.includemode eq 'include'}selected="selected"{/if}>{vtranslate('Include', 'Settings:Workflow2')|strtoupper}</option>
                    <option value="exclude" {if $task.includemode eq 'exclude'}selected="selected"{/if}>{vtranslate('Exclude', 'Settings:Workflow2')|strtoupper}</option>
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
    </table>
</div>

{if !empty($related_tabid)}
{$recordsources}
{/if}

</form>

<form method="POST" name="hidden_search_form" action="#">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>