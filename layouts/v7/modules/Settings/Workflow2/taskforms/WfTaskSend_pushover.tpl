<p>
    {vtranslate('LBL_PUSHOVER_INTRO','Settings:Workflow2')}
</p>
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="dvtCellLabel" width="30%" align="right">{vtranslate('LBL_PUSHOVER_USERID','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextfield" data-name="task[userkey]" data-id="number">{$task.userkey}</div>
                </td>
            </tr>
        <tr>
            <td class="dvtCellLabel" align="right">{vtranslate('LBL_PUSHOVER_TARGET_DEVICE','Settings:Workflow2')} (default=all)</td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[device]" data-id="number">{$task.device}</div>
            </td>
        </tr>

            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('Priority','Vtiger')} <a href="https://pushover.net/api#priority" target="_blank">About Priority Level</a></td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select name="task[priority]">
                        <option value="0" {if empty($task.priority)}selected="selected"{/if}>Normal priority</option>
                        <option value="-2" {if $task.priority eq "-2"}selected="selected"{/if}>-- Lowest priority</option>
                        <option value="-1" {if $task.priority eq "-1"}selected="selected"{/if}>- Low Priority</option>
                        <option value="1" {if $task.priority eq "1"}selected="selected"{/if}>+ High Priority</option>
                        <option value="2" {if $task.priority eq "2"}selected="selected"{/if}>++ Emergency Priority with Acknowledgment</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('URL to send with the Message','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextfield" data-name="task[url]" data-id="url">{$task.url}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('When sending URL, optionally set custom Link Label','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextfield" data-name="task[url_title]" data-id="url_title">{$task.url_title}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('Notification sound','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <select name="task[sound]">
                        <option value="">Default sound of Receiver</option>
                        {foreach from=$sounds item=label key=soundkey}
                        <option value="{$soundkey}" {if $task.sound eq $soundkey}selected="selected"{/if}>{$label}</option>
                        {/foreach}
                    </select>

                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('LBL_EMAIL_SUBJECT','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextfield" data-name="task[subject]" data-id="number">{$task.subject}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('LBL_MESSAGE','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextarea" data-name="task[content]" data-id="sms_text">{$task.content|@stripslashes}</div>
                </td>
            </tr>
        <tr>
            <td colspan=2><br/><br/></td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right"><em>{vtranslate('LBL_PUSHOVER_APPKEY_OPTIONAL','Settings:Workflow2')}</em></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[appkey]" data-id="number">{$task.appkey}</div>
            </td>
        </tr>

            <tr>
                <td class="dvtCellLabel" align="right"></td>
                <td class="dvtCellInfo" align="left" style="padding:5px;" id="buchstabenCounter">
                </td>
            </tr>

    </table>
</div>
