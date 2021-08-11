<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr height="35">
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_BACKGROUND_COLOR_ROW}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[backgroundcolor]" data-id="backgroundcolor" data-options=''>{$task.backgroundcolor}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_INFO_MESSAGE}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[infomessage]" data-id="infomessage" data-options=''>{$task.infomessage}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('show request to the following users', 'Settings:Workflow2')}:</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[targets][]" multiple="multiple" class="select2" style="width:400px;">
                    <optgroup label="{vtranslate('LBL_USER', 'Vtiger')}">
                        {foreach from=$targets.users key=id item=item}
                            <option value="{$id}" {if $item.1 eq true}selected="selected"{/if}>{vtranslate('LBL_USER', 'Vtiger')}: {$item.0}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_GROUP', 'Vtiger')}">
                        {foreach from=$targets.groups key=id item=item}
                            <option value="{$id}" {if $item.1 eq true}selected="selected"{/if}>{vtranslate('LBL_GROUP', 'Vtiger')}: {$item.0}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_ROLES', 'Settings:Vtiger')}">
                        {foreach from=$targets.roles key=id item=item}
                            <option value="{$id}" {if $item.1 eq true}selected="selected"{/if}>{vtranslate('LBL_ROLES', 'Settings:Vtiger')}: {$item.0}</option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_FIELDS', 'Settings:Vtiger')}">
                        {foreach from=$targets.fields key=id item=item}
                            <option value="{$id}" {if $item.1 eq true}selected="selected"{/if}>{$item.0}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </td>
        </tr>
        <tr height="35">
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_DIRECT_RUN}</td>
            <td width=15></td>            <td class="dvtCellInfo" align="left">
                <input type="checkbox" name="task[rundirect]" value="1" {if $task.rundirect eq "1"}checked='checked'{/if}>
            </td>
        </tr>
        <tr height="35">
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('use Timeout','Workflow2')}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left">
                <input type="checkbox" name="task[use_timeout]" value="1" onclick="jQuery('#use_timeout_config').css('display',!jQuery(this).prop('checked')?'none':'')" {if $task.use_timeout eq "1"}checked='checked'{/if}>
            </td>
        </tr>
        <tr height="35" style="{if $task.use_timeout neq '1'}display:none;{/if}" id="use_timeout_config">
            <td class="dvtCellLabel" align="right" width="15%">{vtranslate('Timeout configuration','Workflow2')}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left">
                {vtranslate('use','Workflow2')}
                    <select name="task[timeout_output]" style="width:100px;">
                        <option value='ok' {if $task.timeout_output eq 'ok'}selected="selected"{/if}>Ok</option>
                        <option value='rework' {if $task.timeout_output eq 'rework'}selected="selected"{/if}>Rework</option>
                        <option value='decline' {if $task.timeout_output eq 'decline'}selected="selected"{/if}>Decline</option>
                    </select>
                {vtranslate('after','Workflow2')}
                    <input type="text" name="task[timeout_value]" value="{$task.timeout_value}" style="width:100px;"/>
                    <select name="task[timeout_value_mode]" class="chzn-select-nosearch"  style="width:100px;">
                        <option value="minutes" {if $task.timeout_value_mode eq "minutes"}selected='selected'{/if}>{$MOD.LBL_MINUTES}</option>
                        <option value="hours" {if $task.timeout_value_mode eq "hours"}selected='selected'{/if}>{$MOD.LBL_HOURS}</option>
                        <option value="days" {if $task.timeout_value_mode eq "days"}selected='selected'{/if}>{$MOD.LBL_DAYS}</option>
                        <option value="weeks" {if $task.timeout_value_mode eq "weeks"}selected='selected'{/if}>{$MOD.LBL_WEEKS}</option>
                    </select> {vtranslate('of no action','Workflow2')}
            </td>
        </tr>

        <tr>
            <td colspan="3">
                <table class="tableHeading" border="0"  width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td class="big" nowrap="nowrap">
                            <strong>{$MOD.LBL_BUTTON_TEXTS}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_OK}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[btn_accept]" data-id="btn_accept" data-options=''>{$task.btn_accept}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_REWORK}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[btn_rework]" data-id="btn_rework" data-options=''>{$task.btn_rework}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_DECLINE}</td>
            <td width=15></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[btn_decline]" data-id="btn_decline" data-options=''>{$task.btn_decline}</div>
            </td>
        </tr>

    </table>
</div>