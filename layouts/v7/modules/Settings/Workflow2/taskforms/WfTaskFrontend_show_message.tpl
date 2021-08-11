<div>
    {if $isFrontendWorkflow neq true}
        <p class="alert alert-warning">{vtranslate('Please be sure to use the correct Task. There is another Block "Show Message" special for Default Workflows.<br/>This Task do <strong>NOT</strong> show messages during default workflows.','Settings:Workflow2')}</p>
    {/if}

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_TYPE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[type]">
                    <option value="info" {if $task.type == 'info'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_INFO}</option>
                    <option value="success"  {if $task.type == 'success'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_SUCCESS}</option>
                    <option value="error" {if $task.type == 'error'}selected='selected'{/if}>{$MOD.LBL_MESSAGE_TYPE_ERROR}</option>
                </select>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_TITLE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[subject]" data-id="subject">{$task.subject}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[message]" data-id="message">{$task.message}</div>
            </td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{$MOD.LBL_MESSAGE_POSITION}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name="task[position]">
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
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Timeout (ms)', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <div class="insertTextfield" data-name="task[timeout]" data-id="timeout">{$task.timeout}</div>
                <em>{vtranslate('empty = no timeout', 'Settings:Workflow2')}</em>
            </td>
        </tr>


    </table>
</div>