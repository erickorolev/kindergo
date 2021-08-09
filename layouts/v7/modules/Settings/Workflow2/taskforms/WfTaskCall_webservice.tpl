<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('URL', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <div class="insertTextfield" data-name="task[url]" data-id="subject">{$task.url}</div>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Method', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select class="chzn-select" name="task[method]" style="width:300px;">
                {html_options options=$webservice_methods selected=$task.method}
            </select>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Response Format', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select class="chzn-select" name="task[responsetype]" style="width:300px;">
                <option value="">Plain Text</option>
                <option value="json" {if $task.responsetype eq 'json'}selected="selected"{/if}>JSON</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('LBL_MYSQL_QUERY_ENV_VARIABLE', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            $env["<input type="text" required="required" name="task[envvar]" value="{$task.envvar}" />"] = Response
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Request Content-Type', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <select class="chzn-select" name="task[parameterformat]" style="width:100%;">
                <option value="">Default (application/x-www-form-urlencoded)</option>
                <option value="json" {if $task.parameterformat eq 'json'}selected="selected"{/if}>JSON (application/json)</option>
            </select>
        </td>
    </tr>
    {if $SHOW_INVENTORY eq true}
    <tr>
        <td class="dvtCellLabel" align="right" width="25%">{vtranslate('Write Inventory lines into parameter', 'Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left" style="padding:5px;">
            <input type="text" class="form-control" required="required" name="task[inventoryvar]" value="{$task.inventoryvar}" />
        </td>
    </tr>
    {/if}
</table>

<br/>
<button type="button" onclick="addCol();" class="btn btn-primary">add Parameter</button>

<div id="rows"></div>

<br/>
<h4>Header Values</h4>
<hr/>
<button type="button" onclick="addHeaderCol();" class="btn btn-primary">add Header</button>

<div id="header_rows"></div>

<script type="text/javascript">
    var cols = {$cols|@json_encode};
    var header = {$header|@json_encode};
</script>