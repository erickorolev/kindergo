<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="20%">{vtranslate('API Username', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[apikey]' class="textfield" id='apikey' value="{$task.apikey}" style="width:300px;">
            </td>
        </tr>

        {if $task.apikey != -1 && !empty($task.apikey)}
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">Printer <input type="radio" name="task[printersource]" {if empty($task.printersource) || $task.printersource eq 'static'}checked="checked"{/if} value="static" /></td>
                <td class="dvtCellInfo" align="left">
                    <select class="chzn-select" id="search_module"  name='task[printer]' style="width:500px;">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                        {foreach from=$printers key=computerName item=item}
                            <optgroup label="{$computerName}">
                                {foreach from=$item key=printerID item=printerName}
                                    <option value="{$printerID}" {if $printerID eq $task.printer}selected="selected"{/if}>{$printerName}</option>
                                {/foreach}
                            </optgroup>
                            <option {if $task.printer == $item.id}selected='selected'{/if} value="{$item.id}">{$item.displayName}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">PrinterID by Function <input type="radio" name="task[printersource]"  {if $task.printersource eq 'dynamic'}checked="checked"{/if}value="dynamic" /></td>
                <td class="dvtCellInfo" align="left">
                    <div class="insertTextfield" data-name="task[printerdynamic]" data-id="printerdynamic" data-options=''>{$task.printerdynamic}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right" valign="top" width="15%">Files to print</td>
                <td>
                    {$attachmentsList}
                </td>
            </tr>
        {/if}

    </table>
</div>

