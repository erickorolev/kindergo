<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.CHECK_THIS_QUERY}:</td>
		<td class='dvtCellInfo'>
            <div class="insertTextarea" data-name="task[query]" data-id="query">{$task.query|stripslashes}</div>
		</td>
	</tr>
    <tr>
   		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.SUCCESS_IF_EQUAL_ROWS}:</td>
   		<td class='dvtCellInfo'>
   			<input type="text" name="task[numrows]" class="defaultTextfield" value="{$task.numrows}" />
   		</td>
   	</tr>
</table>