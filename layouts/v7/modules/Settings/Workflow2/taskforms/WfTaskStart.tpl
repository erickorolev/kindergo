<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable small">
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_RUNTIME_WORKFLOW}</td>
		<td class='dvtCellInfo'>
			<select name="task[start]" class="select2" style="width:350px;">
				<option value="synchron" {if $task.start eq "synchron"}selected='selected'{/if}>{$MOD.LBL_SYNCHRONOUS}</option>
				<option value="asynchron" {if $task.start eq "asynchron"}selected='selected'{/if}>{$MOD.LBL_ASYNCHRONOUS}</option>
			</select>
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_START_CONDITION}</td>
		<td class='dvtCellInfo'>
			<select name="task[runtime]" id="runtimeSelection" class="select2" style="width:350px;float:left;margin-right:10px;">
                {foreach from=$trigger key=keyLabel item=triggerlist}
					<optgroup label="{$keyLabel}">
						{foreach from=$triggerlist item=triggerData key=trigger}
							<option data-description="{$triggerData.description}" value="{$trigger}" {if $task.runtime eq $trigger}selected="selected"{/if}>{$triggerData.label}</option>
						{/foreach}
					</optgroup>
                {/foreach}
				{*{html_options options=$trigger selected=$task.runtime}*}
			</select>
			<div id="triggerDescription"></div>
            {*<img src='modules/Workflow2/icons/add.png' style="height:18px;margin-top:3px;margin-left:5px;cursor:pointer;" onclick="window.open('index.php?module=Workflow2&action=settingsTrigger&parenttab=Settings');">*}
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_PARALLEL_ALLOWED}</td>
		<td class='dvtCellInfo'>
			<select name="task[runtime2]" class="select2" style="width:350px;">
				<option value="2" {if $task.runtime2 eq "2"}selected='selected'{/if}>{$MOD.LBL_PARALLEL_NOT_ALLOW}</option>
				<option value="1" {if $task.runtime2 eq "1"}selected='selected'{/if}>{$MOD.LBL_PARALLEL_ALLOW}</option>
			</select>
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('use this Timezone for workflow', 'Settings:Workflow2')}</td>
		<td class='dvtCellInfo'>
			<select name="task[timezone]" class="select2" style="width:350px;" placeholder="{vtranslate('Do not modify timezone', 'Settings:Workflow2')}">
				<option value=""></option>
				{foreach from=$timezones item=label key=tzkey}
					<option value="{$tzkey}" {if $task.timezone eq $tzkey}selected="selected"{/if}>{vtranslate($label, 'Users')}</option>
				{/foreach}
			</select>
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap"><input type="checkbox" name="task[execute_only_once_per_record]" value="1" {if $task.execute_only_once_per_record eq true}checked="checked"{/if} /></td>
		<td class='dvtCellInfo'>
			{vtranslate('LBL_EXECUTE_WORKFLOW_ONLY_ONCE_PER_RECORD','Settings:Workflow2')}
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap"><input type="checkbox" name="task[withoutrecord]" value="1" {if $task.withoutrecord eq true}checked="checked"{/if} /></td>
		<td class='dvtCellInfo'>
			{vtranslate('allow execution without a related record','Settings:Workflow2')} (<strong>{vtranslate('read documentation for more information', 'Settings:Workflow2')}</strong>)
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' valign="top" align="right" width=15% nowrap="nowrap"><input type="checkbox" name="task[collection_process]" value="1" onclick="jQuery('#collection_variable_div').css('display', jQuery(this).prop('checked') ? 'block' : 'none');" {if $task.collection_process eq true}checked="checked"{/if} /></td>
		<td class='dvtCellInfo'>
			{vtranslate('execute process only once with all checked records in listview','Settings:Workflow2')} (<strong>{vtranslate('read documentation for more information', 'Settings:Workflow2')}</strong>)
            <div style="display:{if $task.collection_process eq true}block{else}none{/if}" id="collection_variable_div">
                {vtranslate('Inject all Record IDs within this $env Variable', 'Workflow2')}: <input type="text" name="task[collection_variable]" value="{$task.collection_variable}" />
            </div>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap"><input type="checkbox" name="task[nologging]" value="1" {if $task.nologging eq true}checked="checked"{/if} /></td>
		<td class='dvtCellInfo'>
            {vtranslate('Do not store execution log of this workflow in statistics.', 'Settings:Workflow2')}
		</td>
	</tr>
	<tr>

<!--
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_USER_EXECUTE}</td>
		<td class='dvtCellInfo'>
			<select name="task[execution_user]">
                <option value="0x0" {if $task.execution_user eq "0x0"}selected='selected'{/if}>{$MOD.LBL_START_USER}</option>
			</select>
		</td>
	</tr>
    -->
    </table>
{if $task.runtime != 'WF2_FRONTENDTRIGGER'}
	<h5 class="big" style="padding:5px;"><strong>{$MOD.HEAD_STARTVARIABLE_REQUEST}</strong></h5>
	<p style="margin:5px;" class="small">{$MOD.INFO_STARTVARIABLE}</p>
	<div style='margin:2px;border:1px solid #ccc;padding:3px;'>
	{$formGenerator}
	</div>

{else}
	<br/>
	<p style="text-align:center;font-style: italic;">Request values is not yet supported in Frontend Worklfows.</p>
{/if}

<div class="big" style="padding:5px;line-height:30px;"><strong>{$MOD.HEAD_VISIBLE_CONDITION}</strong>
	<label class="pull-right">
		<input style="display:inline;" type="checkbox" class="rcSwitch doInit" name="task[view_condition_lv]" {if $task.view_condition_lv eq '1'}checked="checked"{/if} value="1"/>
		{vtranslate('Apply this condition in any situation to prevent execution', 'Settings:Workflow2')}
		<a href="https://support.redoo-networks.com/documentation/tasks/start/" target="_blank">
			<img src='modules/Workflow2/icons/question.png' border="0">
		</a>
	</label>
</div>

{$conditionalContent}