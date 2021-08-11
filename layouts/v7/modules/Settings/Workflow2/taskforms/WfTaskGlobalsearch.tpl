<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
            <td class="dvtCellInfo" align="left">
                    <select class="select2" name='task[search_module]' style="width:360px;" onchange="jQuery('#search_module_hidden').val(jQuery(this).val());document.forms['hidden_search_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$related_modules item=module key=tabid}
                        <option {if $related_tabid == $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_FOUND_ROWS}</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="defaultTextfield" id='found_rows' value="{$task.found_rows}" style="width:50px;">
            </td>
        </tr>
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('SORT_RESULTS_WITH', 'Settings:Workflow2')}</td>
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
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Store result Records<br>in the following Environment Variable')}</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[resultEnv]' class="defaultTextfield" id='found_rows' value="{$task.resultEnv}" style="width:350px;">
            </td>
        </tr>
    </table>
</div>

{if !empty($related_tabid)}
{$recordsources}
    {*{$conditionalContent}*}

<script type="text/javascript">
    var deposit = document.getElementById("found_rows");

    deposit.onkeyup = function() {ldelim}
        var PATTERN = /\d$/;

        if (!deposit.value.match(PATTERN)) {ldelim}
            deposit.value = deposit.value.replace(deposit.value.slice(-1), "");
        {rdelim}
    {rdelim}
</script>
{/if}
</form>

<form method="POST" name="hidden_search_form" action="#" onsubmit="">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>