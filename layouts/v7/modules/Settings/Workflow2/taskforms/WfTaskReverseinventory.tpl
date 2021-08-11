<script src="modules/Workflow2/resources/vtigerwebservices.js?v={$smarty.const.WORKFLOW2_VERSION}" type="text/javascript" charset="utf-8"></script>
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

    var productList = {$productlist|@json_encode};
    var taxlist = {$taxlist|@json_encode};
</script>
<div>
    <div style='padding:10px;font-size:13px;'>{$MOD.LBL_REVERSE_CREATE_INVENTORY_EXPLAIN}</div>
    <table width="100%" cellspacing="0" cellpadding="0" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="35%">{$MOD.LBL_RUN_WORKFLOW_WITH_NEW_RECORD}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                    <select name='task[exec_workflow]'>
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
    </table>
</div>


{if $new_module neq ''}
    {$setterContent}
{/if}

<h5>{vtranslate('new Products (additionally to the original products)', 'Settings:Workflow2')}</h5>

<div id='product_chooser'></div>
{if $new_module neq ''}
    {$ProductChooser}
{/if}
</form>
<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[new_module_setter]" id='new_module' value=''>
</form>



