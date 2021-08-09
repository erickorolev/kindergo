<div>
    <h4>{vtranslate('Load comments')}</h4>
    <hr/>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Comments related to', 'Settings:Workflow2')}<br/><br/></td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[relatedto]" data-id="count">{$task.relatedto|stripslashes}</div>
                {vtranslate('empty or $crmid for current record', 'Settings:Workflow2')}
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Sort comments by', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name='task[sort]' class="chzn-select" style="width:400px;">
                    <option {if $task.sort == 'date#desc'}selected='selected'{/if} value="date#desc">Date DESC</option>
                    <option {if $task.sort == 'date#asc'}selected='selected'{/if} value="date#asc">Date ASC</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Comments to choose', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <select name='task[src]' class="chzn-select" style="width:400px;">
                    <option {if $task.src == 'all'}selected='selected'{/if} value="all">All</option>
                    <option {if $task.src == 'users'}selected='selected'{/if} value="users">From Vtiger Users</option>
                    <option {if $task.src == 'customers'}selected='selected'{/if} value="customers">From Customers</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('how much comments?', 'Settings:Workflow2')}<br/><br/></td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[count]" data-id="count">{$task.count|stripslashes}</div>
                {vtranslate('0 or empty to unlimited', 'Settings:Workflow2')}
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">Template</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextarea" data-name="task[text]" data-id="text" data-options='{ldelim}"module":"ModComments"{rdelim}'>{$task.text|stripslashes}</div>
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Divider between comments', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <div class="insertTextfield" data-name="task[divider]" data-placeholder="{vtranslate('empty line', 'Settings:Workflow2')}" data-id="divider">{$task.divider}</div>
                \n = Text New Line, &lt;br&gt; = HTML New line
            </td>
        </tr>

        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>

        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_REGEX_TARGET_ENV_VAR', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                $env[<input type="text" name="task[envid]" class="defaultTextfield" value="{$task.envid}"/>]
            </td>
        </tr>
    </table>
    {if !empty($example)}
        <br/>
    <h4>{vtranslate('Example Styled with your template', 'Settings:Workflow2')}</h4>
        <hr>
        <em><label><input type="checkbox" style="display:inline;" class="NotHTMLFormated" />&nbsp;&nbsp;&nbsp;{vtranslate('Preview is NOT HTML formated', 'Settings:Workflow2')}</label></em>
        <br/>
        <div style="border:1px solid #ccc;padding:10px;" id="PreviewContainer">{$example}</div>
    {/if}
</div>