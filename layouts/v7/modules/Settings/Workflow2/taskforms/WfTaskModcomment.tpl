<table border="0" cellpadding="5" cellspacing="0" width="100%" class="small newTable">
    <tr>
   		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_COMMENT_RECORD}</td>
   		<td class='dvtCellInfo'>
   			<select name="task[relRecord]" class="select2" onchange="if(this.value == 'custom') { jQuery('#customRecordContainer').show(); } else { jQuery('#customRecordContainer').hide(); }  " style="width:300px;">
   				<option value="" {if $task.baseTime eq "now()"}selected='selected'{/if}>{$MOD.LBL_THIS_RECORD}</option>
                   {foreach from=$references item=item key=key}
                       <option value="{$item->name}" {if $item->name eq $task.relRecord}selected='selected'{/if}>{$item->label} [{$item->targetModule}]</option>
                   {/foreach}

                   <option value="custom" {if $task.relRecord eq 'custom'}selected='selected'{/if}>{vtranslate('custom RecordID', 'Settings:Workflow2')}</option>
   			</select>
   		</td>
   	</tr>
    <tr id="customRecordContainer" style="{if $task.relRecord neq 'custom'}display:none;{/if}">
   		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap" style="vertical-align:top;line-height:28px;">{vtranslate('generate custom recordID', 'Settings:Workflow2')}</td>
   		<td class='dvtCellInfo'>
               <div class="insertTextfield" data-name="task[customid]" data-id="customid">{$task.customid}</div><br/>
			<div class="alert alert-info">{vtranslate('You could set the RecordIDs manually, which should be use for this comment. (Multiple CRMIDs separated by comma.)', 'Settings:Workflow2')}</div>
   		</td>
   	</tr>

	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Define Authortype of comment', 'Settings:Workflow2')}</td>
		<td class='dvtCellInfo'>
			<select name="task[authorType]" class="select2" onchange="if(this.value == 'contactid' || this.value == 'userid') { jQuery('#customAuthorId').show(); } else { jQuery('#customAuthorId').hide(); }  " style="width:300px;">
				<option value="currentuser" {if empty($task.authorType) || $task.authorType == 'currentuser'}selected='selected'{/if}>{vtranslate('LBL_CURRENT_USER', 'Workflow2')}</option>
				<option value="userid" {if $task.authorType == 'userid'}selected='selected'{/if}>{vtranslate('custom User ID', 'Workflow2')}</option>
				<option value="contactid" {if $task.authorType == 'contactid'}selected='selected'{/if}>{vtranslate('custom Contact ID', 'Workflow2')}</option>
			</select>
		</td>
	</tr>

	<tr id="customAuthorId" style="{if empty($task.authorType) || $task.authorType eq 'currentuser'}display:none;{/if}">
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap" style="vertical-align:top;line-height:28px;">{vtranslate('Define this ID as Author', 'Settings:Workflow2')}</td>
		<td class='dvtCellInfo'>
			<div class="insertTextfield" data-name="task[authorid]" data-id="customid">{$task.authorid}</div><br/>
			<div class="alert alert-info">{vtranslate('Insert UserID or ContactID to define an individual author.', 'Settings:Workflow2')}</div>
		</td>
	</tr>

    <tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.LBL_CREATE_COMMENT_TEXT}</td>
		<td class='dvtCellInfo'>
			<div class="insertTextarea" data-name="task[comment]" data-id="comment">{$task.comment}</div>
		</td>
	</tr>

	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('LBL_INTERNAL_COMMENT', 'Vtiger')}</td>
		<td class='dvtCellInfo'>
			<input type="checkbox" name="task[private]" value="1" {if $task.private eq '1'}checked="checked"{/if} />
		</td>
	</tr>

	<tr>
		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap" style="vertical-align:top;line-height:28px;">{vtranslate('attach file to comment', 'Settings:Workflow2')}</td>
		<td class='dvtCellInfo'>
            {$attachmentsList}
		</td>
	</tr>

</table>

