<input type="hidden" name="changeModule" id="changeModule" value="0" />
{if $disable neq true}
    <table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap"><strong>PDFMaker Version:</strong></td>
            <td class='dvtCellInfo'>
                <strong>{$PDFMAKER_VERSION}</strong>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
        <tr>
            <td align="right">{vtranslate('with current Record', 'Settings:Workflow2')}</td>
            <td><input type="radio" name="task[recordChooser]" {if $task.recordChooser eq 'current' OR $task.recordChooser eq ''}checked="checked"{/if} onchange="jQuery('#save').trigger('click');"  value="current" />&nbsp;&nbsp;&nbsp;(If you switch this, Page will be saved and reloaded)</td>
        </tr>
        <tr>
            <td align="right">{vtranslate('with matched Record/s', 'Settings:Workflow2')}</td>
            <td><input type="radio" name="task[recordChooser]" {if $task.recordChooser eq 'condition'}checked="checked"{/if} onchange="jQuery('#save').trigger('click');" value="condition" />&nbsp;&nbsp;&nbsp;(If you switch this, Page will be saved and reloaded)</td>
        </tr>
    </table>
    <div style="{if $task.recordChooser neq 'condition'}display: none;{/if}padding:5px;border: 1px solid #ccc;margin:5px;">
        <table>
            <tr>
                <td>From Module</td>
                <td>
                    <select class="chzn-select" name='task[search_module]' style="width:350px;" onchange="jQuery('#changeModule').val('1');jQuery('#save').trigger('click');">
                        <option {if $related_tabid eq 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                        {foreach from=$related_modules item=module key=tabid}
                            <option {if $related_tabid eq $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
        </table>
        {$conditionalContent}
        The result will be a PDF, which is merged from all single templates, like PDFMaker does.
    </div>
    <table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_PDFTEMPLATE}:</td>
            <td class='dvtCellInfo'>
                <input type="hidden" id="TemplateIDList" name="task[templateids]" value="{$task.templateids}" />
                {*<select style="vertical-align:top;width:500px;" name='task[template][]' class="chzn-select" multiple="multiple">*}
                {*<option value=''>--- Choose Template ---</option>*}
                {*{foreach from=$templates key=label item=field}*}
                {*<option value='{$field.templateid}'  {if in_array($field.templateid, $task.template)}selected='selected'{/if}>{$field.filename}</option>*}
                {*{/foreach}*}
                {*</select>*}
            </td>
        </tr>
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">Language:</td>
            <td class='dvtCellInfo'>
                <select name="task[language]" class="select2 form-control">
                    {foreach from=$LANGUAGES item=LABEL key=LANG}
                        <option value="{$LANG}" {if $task.language eq $LANG}selected="selected"{/if}>{vtranslate($LABEL, 'Vtiger')}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">Copies:</td>
            <td class='dvtCellInfo'>
                <div class="insertTextfield" data-name="task[copies]" data-id="copies">{$task.copies}</div>
            </td>
        </tr>
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_OVERWRITE_FILENAME}:</td>
            <td class='dvtCellInfo'>
                <div class="insertTextfield" data-name="task[filename]" data-id="filename">{$task.filename}</div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
        <tr>
            <td valign="top" style="line-height:28px;">{vtranslate('What to to with the file', 'Workflow2')}</td>
            <td>{$fileactions_resultaction}</td>
        </tr>
    </table>

{else}
    <br/>
    <br/>
    <strong>PDFMaker Extension not found</strong>
    <br/>
    <br/>
    <br/>

{/if}
</form>

<form method="POST" name="hidden_search_form" action="#">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>
<script type="text/javascript">
    var templateList = {$templates|json_encode};
    var templateData = {$templateData|json_encode};
</script>