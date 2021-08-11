<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_REGEX_SOURCE}</td>
		<td class='dvtCellInfo'>
			<textarea class="defaultTextfield" style="width:500px;height:60px;" id="source" name="task[source]">{$task.source}</textarea>
            <img src='modules/Workflow2/icons/templatefield.png' style='margin-bottom:-7px;cursor:pointer;' onclick="insertTemplateField('source','([source]: ([module]) [destination])')">
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_REGEX_VALUE}</td>
		<td class='dvtCellInfo'>
			<textarea class="defaultTextfield" style="width:500px;height:60px;" id="regex" name="task[regex]">{$task.regex}</textarea>
            <img src='modules/Workflow2/icons/templatefield.png' style='margin-bottom:-7px;cursor:pointer;' onclick="insertTemplateField('regex','([source]: ([module]) [destination])')">
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_REGEX_TARGET_ELEMENT}</td>
		<td class='dvtCellInfo'>
			<input type="text" class="defaultTextfield" style="width:200px;"  name="task[targetindex]" value="{$task.targetindex}">
		</td>
	</tr>
    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_REGEX_TARGET_ENV_VAR}</td>
		<td class='dvtCellInfo'>
			<input type="text" class="defaultTextfield" style="width:200px;" name="task[env_var]" value="{$task.env_var}">
		</td>
	</tr>
</table>
<hr>
    <table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    	<tr>
    		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_REGEX_TEST_STRING}</td>
    		<td class='dvtCellInfo'>
    			<textarea class="defaultTextfield" style="width:500px;height:100px;" name="testtext">{$testtext}</textarea>
    		</td>
    	</tr>
        <tr>
    		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">&nbsp;</td>
    		<td class='dvtCellInfo buttonbar'>
    			<input type="submit" class="btn btn-primary" name="submit" value="{$MOD.LBL_RUN_TEST}">
    		</td>
    	</tr>
        <tr>
    		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_TEST_RESULT}</td>
    		<td class='dvtCellInfo'>
    			{$testresult}
    		</td>
    	</tr>
    </table>
</form>