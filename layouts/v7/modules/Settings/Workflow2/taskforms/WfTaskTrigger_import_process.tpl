<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Filestore ID you want to import', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <div class="insertTextfield" data-name="task[filestoreid]" data-id="filestoreid">{$task.filestoreid}</div>
        </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('choose a Workflow', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <select name="task[workflow]" style="width:600px;" class="select2">
                {foreach from=$workflows item=title key=id}
                    <option value="{$id}" {if $task.workflow eq $id}selected="selected"{/if}>{$title}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_PLEASE_CHOOSE_IMPORT_FORMAT', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <select name="task[format]" style="width:600px;"class="select2">
                <option value='csv'>CSV</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Delimiter', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <div class="insertTextfield" data-name="task[delimiter]" data-id="delimiter">{$task.delimiter}</div>
        </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('File encoding', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <select class="select2 form-control" name="task[encoding]">
                {foreach from=$encodings item=encoding}
                    <option value="{$encoding}" {if (!empty($task.encoding) && $encoding eq $task.encoding) || (empty($task.encoding) && $encoding eq 'UTF-8')}selected="selected"{/if}>{$encoding}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('skip first row', 'Settings:Workflow2')}</td>
        <td class='dvtCellInfo'>
            <input type="checkbox" name="task[skipfirstrow]" {if $task.skipfirstrow eq '1'}checked="checked"{/if} value="1" />
        </td>
    </tr>
</table>
