<table border="0" cellpadding="5" cellspacing="0" width="100%" class="small">
    <tr>
      <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">Message</td>
     <td width="15"></td>
      <td class='dvtCellInfo' style="padding:5px;">
          <div class="insertTextfield" data-name="task[message]" data-id="message" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}'>{$task.message}</div>
      </td>
    </tr>
    <tr>
        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Text on Button to continue', 'Settings:Workflow2')}</td>
        <td width="15"></td>
        <td class='dvtCellInfo' style="padding:5px;">
            <div class="insertTextfield" data-name="task[successtext]" data-id="successtext" data-options='{ldelim}"refFields":true, "module":"{$workflow_module_name}"{rdelim}'>{if empty($task.successtext)}{vtranslate('Execute Workflow', 'Settings:Workflow2')}{else}{$task.successtext}{/if}</div>
        </td>
    </tr>
    <tr>
      <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('able to completely stop workflow', 'Settings:Workflow2')}</td>
     <td width="15"></td>
      <td class='dvtCellInfo' style="padding:5px;">
          <input type="checkbox" class="rcSwitch doInit" name="task[stoppable]" value="1" {if $task.stoppable eq '1'}checked="checked"{/if}/>
      </td>
    </tr>
    {*<tr>*}
        {*<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('close request popup will stop workflow', 'Settings:Workflow2')}</td>*}
        {*<td width="15"></td>*}
        {*<td class='dvtCellInfo' style="padding:5px;">*}
            {*<input type="checkbox" class="rcSwitch doInit" name="task[stoponclose]" value="1" {if $task.stoponclose eq '1'}checked="checked"{/if}/>*}
        {*</td>*}
    {*</tr>*}
    <tr>
      <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('able to input later and pause workflow', 'Settings:Workflow2')}</td>
     <td width="15"></td>
      <td class='dvtCellInfo' style="padding:5px;">
          <input type="checkbox" class="rcSwitch doInit"  name="task[pausable]" value="1" {if $task.pausable eq '1'}checked="checked"{/if}/>
      </td>
    </tr>
</table>

<div style='margin:2px;border:1px solid #ccc;padding:3px;'>
    {$formGenerator}
</div>


