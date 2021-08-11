<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
	<tr>
		<td class="dvtCellLabel" width="25%" align="right">MySQL Provider:</td>
		<td class="dvtCellInfo" align="left" style="padding:5px;">
			<select name="task[provider]" required="required" class="select2" style="width:300px;">
				{html_options options=$available_providers selected=$task['provider']}
			</select>
			<a href="index.php?module=Workflow2&view=ProviderManager&parent=Settings" target="_blank"><img src="modules/Workflow2/icons/question.png" /></a>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.CHECK_THIS_QUERY}:</td>
		<td class='dvtCellInfo'>
			<div class="insertTextarea" data-name="task[query]" data-id="query">{$task.query}</div>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_MYSQL_QUERY_ENV_VARIABLE}:</td>
		<td class='dvtCellInfo'>
			$env["<input type="text" required="required" class="defaultTextfield" name="task[envvar]" value="{$task.envvar}" />"] = mysql_fetch_assoc($result);
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Result Mode', 'Settings:Workflow2')}:</td>
		<td class='dvtCellInfo'>
			<select name="task[resultmode]">
				<option value="single">{vtranslate('Single Row Result', 'Settings:Workflow2')}</option>
				<option value="multi" {if $task.resultmode eq 'multi'}selected="selected"{/if}>{vtranslate('Multi Row Result', 'Settings:Workflow2')}</option>
			</select>
		</td>
	</tr>
</table>