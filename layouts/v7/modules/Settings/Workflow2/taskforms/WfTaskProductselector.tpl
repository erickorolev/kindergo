<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="30%">{vtranslate('Product Collection ID', 'Settings:Workflow2')}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[collectionid]' class="defaultTextfield" id='found_rows' required="requried" value="{$task.collectionid}" style="width:250px;">
            </td>
        </tr>
</table>
        {$recordsources}
<hr/>
<h3>Products you will to select</h3>
{$productcondition}
<h3>Services you will to select</h3>
{$servicecondition}