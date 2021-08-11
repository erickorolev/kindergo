<div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" width="300">{vtranslate('Cookie Name', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type="text" name="task[cookiename]" value="{$task.cookiename|htmlentities}" />
            </td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right">{vtranslate('Have this value?', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <input type="text" name="task[cookievalue]" value="{$task.cookievalue|htmlentities}" />
            </td>
        </tr>
        <tr>
            <td class="dvtCellLabel" align="right"><br/>{vtranslate('Cookie exists in current browser?', 'Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left">
                <br/>
                {if $cookie_exists eq true}
                    <strong style="color:green;font-weight:bold;">{vtranslate('LBL_YES', 'Vtiger')}</strong>
                {else}
                    <strong style="color:red;font-weight:bold;">{vtranslate('LBL_NO', 'Vtiger')}</strong>
                {/if}
            </td>
        </tr>
    </table>
    <br/>
    <br/>
    <input type="hidden" name="setCookieAction" id="setCookieAction" value=""/>
    <input type="button" class="btn btn-primary" name="set_cookie" onclick="jQuery('#setCookieAction').val(1);jQuery('#save').trigger('click');" value="{vtranslate('Set cookie in your browser for 1 hour', 'Settings:Workflow2')}" />
    <input type="button" class="btn btn-warning" name="set_cookie" onclick="jQuery('#setCookieAction').val(2);jQuery('#save').trigger('click');" value="{vtranslate('remove cookie from browser', 'Settings:Workflow2')}" />
</div>
