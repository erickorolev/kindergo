<div>
    {if $isFrontendWorkflow neq true}
        <p class="alert alert-warning">{vtranslate('Please be sure to use the correct Task. There is another Block "Show Message" special for Default Workflows.<br/>This Task do <strong>NOT</strong> show messages during default workflows.','Settings:Workflow2')}</p>
    {/if}

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="dvtCellLabel" align="right" width="25%">{vtranslate('execute this function','Settings:Workflow2')}</td>
            <td width="15"></td>
            <td class="dvtCellInfo" align="left" style="padding:5px;">
                <select name="task[frontendaction]" id="FrontendActionSel">
                    <option value=""></option>
                    {foreach from=$actions key=key item=item}
                        <option value="{$key}" {if $key eq $task.frontendaction}selected="selected"{/if}>{$item.label}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    </table>

    {foreach from=$actions key=key item=item}
        <div class="ConfigContainer" data-action="{$key}" style="display:none;">
            <h5>{vtranslate('Settings', 'Settings:Workflow2')}: {vtranslate($item.label, 'Settings:Workflow2')}</h5>
            <hr/>
            {if !empty($item.hint)}
            <p class="alert alert-info">{$item.hint}</p>
            {/if}
            {foreach from=$item.config key=fieldname item=config}
                <div style="line-height:26px;overflow:hidden;margin:5px 20px;">
                    <span class="pull-left" style="width:200px">{$config.label}</span>
                    <div style="width:400px;float:left;margin-left:50px;">
                        {assign var="set_fieldname" value="task[action][`$key`][config][`$fieldname`]"}
                        {assign var="fieldname_template" value="task[action][`$key`][config][##FIELDNAME##]"}

                        {include file='../VT7/ConfigGenerator.tpl' config=$config fieldname=$set_fieldname value=$task.action[$key].config[$fieldname] fieldname_template=$fieldname_template}
                        {if !empty($config.description)}
                            <em>{$config.description}</em>
                        {/if}
                    </div>
                </div>
                {foreachelse}
                <em>{vtranslate('LBL_NO_CONFIG_FORM', 'Settings:Workflow2')}</em>
            {/foreach}
        </div>
    {/foreach}
</div>