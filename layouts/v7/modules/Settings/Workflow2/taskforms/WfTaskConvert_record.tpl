<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" width="45%" align="right">{$MOD.LBL_CREATE_RECORD_OF_MODULE}</td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select class="chzn-select" style="width:300px;" name='task[new_module]' onchange="jQuery('#new_module').val(jQuery(this).val());document.forms['hidden_related_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$avail_module item=module key=tabname}
                        <option {if $task.new_module == $tabname}selected='selected'{/if} value="{$tabname}">{$module}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" width="45%" align="right">{$MOD.LBL_RUN_WORKFLOW_WITH_NEW_RECORD}</td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select class="chzn-select" style="width:300px;" name='task[exec_workflow]'>
                        <option {if $task.exec_workflow == ""}selected='selected'{/if} value="">{$MOD.LBL_NO_WORKFLOW}</option>
                    {foreach from=$extern_workflows item=workflow}
                        <option {if $task.exec_workflow == $workflow.id}selected='selected'{/if} value="{$workflow.id}">{$workflow.title}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" width="45%" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td class="dvtCellInfo">&nbsp;&nbsp;<input type="checkbox" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}></td>
        </tr>
    </table>
</div>
{if $task.new_module neq ''}
    {$setterContent}
{/if}

{if $productchooser eq true}
    <br/>
<table class="tableHeading" border="0"  width="100%" cellspacing="0" cellpadding="5">
    <tr>
        <td class="big" nowrap="nowrap">
            <strong>Products</strong>
        </td>
    </tr>
</table>
    <br/>
    {$ProductChooser}
{/if}
</form>
<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[new_module_setter]" id='new_module' value=''>
</form>