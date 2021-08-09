<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    <tr>
        <td class="dvtCellLabel" align="right" width="15%">Printer</td>
        <td class="dvtCellInfo" align="left">
                <select class="chzn-select" id="search_module"  name='task[printer]' style="width:500px;">
                    <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                {foreach from=$printer item=item}
                    <option {if $task.printer == $item.id}selected='selected'{/if} value="{$item.id}">{$item.displayName}</option>
                {/foreach}
                </select>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" valign="top" width="15%">Files to print</td>
        <td>
            {$attachmentsList}
        </td>
    </tr>
</table>
<hr/>
<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    {if !empty($printerCapabilities)}
        {if !empty($printerCapabilities.page_orientation)}
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">Page Orientation</td>
                <td class="dvtCellInfo" align="left">
                    <select name="task[capability][page_orientation]">
                    {foreach from=$printerCapabilities.page_orientation key=key item=option}
                        <option value="{$key}" {if $task.capability.page_orientation eq $key OR (empty($task.capability.pageorientation) && $printerCapabilityDefaults.page_orientation eq $key)}selected="selected"{/if}>{$option}</option>
                    {/foreach}
                    </select>
                </td>
            </tr>
        {/if}

        {if !empty($printerCapabilities.duplex)}
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">Duplex?</td>
                <td class="dvtCellInfo" align="left">
                    <select name="task[capability][duplex]">
                    {foreach from=$printerCapabilities.duplex key=key item=option}
                        <option value="{$key}" {if $task.capability.duplex eq $key OR (empty($task.capability.duplex) && $printerCapabilityDefaults.duplex eq $key)}selected="selected"{/if}>{$option}</option>
                    {/foreach}
                    </select>
                </td>
            </tr>
        {/if}
    {/if}
</table>