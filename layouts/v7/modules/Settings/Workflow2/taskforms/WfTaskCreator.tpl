<script src="modules/Workflow2/resources/vtigerwebservices.js" type="text/javascript" charset="utf-8"></script>
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" width="45%" align="right">{$MOD.LBL_CREATE_RECORD_OF_MODULE}</td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select class="select2" style="width:300px;" name='task[new_module]' onchange="jQuery('#new_module').val(jQuery(this).val());document.forms['hidden_related_form'].submit();">
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
                    <select class="select2" style="width:300px;" name='task[exec_workflow]'>
                        <option {if $task.exec_workflow == ""}selected='selected'{/if} value="">{$MOD.LBL_NO_WORKFLOW}</option>
                    {foreach from=$extern_workflows item=workflow}
                        <option {if $task.exec_workflow == $workflow.id}selected='selected'{/if} value="{$workflow.id}">{$workflow.title}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" width="45%" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <select name="task[redirectAfter]" class="select2" style="width:300px;">
                    <option value="0">{vtranslate('No', 'Settings:Workflow2')}</option>
                    <option value="1" {if $task.redirectAfter eq "1"}selected='selected'{/if}>{vtranslate('Yes, same window', 'Settings:Workflow2')}</option>
                    <option value="2" {if $task.redirectAfter eq "2"}selected='selected'{/if}>{vtranslate('Yes, within popup', 'Settings:Workflow2')}</option>
                </select>
                {*<input type="checkbox" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}>*}
            </td>
        </tr>
    </table>
</div>
{if $task.new_module neq ''}
    {$setterContent}
{/if}
{if $task.new_module neq ''}
<br/>
<h4>{vtranslate('duplicate record check', 'Settings:Workflow2')}</h4>
<hr/>
<p>
    {vtranslate('The task will check the configured fields, before creating a new record. If the task found already some records with equal fieldvalues, no new record will be created.', 'Settings:Workflow2')}
</p>

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="dvtCellLabel" width="25%" align="right">{vtranslate('choose fields to check', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select name="task[uniquecheck][]" class="select2" multiple="multiple" style="width:100%;">
                {foreach from=$fields key=label item=block}
                    <optgroup label="{$label}">
                    {foreach from=$block item=field}
                        {if $field->name neq "smownerid"}
                            <option value='{$field->name}'' {if in_array($field->name, $task.uniquecheck)}selected="selected"{/if}>{$field->label}</option>
                        {else}
                            <option value='assigned_user_id'>{$field->label}</option>
                        {/if}
                    {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" width="25%" align="right">{vtranslate('update these fields if duplicate found', 'Settings:Workflow2')}</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select name="task[updateexisting][]" class="select2" multiple="multiple" style="width:100%;">
                <option value="all-configured">{vtranslate('all configured fields', 'Settings:Workflow2')}</option>
                {foreach from=$fields key=label item=block}
                    <optgroup label="{$label}">
                    {foreach from=$block item=field}
                        {if $field->name neq "smownerid"}
                            <option value='{$field->name}' {if in_array($field->name, $task.updateexisting)}selected="selected"{/if}>{$field->label}</option>
                        {else}
                            <option value='assigned_user_id'>{$field->label}</option>
                        {/if}
                    {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </td>
    </tr>
</table>
{/if}
</form>

<form method="POST" name="hidden_related_form" action="#">
    <input type="hidden" name="task[new_module_setter]" id='new_module' value=''>
</form>