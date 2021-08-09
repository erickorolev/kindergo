<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="dvtCellLabel" width="25%" align="right">MySQL Provider:</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select name="task[provider]" required="required">
                {html_options options=$available_providers selected=$task['provider']}
            </select>
            <a href="index.php?module=Workflow2&view=ProviderManager&parent=Settings" target="_blank"><img src="modules/Workflow2/icons/question.png" /></a>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" width="25%" align="right">Database:</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <div class="insertTextfield" data-name="task[dbname]" data-id="dbname" data-options=''>{$task.dbname}</div>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" width="25%" align="right">Table:</td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <div class="insertTextfield" data-name="task[table]" data-id="table" data-options='' style="float:left;">{$task.table}</div>
            &nbsp;&nbsp;&nbsp;<input type="button" class="btn btn-primary" id="loadStructureBtn" value="load Structure" />
            <input type="hidden" name="loadStructure" id="loadStructure" value="0" />
        </td>
    </tr>
</table>

<br/>
<button type="button" onclick="addCol('','',0,'rows', 'cols');" class="btn btn-primary">add Value</button>

<h4>set Table columns -> value</h4>
<div id="rows"></div>

<div class="clearfix"></div>
<hr/>
<button type="button" onclick="addCol('','',0,'rowsWhere', 'colsWhere');" class="btn btn-primary">add Value</button>

<h4>Check for already existing row and update: check columns -> value</h4>
<div id="rowsWhere"></div>

<script type="text/javascript">
    var cols = {$cols|@json_encode};
    var colsWhere = {$colsWhere|@json_encode};
</script>