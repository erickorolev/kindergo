<table width="100%" cellspacing="0" cellpadding="5" class="newTable">
    <tr>
        <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_PAUSE_AFTER_RECORDS}:</td>
        <td class="dvtCellInfo" align="left">
            <input type='text' name='task[pause_rows]' class="defaultTextfield" id='pause_rows' value="{$task.pause_rows}" style="width:50px;"> ({$MOD.LBL_WHY_IMPORT_PAUSE})
        </td>
    </tr>
</table>
<br>
<br>
    <button type="button" class="btn btn-primary" onclick="addCol();">add Column</button>
<div id="rows"></div>

<script type="text/javascript">
    var cols = {$cols|@json_encode}
</script>