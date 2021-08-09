<div>
    <table width="100%" cellspacing="0" cellpadding="0" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Add record to this module', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name='task[target_module]' class="select2" style="width:400px;" onchange="jQuery('#save').trigger('click');">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$EntityModules item=module}
                        <option {if $task.target_module == $module.0}selected='selected'{/if} value="{$module.0}">{$module.1}</option>
                    {/foreach}
                </select>
                <div class="alert alert-info" style="display:inline-block;margin:0;">
                    {vtranslate('The module show the Relation ListView in frontend.', 'Settings:Workflow2')}
                </div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Add record of this module', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name='task[related_module]' class="select2" style="width:400px;" onchange="jQuery('#save').trigger('click');">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$related_modules item=module}
                        <option {if $related_module == $module.module_name}selected='selected'{/if} value="{$module.module_name}">{$module.label}</option>
                    {/foreach}
                </select>
                <div class="alert alert-info" style="display:inline-block;margin:0;">
                    {vtranslate('This module provide the records, which will be referenced.', 'Settings:Workflow2')}
                </div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('At most add this number of records', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="defaultTextfield" id='found_rows' value="{$task.found_rows}" style="width:50px;margin:5px 0;">
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Relate records to this target record', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[target]" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}' data-id="target" data-placeholder="$crmid">{$task.target}</div>
            </td>
        </tr>
    </table>
</div>

{if !empty($related_module)}
    <br/>
    <h4>{vtranslate('Search the records you want to add', 'Settings:Workflow2')}</h4>
    <hr/>
    {$conditionalContent}
{/if}
