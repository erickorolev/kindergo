<div>
    <p class="alert alert-info">{vtranslate('The module could only copy relations, related by <strong>n:m relations</strong> by using the vtiger_crmentityrel table.', 'Settings:Workflow2')}</p>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">Source and Target Record is in this module</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select class="select2" id="search_module"  name='task[search_module]' style="width:250px;" onchange="submitConfigForm();">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$related_modules item=module key=tabid}
                        <option {if $task.search_module == $tabid}selected='selected'{/if} value="{$tabid}">{$module.1}</option>
                    {/foreach}
                    <option value="Custom" {if $task.search_module eq 'Custom'}selected='selected'{/if}>{vtranslate('Custom Data', 'Settings:Workflow2')}</option>
                </select>
            </td>
        </tr>
        {if !empty($task.search_module)}
        <tr>
            <td colspan="3" height="20">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Add record of this module', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name='task[relations][]' multiple="multiple" class="chzn-select" data-placeholder="Select Relations to copy" style="width:400px;">
                    {foreach from=$available_relations item=module}
                        <option {if in_array($module.relation_id, $task.relations)}selected='selected'{/if} value="{$module.relation_id}">{$module.label}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="20">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Copy FROM this Record ID', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[crmid_src]" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}' data-id="crmid_src" data-placeholder="$crmid">{$task.crmid_src}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="20">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Copy TO this Record ID', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[crmid_dest]" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}' data-id="crmid_dest" data-placeholder="">{$task.crmid_dest}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="20">&nbsp;</td>
        </tr>
        {/if}
    </table>
</div>
