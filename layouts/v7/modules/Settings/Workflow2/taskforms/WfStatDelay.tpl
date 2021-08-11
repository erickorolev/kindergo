<table class="tableHeading" width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr>
		<td class="big" nowrap="nowrap">
			<strong>{$MOD.LBL_CURRENTLY_IN_DELAY}</strong>
		</td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="4" style="margin-left:3px; border-collapse:collapse;">
    <tr>
        <th class="">ID</td>
        <th class="">Record</td>
        <th class="">{$MOD.LBL_WAITING_SINCE}</td>
        <th class="">{$MOD.LBL_WAITING_UNTIL}</td>
    </tr>
{foreach from=$waiting item=record}
    <tr>
        <td class="dvtCellInfo">{$record.crmid}</td>
        <td class="dvtCellInfo">{$record.title}</td>
        <td class="dvtCellInfo">{$record.timestamp}</td>
        <td class="dvtCellInfo">{$record.nextsteptime}</td>
    </tr>
{/foreach}
</table>