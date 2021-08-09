<script src="modules/Workflow2/resources/emailtaskscript.js?v={$smarty.const.WORKFLOW2_VERSION}" type="text/javascript" charset="utf-8"></script>
<script src="modules/Workflow2/resources/newemailworkflow.js?v={$smarty.const.WORKFLOW2_VERSION}" type="text/javascript" charset="utf-8"></script>
{foreach from=$attachmentsJAVASCRIPT item=script}<script type="text/javascript">{$script}</script>{/foreach}

<div class="SendMailHeadContainer" >
    <div style="flex-grow:2;flex-basis:200px;">
        <table border="0" cellpadding="5" cellspacing="0" style="width:100%;" class="small newTable sendMailFields">
            {if $from.from_readonly == true}
                <tr>
                    <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap">* From Name</td>
                    <td class='dvtCellInfo'>{$from.from_name}<input type="hidden" name="task[from_name]" value="" id="save_from_name" class="form_input" style='width: 250px;'></td>
                </tr>
                <tr id="from_row">
                    <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b><font color=red>*</font> From Mail</b></td>
                    <td class='dvtCellInfo'>{$from.from_mail}<input type="hidden" name="task[from_mail]" value="" id="save_from_mail" class="form_input" style='width: 250px;'></td>
                </tr>
            {else}
                <tr>
                    <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b><span style='color:red;'>*</span> {vtranslate('LBL_SENDER_NAME', 'Settings:Workflow2')}</b></td>
                    <td class='dvtCellInfo'>
                        <div class="insertTextfield" data-name="task[from_name]" data-id="from_name">{$task.from_name}</div>
                </tr>
                <tr id="from_row">
                    <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b><span style='color:red;'>*</span> {vtranslate('LBL_SENDER_MAIL', 'Settings:Workflow2')}</b></td>
                    <td class='dvtCellInfo'>
                        <div class="insertTextfield" data-name="task[from_mail]" data-id="from_mail" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.from_mail}</div>
                </tr>
            {/if}
            <tr>
                <td class='dvtCellLabel' style="" align="right" width=20% nowrap="nowrap"><b><span style='color:red;'>*</span> {vtranslate('LBL_EMAIL_RECIPIENT', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[recepient]" data-id="recepient" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.recepient}</div>
                </td>
            </tr>
            <tr id="cc_row"  style="{if $task.emailcc eq ''}display: none;{/if}">
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b> {vtranslate('LBL_EMAIL_CC', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[emailcc]" data-id="emailcc" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.emailcc}</div>
            </tr>
            <tr id="bcc_row" style="{if $task.emailbcc eq ''}display: none;{/if}">
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b> {vtranslate('LBL_EMAIL_BCC', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[emailbcc]" data-id="emailbcc" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.emailbcc}</div>
                </td>
            </tr>
            <tr id="replyto_row" style="{if $task.replyto eq ''}display: none;{/if}">
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b> {vtranslate('LBL_EMAIL_REPLYTO', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[replyto]" data-id="emailreplyto" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.replyto}</div>
                </td>
            </tr>
            <tr id="confirmreading_row" style="{if $task.confirmreading eq ''}display: none;{/if}">
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b> {vtranslate('LBL_EMAIL_CONFIRMREADINGTO', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[confirmreading]" data-id="confirmreading" data-options='{ldelim}"type":"email","delimiter":","{rdelim}'>{$task.confirmreading}</div>
                </td>
            </tr>
            <tr id="storeid_row" style="{if $task.storeid eq ''}display: none;{/if}">
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b> {vtranslate('Store Mail to', 'Settings:Workflow2')}</b>
                    <a href="http://shop.stefanwarnat.de/?wf-docu=sendmail#store_mail_to" target="_blank">
                        <img src='modules/Workflow2/icons/question.png' border="0">
                    </a>


                </td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" style="display:inline;" data-name="task[storeid]" data-id="storeid" data-options='{ldelim}"type":"text","delimiter":","{rdelim}'>{$task.storeid}</div>
                </td>
            </tr>
            <tr>
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"></td>
                <td class='dvtCellInfo'>
                    <a href="#" onclick="jQuery(this).hide();jQuery('#cc_row').show();" style="{if $task.emailcc neq ''}display: none;{/if}padding-right:30px;">CC</a>
                    <a href="#"  onclick="jQuery(this).hide();jQuery('#bcc_row').show();" style="{if $task.emailbcc neq ''}display: none;{/if}padding-right:30px;">BCC</a>
                    <a href="#"  onclick="jQuery(this).hide();jQuery('#replyto_row').show();" style="{if $task.replyto neq ''}display: none;{/if}padding-right:30px;">ReplyTo</a>
                    <a href="#"  onclick="jQuery(this).hide();jQuery('#confirmreading_row').show();" style="{if $task.confirmreading neq ''}display: none;{/if}padding-right:30px;">Confirm Reading</a>
                </td>
            </tr>
            <tr>
                <td class='dvtCellLabel' align="right" width=20% nowrap="nowrap"><b><span style='color:red;'>*</span> {vtranslate('LBL_EMAIL_SUBJECT', 'Settings:Workflow2')}</b></td>
                <td class='dvtCellInfo'>
                    <div class="insertTextfield" data-name="task[subject]" data-id="subject">{$task.subject}</div>
                </td>
            </tr>
        </table>
    </div>
    <div style="flex-grow:1;flex-basis:100px;margin-left:10px;">
        <div class="accordion" id="accordion2">
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
                        {vtranslate('E-Mail attachments','Settings:Workflow2')}
                    </a>
                </div>
                <div id="collapseOne" class="accordion-body collapse in">
                    <div class="accordion-inner">
                        {$attachmentsList}
                    </div>
                </div>
            </div>
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                        {vtranslate('Options','Settings:Workflow2')}
                    </a>
                </div>
                <div id="collapseTwo" class="accordion-body collapse">
                    <div class="accordion-inner">
                        {if $show_emailfrom_checkbox eq true}
                            <label style="line-height:28px;margin:0;">
                                <input style="display:inline;" id="use_mailserver_from"class="rcSwitch doInit" type="checkbox" name="task[use_mailserver_from]" {if $task.use_mailserver_from eq '1'}checked="checked"{/if} value="1"/>
                                {vtranslate('Use Sender E-Mail from Mailserver config', 'Settings:Workflow2')}
                            </label>
                        {else}
                            <input type="hidden" name="task[use_mailserver_from]" value="0" />
                        {/if}

                        <label style="line-height:28px;margin:0;">
                            <input style="display:inline;" class="rcSwitch doInit" type="checkbox" name="task[trackAccess]" {if $task.trackAccess eq '1'}checked="checked"{/if} value="1"/>
                            {vtranslate('integrate VtigerCRM Access Tracker', 'Settings:Workflow2')}
                        </label>
                        <label style="line-height:28px;margin:0;">
                            <input style="display:inline;" type="checkbox" class="rcSwitch doInit" name="task[attachImages]" {if $task.attachImages eq '1'}checked="checked"{/if} value="1"/>
                            {vtranslate('directly embed images to mail', 'Settings:Workflow2')}
                        </label>
                        <label style="line-height:28px;margin:0;">
                            <input style="display:inline;" type="checkbox" class="rcSwitch doInit" name="task[multiplereceiver]" {if $task.multiplereceiver eq '1'}checked="checked"{/if} value="1"/>
                            {vtranslate('single mail to all receiver', 'Settings:Workflow2')}
                        </label>
                        <label style="line-height:28px;margin:0;">
                            <input style="display:inline;" type="checkbox" class="rcSwitch doInit" name="task[unlimitedretry]" {if $task.unlimitedretry eq '1'}checked="checked"{/if} value="1"/>
                            {vtranslate('unlimited retry on error (deactive = 6 Retry)', 'Settings:Workflow2')}
                        </label>
                        <label style="line-height:28px;margin:0;">
                            <input style="display:inline;" type="checkbox" class="rcSwitch doInit" name="task[interactivemail]" {if $task.interactivemail eq '1'}checked="checked"{/if} value="1"/>
                            {vtranslate('request user confirmation before sending', 'Settings:Workflow2')}
                        </label>
                        {if !empty($SMTPSERVER)}
                            <p>{vtranslate('Use this SMTP Server to send', 'Settings:Workflow2')}</p>
                            <select name="task[provider]">
                                <option value="">{vtranslate('Default SMTP Server', 'Settings:Workflow2')}</option>
                                {foreach from=$SMTPSERVER key=providerid item=label}
                                    <option value="{$providerid}" {if $task.provider eq $providerid}selected="selected"{/if}>{$label}</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
		<td style='padding-top: 10px;width:180px;'>
            <input type="hidden" id="templateVarContainer" value="" />
            <input type="button" class="btn btn-primary" value="{vtranslate('insert Fieldcontent', 'Settings:Workflow2')}" id="btn_insert_variable">
		</td>
        <td style="padding-top: 10px;">
            <ul class="nav nav-pills" style="margin-bottom: 0;{if $task.storeid neq ''}display: none;{/if}">
                          <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                Options
                                <b class="caret"></b>
                              </a>
                            <ul class="dropdown-menu">
                              <li style="{if $task.storeid neq ''}display: none;{/if}"><a href="#" onclick="jQuery(this).hide();jQuery('#storeid_row').show();">change Record to this mail will be stored</a></li>
                            </ul>
                          </li>
                        </ul>

        </td>
        <td>
        </td>
		<td style='padding-top: 10px;'>
            <div style="float: right;">
                <b>{vtranslate('LBL_SELECT_MAIL_TEMPLATE', 'Settings:Workflow2')}&nbsp</b>
                <select name="task[mailtemplate]" class="select2" style="width:300px;">
                    <option value="">- none -</option>
                    {foreach from=$MAIL_TEMPLATES item=category key=title}
                        <optgroup label="{$title}">
                            {foreach from=$category item=templatename key=templateid}
                                <option value="{$templateid}" {if $task.mailtemplate eq $templateid}selected="selected"{/if}>{$templatename}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <a href="http://shop.stefanwarnat.de/?wf-docu=sendmail" target="_blank">
                    <img src='modules/Workflow2/icons/question.png' border="0">
                </a>
            </div>
		</td>
	</tr>
</table>

<script type="text/javascript" src="libraries/jquery/ckeditor/ckeditor.js"></script>

<textarea style="width:90%;height:200px;" name="task[content]" rows="55" cols="40" id="save_content" class="detailedViewTextBox"> {$task.content} </textarea>

<script type="text/javascript" defer="1">
	var textAreaName = 'save_content';

    CKEDITOR.config.protectedSource.push(/<\?php[\s\S]*?\?>/g);

	CKEDITOR.replace( textAreaName,	{ldelim}
		extraPlugins : 'uicolor',
		uiColor: '#dfdff1',
        protectedSource: [/<tex[\s\S]*?\/tex>/g]
	{rdelim} );

	var oCKeditor = CKEDITOR.instances[textAreaName];
/*    var available_attachments = {$jsAttachmentsList|@json_encode};*/
/*    var attachmentFiles = {$task.attachments}; */
</script>
<style type="text/css">
    table.sendMailFields td {
        padding: 5px;
    }
    table.sendMailFields tr:hover  td.dvtCellInfo {
        background-color:#f8f8f8;
        color:#000;
    }
    table.sendMailFields tr:hover  td.dvtCellInfo input, table.sendMailFields tr  td.dvtCellInfo input:fo {
        color:#000;
    }
    table.sendMailFields td.dvtCellInfo {
        border-top:1px solid #ddd !important;
        border-bottom:1px solid #ddd !important;
        border-right:1px solid #ddd !important;
        padding: 2px 0 2px 5px;
    }
    table.sendMailFields td.dvtCellLabel {
        border-top:1px solid #ddd !important;
        border-bottom:1px solid #ddd !important;

        padding: 2px 5px 2px 0;
    }
    table.sendMailFields span.templateFieldSpan {
        border:none;
        box-shadow: none;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        border-radius: 0;
        -webkit-border-radius: 0;
        -moz-border-radius: 0;
    }
    table.sendMailFields span.templateFieldBtn img {
        margin-bottom:-8px;
    }
    table.sendMailFields span.templateFieldBtn {
        border-radius: 0;
        -webkit-border-radius: 0;
        -moz-border-radius: 0;
        margin:-2px 0 0px 0;
        height:30px;
    }
    table.sendMailFields span.templateFieldSpan input {
        border:none;
        box-shadow: none;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        padding:5px 30px 3px 0px;
        background-color:transparent;
    }
    div#mail_files {
        margin-top:0 !important;
    }
    .accordion-inner {
        padding:9px 5px;
    }
    .accordion-heading {

    }

    .accordion-heading {
        background-color:#0060bf;
        color:white;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        padding:5px 10px;
    }
    div.SendMailHeadContainer {
        display:flex;
    }
    .accordion-body.in.collapse {
        height:auto !important;
    }
</style>