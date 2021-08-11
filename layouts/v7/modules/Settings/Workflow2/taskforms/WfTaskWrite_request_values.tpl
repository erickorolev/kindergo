<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Module of target Record', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo' width=85 style="text-align: center;"></td>
		<td class='dvtCellInfo'>
            <select name="task[targetModule]" onchange="jQuery('#save').trigger('click');" class="select2" style="width:300px;">
                {foreach from=$modules item=module}
                <option value="{$module.0}" {if $task.targetModule eq $module[0]}selected="selected"{/if}>{$module.1}</option>
                {/foreach}
            </select>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('ID of target Record (emty values = current record)', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo' width=85 style="text-align: center;"></td>
		<td class='dvtCellInfo'>
            <div class="insertTextfield" data-name="task[targetid]" data-id="targetid">{$task.targetid}</div>
            <span>{vtranslate('Enter "new" to create a new record if duplicate check do not match. Be sure mandatory fields are set.','Settings:Workflow2')}</span>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Array-key which contain values (emty values = in main scope)', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo' width=85 style="text-align: center;"></td>
		<td class='dvtCellInfo'>
            <div class="insertTextfield" data-name="task[scope]" data-id="scope" data-placeholder="Scope, which should be used">{$task.scope}</div>
		</td>
	</tr>
</table>

<p>{vtranslate('choose which variables could be submit', 'Settings:Workflow2')}</p>
<div style="display:flex;align-items: stretch;flex-direction: row;flex-wrap: wrap;">
    {foreach from=$fields key=blockLabel item=blockFields}
        <div style="width:32%;margin-right:1%;box-sizing: border-box;border:1px solid #6699cc;">
            <h4 style="background-color:#6699cc;color:#fff;line-height:28px;margin-bottom:5px;padding-left:5px;"><input type="checkbox"  style="margin-right:10px;" value="1" class="headLineBlock" /> {$blockLabel}</h4>
            {foreach from=$blockFields item=field}
            <p style="padding-left:5px;">
                <label><input type="checkbox" {if $task.fields[$field->name] eq '1'}checked="checked"{/if}  name="task[fields][{$field->name}]" style="margin-right:10px;float:left;" value="1"/>{$field->label} <em>/ {$field->name}</em></label>
            </p>
            {/foreach}
        </div>
    {/foreach}
</div>
<br/>
<h4>{vtranslate('duplicate record check', 'Settings:Workflow2')}</h4>
<hr/>
<p>
    {vtranslate('If you enter "new" into the target Record ID, you could use this duplicate record check to update existing records, instead of creating duplicates.', 'Settings:Workflow2')}
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
