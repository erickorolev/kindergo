<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('add files to final pdf', 'Workflow2')}:</td>
        <td class='dvtCellInfo'>
            {$attachmentsList}
        </td>
    </tr>
    <tr>
        <td valign="top" class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Filename of merged file', 'Workflow2')}</td>
        <td>
            <div class="insertTextfield" data-name="task[filename]" data-id="filename" data-options=''>{$task.filename}</div>
        </td>
    </tr>
    <tr>
        <td valign="top" class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('What to do with the file', 'Workflow2')}</td>
        <td>{$fileactions_resultaction}</td>
    </tr>
</table>
