<div>
    {if $isFrontendWorkflow eq true}
        <p class="alert alert-warning">{vtranslate('Please be sure to use the correct Task. There is another Block "Show Message" special for Frontend Workflows.<br/>This Task do <strong>NOT</strong> show messages during EditView.','Settings:Workflow2')}</p>
    {/if}

    <table width="100%" cellspacing="0" cellpadding="0" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_TYPE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[type]" class="select2" style="width:150px;">
                    <option value="info" {if $task.type == 'info'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_INFO}</option>
                    <option value="success"  {if $task.type == 'success'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_SUCCESS}</option>
                    <option value="error" {if $task.type == 'error'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_ERROR}</option>
                </select>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_MESSAGE_TITLE', 'Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[subject]" data-id="subject">{$task.subject}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_MESSAGE', 'Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[message]" data-id="message">{$task.message}</div>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_MESSAGE_SHOW_ONCE', 'Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type="checkbox" name="task[show_once]" value="1" {if $task.show_once eq "1"}checked='checked'{/if}>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_MESSAGE_SHOW_UNTIL', 'Workflow2')}<br/>(YYYY-MM-DD HH:MM)</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[show_until]" data-id="show_until">{$task.show_until}</div>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_POSITION}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[position]" class="select2" style="width:150px;">
                    <option value="top" {if $task.position == 'top'}selected='selected'{/if}>{$MOD.LBL_POS_TOP}</option>
                    <option value="topLeft" {if $task.position == 'topLeft'}selected='selected'{/if}>{$MOD.LBL_POS_TOP} {$MOD.LBL_POS_LEFT}</option>
                    <option value="topCenter" {if $task.position == 'topCenter'}selected='selected'{/if}>{$MOD.LBL_POS_TOP} {$MOD.LBL_POS_CENTER}</option>
                    <option value="topRight" {if $task.position == 'topRight'}selected='selected'{/if}>{$MOD.LBL_POS_TOP} {$MOD.LBL_POS_RIGHT}</option>

                    <option value="centerLeft" {if $task.position == 'centerLeft'}selected='selected'{/if}>{$MOD.LBL_POS_CENTER} {$MOD.LBL_POS_LEFT}</option>
                    <option value="center" {if $task.position == 'center'}selected='selected'{/if}>{$MOD.LBL_POS_CENTER}</option>
                    <option value="centerRight" {if $task.position == 'centerRight'}selected='selected'{/if}>{$MOD.LBL_POS_CENTER} {$MOD.LBL_POS_RIGHT}</option>

                    <option value="bottomLeft" {if $task.position == 'bottomLeft'}selected='selected'{/if}>{$MOD.LBL_POS_BOTTOM} {$MOD.LBL_POS_LEFT}</option>
                    <option value="bottomCenter" {if $task.position == 'bottom'}selected='selected'{/if}>{$MOD.LBL_POS_BOTTOM} {$MOD.LBL_POS_CENTER}</option>
                    <option value="bottomRight" {if $task.position == 'bottomRight'}selected='selected'{/if}>{$MOD.LBL_POS_BOTTOM} {$MOD.LBL_POS_RIGHT}</option>
                    <option value="bottom" {if $task.position == 'bottom'}selected='selected'{/if}>{$MOD.LBL_POS_BOTTOM}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Assign message to record or user','Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[target]" onchange="jQuery('#targetUserChooser').css('visibility', this.value == 'user' ? 'visible' : 'hidden');jQuery('#targetRecordChooser').css('visibility', this.value == 'record' ? 'visible' : 'hidden');" class="select2" style="width:150px;">
                    <option value="record" {if empty($task.target) || $task.target == 'record'}selected='selected'{/if}>{vtranslate('Record', 'Settings:Workflow2')}</option>
                    <option value="user" {if $task.target == 'user'}selected='selected'{/if}>{vtranslate('User', 'Settings:Workflow2')}</option>
                </select>
            </td>
        </tr>
        <tr id="targetUserChooser" style="{if $task.target neq 'user'}visibility: hidden;{/if}">
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Assign message to this user','Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[targetUser]" class="select2" style="width:150px;">
                    <option value="current_user_id" {if empty($task.targetUser) || $task.targetUser == 'current_user'}selected='selected'{/if}>{vtranslate('LBL_CURRENT_USER', 'Settings:Workflow2')}</option>
                    <option value="assigned_user_id" {if $task.targetUser == 'assigned' || $task.targetUser == 'assigned_user_id'}selected='selected'{/if}>{vtranslate('assigned to User', 'Settings:Workflow2')}</option>
                    <option value="modifiedby" {if $task.targetUser == 'modified' || $task.targetUser == 'modifiedby'}selected='selected'{/if}>{vtranslate('modified by User', 'Settings:Workflow2')}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellInfo" align="right" width="25%">&nbsp;</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                &nbsp;
            </td>
        </tr>
        <tr id="targetRecordChooser" style="{if $task.target neq 'record'}visibility: hidden;{/if}">
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_TARGET}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[targetId]" data-id="targetId">{$task.targetId}</div>
            </td>
        </tr>

    </table>
</div>