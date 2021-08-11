<script src="modules/Workflow2/resources/vtigerwebservices.js" type="text/javascript" charset="utf-8"></script>

<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right">{$MOD.LBL_DUPLICATE_RECORD_OF_MODULE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select class="chzn-select" style="width:300px;" name='task[new_module]' onchange="jQuery('#new_module').val(jQuery(this).val());document.forms['hidden_related_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$avail_module item=module key=tabname}
                        <option {if $new_module == $tabname}selected='selected'{/if} value="{$tabname}">{$module}</option>
                    {/foreach}
                    </select>
            </td>
        <tr>
          <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap"><b><span style='color:red;'>*</span> {$MOD.LBL_USE_FOLLOWING_RECORDID}</b></td>
         <td width="15"></td>
          <td class='dvtCellInfo' style="padding:5px;">
              <div class="insertTextfield" data-name="task[recordid]" data-id="recordid" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}'>{$task.recordid}</div>
          </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_RUN_WORKFLOW_WITH_NEW_RECORD}</td>
            <td width="15"></td>
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
            <td class="dvtCellLabel" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td width="15"></td>
            <td class="dvtCellInfo"><input type="checkbox" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}></td>
        </tr>
    </table>
</div>

{if $new_module neq ''}
    {$setterContent}
{/if}


</form>
<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[new_module_setter]" id='new_module' value=''>
</form>