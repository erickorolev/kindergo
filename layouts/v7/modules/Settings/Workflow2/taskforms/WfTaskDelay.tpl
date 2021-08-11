<table border="0" cellpadding="5" cellspacing="0" width="100%" class="small">
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_DATE_BASE}</td>
		<td class='dvtCellInfo' width=35></td>
		<td class='dvtCellInfo'>
			<select name="task[baseTime]" class="chzn-select-nosearch"  style="width:300px;">
				<option value="now()" {if $task.baseTime eq "now()"}selected='selected'{/if}>{$MOD.LBL_CURRENT_TIME}</option>
                {foreach from=$datefields item=item key=key}
                    <option value="{$item->name}" {if $item->name eq $task.baseTime}selected='selected'{/if}>{$item->label}</option>
                {/foreach}
			</select>
            <input type="checkbox" name="task[update_basefield]" value="1" {if $task.update_basefield eq "1"}checked='checked'{/if}> {$MOD.LBL_UPDATE_DYNAMIC_BASETIME}
		</td>
	</tr>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate("LBL_WAIT_MIN", "Settings:Workflow2")}</td>
		<td class='dvtCellInfo' width=25 align="center"><input type="checkbox" name="task[waitMin]" value="1" {if $task.waitMin eq "1"}checked='checked'{/if}></td>
		<td class='dvtCellInfo'>
			<div class="insertTextfield" style="width:200px;display:inline-block;" data-name="task[waitMinValue]" data-id="waitMinValue">{$task.waitMinValue|@htmlentities}</div>
			{*<input type="text" name="task[waitMinValue]" value="{$task.waitMinValue}" id="task_waitMinValue" class="defaultTextfield" style='width: 120px;font-size:11px;padding:3px;'>*}
            {*<img src='modules/Workflow2/icons/templatefield.png' style='margin-bottom:-7px;cursor:pointer;' onclick="insertTemplateField('task_waitMinValue','([source]: ([module]) [destination])')">*}
			<select name="task[waitMinCat]" class="chzn-select-nosearch"  style="width:100px;">
				<option value="minutes" {if $task.waitMinCat eq "minutes"}selected='selected'{/if}>{$MOD.LBL_MINUTES}</option>
				<option value="hours" {if $task.waitMinCat eq "hours"}selected='selected'{/if}>{$MOD.LBL_HOURS}</option>
				<option value="days" {if $task.waitMinCat eq "days"}selected='selected'{/if}>{$MOD.LBL_DAYS}</option>
				<option value="weeks" {if $task.waitMinCat eq "weeks"}selected='selected'{/if}>{$MOD.LBL_WEEKS}</option>
			</select>
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_WAIT_UNTIL_NEXT_MONTHDAY}</td>
		<td class='dvtCellInfo' width=25 align="center"><input type="checkbox" name="task[waitUntilMonthDay]" value="1" {if $task.waitUntilMonthDay eq "1"}checked='checked'{/if}></td>
		<td class='dvtCellInfo'>
			<div class="insertTextfield" style="width:200px;display:inline-block;" data-name="task[waitUntilMonthDayValue]" data-id="waitUntilMonthDayValue">{$task.waitUntilMonthDayValue|@htmlentities}</div>
			{*<input type="text" alt="" title="{$MOD.LBL_WAIT_UNTIL_NEXT_MONTHDAY_TITLE}" name="task[waitUntilMonthDayValue]" value="{$task.waitUntilMonthDayValue}" id="task_waitUntilMonthDayValue" class="defaultTextfield" style='width: 120px;font-size:11px;padding:3px;'>*}
            {*<img src='modules/Workflow2/icons/templatefield.png' style='margin-bottom:-7px;cursor:pointer;' onclick="insertTemplateField('task_waitUntilMonthDayValue','([source]: ([module]) [destination])')">*}
            {vtranslate('LBL_WAIT_UNTIL_NEXT_MONTHDAY2', 'Settings:Workflow2')}
            <select class="chzn-select-nosearch" name="task[waitUntilMonthDayGroup]" style="width:100px;">
				<!--<option value="next_week" {if $task.waitUntilMonthDayGroup eq "next_week"}selected='selected'{/if}>{$MOD.LBL_NEXT_WEEK}</option>-->
				<option value="next_month" {if $task.waitUntilMonthDayGroup eq "next_month"}selected='selected'{/if}>{$MOD.LBL_NEXT_MONTH}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_WAIT_UNTIL_NEXT}</td>
		<td class='dvtCellInfo' width=25 align="center"><input type="checkbox" {if $task.waitUntilWeekDay eq "1"}checked='checked'{/if} name="task[waitUntilWeekDay]" value="1"></td>
		<td class='dvtCellInfo'>
			<select name="task[waitUntilWeekDayValue][]" multiple="true"class="chzn-select-nosearch" style="width:650px;">
				{foreach from=$weekdays item=day key=key}
				<option value="{$key}" {if is_array($task.waitUntilWeekDayValue) && $key|in_array:$task.waitUntilWeekDayValue}selected='selected'{/if}>{$day}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_WAIT_UNTIL_TIME}</td>
		<td class='dvtCellInfo' width=25 align="center"><input type="checkbox" name="task[waitUntilTime]" value="1" {if $task.waitUntilTime eq "1"}checked='checked'{/if}></td>
		<td class='dvtCellInfo'>
			<select name="task[waitUntilTimeHour]" class="select2" style="width:50px;">
				{foreach from=$hours item=value}
				<option value="{$value}" {if $task.waitUntilTimeHour eq $value}selected='selected'{/if}>{$value}</option>
				{/foreach}
			</select>&nbsp;:&nbsp;
			<select name="task[waitUntilTimeMinutes]" class="select2" style="width:50px;">
				{foreach from=$minutes item=value}
				<option value="{$value}" {if $task.waitUntilTimeMinutes eq $value}selected='selected'{/if}>{$value}</option>
				{/foreach}
			</select>	({$MOD.LBL_CURRENT_TIME}: <b>{$smarty.now|date_format:"%T"}</b> {$MOD.LBL_USED_TZ}: <b>{$smarty.now|date_format:"%Z"}</b> }
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' valign="top" align="right" width=15% nowrap="nowrap">{$MOD.LBL_WAIT_UNTIL_FUNCTION}</td>
		<td class='dvtCellInfo' width=25 valign="top" align="center"><input type="checkbox" name="task[checkWaitUntilFunction]" value="1" {if $task.checkWaitUntilFunction eq "1"}checked='checked'{/if}></td>
		<td class='dvtCellInfo'>
            <textarea style="width:460px;height:80px;" name="task[waitUntilFunction]" class="customFunction" id="waitUntilFunction">{$task.waitUntilFunction}</textarea>
            <img src='modules/Workflow2/icons/templatefieldPHP.png' style='margin-bottom:-7px;cursor:pointer;' onclick="insertTemplateField('waitUntilFunction')"><br>
            <span style="font-size:10px;font-style:italic;">{$MOD.LBL_RETURN_UNIX_TIMESTAMP}</span>
		</td>
	</tr>
</table>
<style type="text/css">
	table td {
		padding:5px 0;
	}
</style>