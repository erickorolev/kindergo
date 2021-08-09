<script type="text/javascript">
    var extraWsModuleFieldOptions = {ldelim}
        "hdnS_H_Amount" : {ldelim}
            "label" : "Shipping & Handling Charges",
            "name"  : "hdnS_H_Amount",
            "type" : {ldelim}
                "name" : "string"
            {rdelim}
        {rdelim}
    {rdelim};

    //var setter_values = {$task.setter|@json_encode};
    var global_values = {$task.global|@json_encode};
    //var availUsers = {$availUsers|@json_encode};
    var availCurrency = {$availCurrency|@json_encode};
    var availTaxes = {$availTaxes|@json_encode};
    //var module_name = "{$module_name}";
    //var new_module_name = "{$module_name}";
    // var workflowModuleName = "{$new_module}";

    {*var productList = {$productlist|@json_encode};*}
    {*var taxlist = {$taxlist|@json_encode};*}
</script>
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right">{$MOD.LBL_CREATE_RECORD_OF_MODULE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                    <select name='task[new_module]' style="width:600px;" class="select2" onchange="jQuery('#new_module').val(jQuery(this).val());document.forms['hidden_related_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$avail_module item=module key=tabname}
                        <option {if $new_module == $tabname}selected='selected'{/if} value="{$tabname}">{$module}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        {if $new_module neq ''}
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_RUN_WORKFLOW_WITH_NEW_RECORD}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                    <select name='task[exec_workflow]' style="width:600px;" class="select2" >
                        <option {if $task.exec_workflow == ""}selected='selected'{/if} value="">{$MOD.LBL_NO_WORKFLOW}</option>
                    {foreach from=$extern_workflows item=workflow}
                        <option {if $task.exec_workflow == $workflow.id}selected='selected'{/if} value="{$workflow.id}">{$workflow.title}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td width="15"></td>
            <td class="dvtCellInfo"><input type="checkbox" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}></td>
        </tr>
        {/if}
    </table>
</div>


{if $new_module neq ''}
    {$setterContent}

    <h5>{vtranslate('Global Values', 'Settings:Workflow2')}</h5>

<div id='InventoryGlobalValues' style="padding:0 20px;"></div>

    <div style="clear:both;overflow:hidden">
        {if !empty($InventoryLoaderString)}
            {$InventoryLoaderString}
        {/if}
    </div>

    <h5>{vtranslate('Products', 'Settings:Workflow2')}</h5>


    {$ProductChooser}
{/if}
</form>
<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[new_module_setter]" id='new_module' value=''>
</form>
