<div>
    <table width="100%" cellspacing="0" cellpadding="0" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                    <select name='task[related_module]' class="select2" style="width:400px;" onchange="jQuery('#related_module_hidden').val(jQuery(this).val());document.forms['hidden_related_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                        {foreach from=$related_modules item=module}
                            <option {if $related_tabid == $module.related_tabid}selected='selected'{/if} value="{$module.action}#~#{$module.related_tabid}">{$module.label}</option>
                        {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_FOUND_ROWS}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="defaultTextfield" id='found_rows' value="{$task.found_rows}" style="width:50px;margin:2px 0;">
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Store result Records<br>in the following Environment Variable')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[resultEnv]' class="defaultTextfield" id='found_rows' value="{$task.resultEnv}" style="width:350px;">
            </td>
        </tr>

    </table>
</div>

{if !empty($related_tabid)}
<div id='conditional_container'><div style="margin:50px auto;text-align:center;font-weight:bold;color:#aaa;font-size:18px;">{$MOD.LOADING_INDICATOR}<br><br><img src='modules/Workflow2/loader.gif' alt='Loading ...'></div></div>
<link rel="stylesheet" type="text/css" media="all" href="modules/Workflow2/style.css">

<script src="modules/Workflow2/resources/vtigerwebservices.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">
    var conditional = {$conditional|@json_encode};
    var condition_module = "{$module_name}";
    var condition_fields = {$module_fields|@json_encode};
    var condition_container = jQuery("#conditional_container");

    var describeModule = {$describe|@json_encode};
</script>
<script type="text/javascript">
    var MOD = {$MOD|@json_encode};
</script>
<script type="text/javascript">
       var workflowModuleName = '{$workflow_module_name}';
</script>
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

<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[related_module]" id='related_module_hidden' value=''>
</form>