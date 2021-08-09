<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="3">
                <table width="100%">
                    <tr>
                        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Thousands Separator', 'Settings:Workflow2')}</td>
                        <td width="15"></td>
                        <td class="dvtCellInfo" align="left">
                            <div class="insertTextfield" data-name="task[thousendsseparator]" data-id="thousendsseparator">{$task.thousendsseparator|stripslashes}</div>
                        </td>
                        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Decimal Separator', 'Settings:Workflow2')}</td>
                        <td width="15"></td>
                        <td class="dvtCellInfo" align="left">
                            <div class="insertTextfield" data-name="task[decimalseparator]" data-id="decimalseparator">{$task.decimalseparator|stripslashes}</div>
                        </td>
                        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Decimal Numbers', 'Settings:Workflow2')}</td>
                        <td width="15"></td>
                        <td class="dvtCellInfo" align="left">
                            <div class="insertTextfield" data-name="task[decimalnumbers]" data-id="decimalnumbers">{$task.decimalnumbers|stripslashes}</div>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="height:30px;">&nbsp;</td>
        </tr
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_REGEX_TARGET_ENV_VAR', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                $env[<input type="text" name="task[envid]" class="defaultTextfield" value="{$task.envid}"/>]
            </td>
        </tr>
    </table>
</div>
<br/>
<script type="text/javascript" src="libraries/jquery/ckeditor/ckeditor.js"></script>

<textarea style="width:90%;height:200px;" name="task[content]" rows="55" cols="40" id="save_content" class="detailedViewTextBox"> {$task.content} </textarea>

<script type="text/javascript" defer="1">
    var textAreaName = 'save_content';

    CKEDITOR.config.protectedSource.push(/<\?php[\s\S]*?\?>/g);

    CKEDITOR.replace( textAreaName,	{ldelim}
        extraPlugins : 'uicolor',
        uiColor: '#dfdff1',
        fullPage: false,
        protectedSource: [/<tex[\s\S]*?\/tex>/g]
        {rdelim} );

    var oCKeditor = CKEDITOR.instances[textAreaName];
    /*    var available_attachments = {$jsAttachmentsList|@json_encode};*/
    /*    var attachmentFiles = {$task.attachments}; */
</script>
