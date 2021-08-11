<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
	{if $showprovider eq true}
		<tr>
			<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Choose connection', 'Settings:Workflow2')}:</td>
			<td class='dvtCellInfo'>
				<select name="task[provider]" class="select2" style="width:300px;">
					{foreach from=$provider key=providerid item=label}
						<option value="{$providerid}" {if $task.provider eq $providerid}selected="selected"{/if}>{$label}</option>
					{/foreach}
				</select>
				<a href="index.php?module=Workflow2&view=ProviderManager&parent=Settings" target="_blank"><img src="modules/Workflow2/icons/question.png" /></a>
			</td>
		</tr>
	{/if}
	{if !empty($calendar)}
    <tr>
   		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_CALENDAR', 'Settings:Workflow2')}:</td>
   		<td class='dvtCellInfo'>
			<select name="task[calendar]" class="select2" style="width:300px;">
               {html_options  options=$calendar selected=$task.calendar}
			</select>
   		</td>
   	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_EVENT_TITLE', 'Settings:Workflow2')}:</td>
		<td class='dvtCellInfo'>
			<div class="insertTextfield" data-name="task[eventtitle]" data-id="eventtitle">{$task.eventtitle|@htmlentities}</div>
		</td>
	</tr>

    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_EVENT_DESCR', 'Settings:Workflow2')}:</td>
		<td class='dvtCellInfo'>
			<div class="insertTextarea" data-name="task[eventdescr]" data-id="eventdescr">{$task.eventdescr|@htmlentities}</div>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Location', 'Settings:Workflow2')}:</td>
		<td class='dvtCellInfo'>
			<div class="insertTextfield" data-name="task[location]" data-id="location">{$task.location|@htmlentities}</div>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_PRIVACY', 'Settings:Workflow2')}:</td>
		<td class='dvtCellInfo'>
			{html_options name="task[privacy]" options=$privacySettings selected=$task.privacy}
		</td>
	</tr>
</table>
<div style="display:flex;flex-direction: row;">
	<div style="width:50%;">
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
			<tr>
				<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_EVENT_START_DATE', 'Settings:Workflow2')}:</td>
				<td class='dvtCellInfo'>
					<div class="insertTextfield" data-name="task[eventstartdate]" data-id="eventstartdate">{$task.eventstartdate|@htmlentities}</div>
				</td>
			</tr>
			<tr>
				<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_EVENT_START_TIME', 'Settings:Workflow2')}:</td>
				<td class='dvtCellInfo'>
					<div class="insertTextfield" data-name="task[eventstarttime]" data-id="eventstarttime">{$task.eventstarttime|@htmlentities}</div>
				</td>
			</tr>
			<tr>
				<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_EVENT_DURATION', 'Settings:Workflow2')}:</td>
				<td class='dvtCellInfo'>
					<div class="insertTextfield" data-name="task[eventduration]" data-id="eventduration">{$task.eventduration|@htmlentities}</div>
				</td>
			</tr>
		</table>
	</div>
	<div style="width:50%;">
		<p style="text-align:right;font-size:10px;line-height:29px;"><strong>{vtranslate('Optionally you could manually set Enddate and Endtime.  Duration will be ignored in this case.', 'Settings:Workflow2')}</strong></p>
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
			<tr>
				<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('End date (yyyy-mm-dd)', 'Settings:Workflow2')}:</td>
				<td class='dvtCellInfo'>
					<div class="insertTextfield" data-name="task[eventenddate]" data-id="eventenddate">{$task.eventenddate|@htmlentities}</div>
				</td>
			</tr>
			<tr>
				<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('End time (hh:mm)', 'Settings:Workflow2')}:</td>
				<td class='dvtCellInfo'>
					<div class="insertTextfield" data-name="task[eventendtime]" data-id="eventendtime">{$task.eventendtime|@htmlentities}</div>
				</td>
			</tr>
		</table>
	</div>
</div>
{$mainconfig}

{/if}

