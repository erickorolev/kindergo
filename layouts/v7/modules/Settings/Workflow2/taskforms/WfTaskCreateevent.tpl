
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" width="25%" align="right">{$MOD.LBL_REDIRECT_AFTER_WORKFLOW}</td>
            <td class="dvtCellInfo">&nbsp;&nbsp;<input type="checkbox" class="rcSwitch doInit" name="task[redirectAfter]" value="1" {if $task.redirectAfter eq "1"}checked='checked'{/if}></td>
        </tr>
        <tr>
            <td class="dvtCellLabel" width="25%" align="right">{vtranslate('Recurring Event', 'Settings:Workflow2')}</td>
            <td class="dvtCellInfo">&nbsp;&nbsp;<input type="checkbox" class="rcSwitch doInit" name="task[recurring]" value="1" {if $task.recurring eq "1"}checked='checked'{/if}></td>
        </tr>
        <tr class="ShowOnRecurringEvents">
            <td class="dvtCellLabel" width="25%" align="right"></td>
            <td class="dvtCellInfo">
                {assign var=QUALIFIED_MODULE value='Events'}
                <div class="" id="repeatUI" style="box-sizing: border-box;{if $task.recurring neq '1'}display:none;{/if}">
                    <div style="display:flex;flex-direction: row;align-content:stretch;">
                        <div style="padding:0 5px;">
                            <span class="alignMiddle">{vtranslate('LBL_REPEATEVENT', $QUALIFIED_MODULE )}</span>
                        </div>
                        <div style="padding:0 5px;">
                            <select class="select2 input-mini" name="task[repeat][repeat_frequency]">
                                {for $FREQUENCY = 1 to 14}
                                    <option value="{$FREQUENCY}" {if $task.repeat.repeat_frequency eq $FREQUENCY}selected{/if}>{$FREQUENCY}</option>
                                {/for}
                            </select>
                        </div>
                        <div style="padding:0 5px;">
                            <select class="select2 input-medium" name="task[repeat][recurring_type]" id="recurringType">
                                <option value="Daily" {if $task.repeat.recurring_type eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $QUALIFIED_MODULE)}</option>
                                <option value="Weekly" {if $task.repeat.recurring_type eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $QUALIFIED_MODULE)}</option>
                                <option value="Monthly" {if $task.repeat.recurring_type eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $QUALIFIED_MODULE)}</option>
                                <option value="Yearly" {if $task.repeat.recurring_type eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $QUALIFIED_MODULE)}</option>
                            </select>
                        </div>
                        <div style="padding:0 5px;">
                            <span class="alignMiddle">{vtranslate('LBL_UNTIL', $QUALIFIED_MODULE)}</span>
                        </div>
                        <div style="flex-grow:1;">
                            <div class="insertDatefield" data-name="task[repeat][calendar_repeat_limit_date]" data-id="calendar_repeat_limit_date">{$task.repeat.calendar_repeat_limit_date}</div>
                        </div>
                    </div>
                    <div class="{if $task.repeat.recurring_type eq 'Weekly'}show{else}hide{/if}" id="repeatWeekUI">
                        <label class="checkbox inline"><input name="task[repeat][sun_flag]" value="sunday" {if $task.repeat.sun_flag eq "sunday"}checked{/if} type="checkbox"/>{vtranslate('LBL_SM_SUN', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][mon_flag]" value="monday" {if $task.repeat.mon_flag eq "monday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_MON', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][tue_flag]" value="tuesday" {if $task.repeat.tue_flag eq "tuesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_TUE', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][wed_flag]" value="wednesday" {if $task.repeat.wed_flag eq "wednesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_WED', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][thu_flag]" value="thursday" {if $task.repeat.thu_flag eq "thursday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_THU', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][fri_flag]" value="friday" {if $task.repeat.fri_flag eq "friday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_FRI', $QUALIFIED_MODULE)}</label>
                        <label class="checkbox inline"><input name="task[repeat][sat_flag]" value="saturday" {if $task.repeat.sat_flag eq "saturday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_SAT', $QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="{if $task.repeat.recurring_type eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI">
                        <div class="row-fluid" style="padding-left:100px;">
                            <div class="span"><input type="radio" id="repeatDate" name="task[repeat][repeatMonth]" checked value="date" {if $task.repeat.repeatMonth eq 'date'} checked {/if}/></div>
                            <div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
                            <div class="span"><input type="text" id="repeatMonthDate" class="input-mini" name="task[repeat][repeatMonth_date]" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{$task.repeat.repeatMonth_date}"/></div>
                            <div class="span alignMiddle">{vtranslate('LBL_DAY_OF_THE_MONTH', $QUALIFIED_MODULE)}</div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row-fluid" id="repeatMonthDayUI" style="padding-left:100px;">
                            <div class="span"><input type="radio" id="repeatDay" name="task[repeat][repeatMonth]" value="day" {if $task.repeat.repeatMonth eq 'day'} checked {/if}/></div>
                            <div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
                            <div class="span">
                                <select id="repeatMonthDayType" class="select2 input-small" name="task[repeat][repeatMonth_daytype]">
                                    <option value="first" {if $task.repeat.repeatMonth_daytype eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $QUALIFIED_MODULE)}</option>
                                    <option value="last" {if $task.repeat.repeatMonth_daytype eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $QUALIFIED_MODULE)}</option>
                                </select>
                            </div>
                            <div class="span">
                                <select id="repeatMonthDay" class="select2 input-medium" name="task[repeat][repeatMonth_day]">
                                    <option value=1 {if $task.repeat.repeatMonth_day eq 1} selected {/if}>{vtranslate('LBL_DAY1', $QUALIFIED_MODULE)}</option>
                                    <option value=2 {if $task.repeat.repeatMonth_day eq 2} selected {/if}>{vtranslate('LBL_DAY2', $QUALIFIED_MODULE)}</option>
                                    <option value=3 {if $task.repeat.repeatMonth_day eq 3} selected {/if}>{vtranslate('LBL_DAY3', $QUALIFIED_MODULE)}</option>
                                    <option value=4 {if $task.repeat.repeatMonth_day eq 4} selected {/if}>{vtranslate('LBL_DAY4', $QUALIFIED_MODULE)}</option>
                                    <option value=5 {if $task.repeat.repeatMonth_day eq 5} selected {/if}>{vtranslate('LBL_DAY5', $QUALIFIED_MODULE)}</option>
                                    <option value=6 {if $task.repeat.repeatMonth_day eq 6} selected {/if}>{vtranslate('LBL_DAY6', $QUALIFIED_MODULE)}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
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
