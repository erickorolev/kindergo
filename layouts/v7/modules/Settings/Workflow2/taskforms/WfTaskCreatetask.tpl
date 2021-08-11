<script src="modules/Workflow2/resources/vtigerwebservices.js" type="text/javascript" charset="utf-8"></script>
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" width="25%" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td class="dvtCellInfo">&nbsp;&nbsp;<input type="checkbox" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}></td>
        </tr>
    </table>
    <hr/>
</div>
{$setterContent}

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
