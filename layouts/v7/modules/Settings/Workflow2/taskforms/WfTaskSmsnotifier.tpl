<div>
    <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('LBL_PHONE_NUMBER','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextfield" data-name="task[number]" data-id="number">{$task.number}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right">{vtranslate('LBL_SMS_TEXT','Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left" style="padding:5px;">
                    <div class="insertTextarea" data-name="task[sms_text]" data-id="sms_text">{$task.sms_text|@stripslashes}</div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right"></td>
                <td class="dvtCellInfo" align="left" style="padding:5px;" id="buchstabenCounter">
                </td>
            </tr>
        <script type="text/javascript">
            jQuery('body').on('inputFieldsReady', function() {ldelim}
                jQuery("#sms_text").on("keyup", function() {ldelim}
                    jQuery("#buchstabenCounter").html(jQuery("#sms_text").val().length + " {vtranslate('LBL_LETTER','Settings:Workflow2')}");
                {rdelim});

                jQuery("#buchstabenCounter").html(jQuery("#sms_text").val().length + " {vtranslate('LBL_LETTER','Settings:Workflow2')}");
            {rdelim});
        </script>
    </table>
</div>
