<p class="alert alert-warning">
    {vtranslate('LBL_HINT_TASK_REMOVE', 'Settings:Workflow2')}
</p>
<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" valign="top" width="25%" style="line-height:28px;">{vtranslate('Delete the Record with this ID', 'Settings', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[crmid]" data-id="crmid">{if empty($task.crmid)}$crmid{else}{$task.crmid}{/if}</div>
                <br/>
                <div class="alert alert-info">
                    {vtranslate('If you want to delete the current record, keep the default value for this field!', 'Settings:Workflow2')}
                </div>

        </tr>
    </table>
</div>