<p class="alert alert-info">{vtranslate('You are able to set any fields of Emails module you like. Email related fields will be overwritte by this task.', 'Settings:Workflow2')}</p>

{$setterContent}

{*<div>*}
    {**}
    {*<table width="100%" cellspacing="0" cellpadding="5" class="newTable">*}
        {*<tr>*}
            {*<td class='dvtCellLabel' align="right" width=20% nowrap="nowrap">{vtranslate('Assigned to', 'Vtiger')}</td>*}
            {*<td class='dvtCellInfo'>*}
                {*<select name="task[assign_to]" class="select2" style="width:350px;">*}
                    {*<option value="0" {if $task.assign_to eq ""}selected='selected'{/if}>-</option>*}
                    {*{foreach from=$users item=userlabel key=userid}*}
                        {*<option value="{$userid}" {if $userid eq $task.assign_to}selected='selected'{/if}>{$userlabel}</option>*}
                    {*{/foreach}*}
                {*</select>*}
            {*</td>*}
        {*</tr>*}
    {*</table>*}
{*</div>*}


<p class="alert alert-info">{vtranslate('Choose records, which will be used to relate the eMail to. If no record was found, the second output will executed.', 'Settings:Workflow2')}</p>

{$recordsources}