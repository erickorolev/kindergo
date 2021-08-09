<input type="hidden" name="ChanceProvider" id="ChanceProvider" value="0" />
<table class="table">
    <tr>
        <td style="width:30%">{vtranslate('Provider', 'Settings:Workflow2')}</td>
        <td><select class="select2" id="provider" name="task[provider]" style="width:400px;">
                {foreach from=$provider item=label key=id}
                    <option value="{$id}" {if $task.provider eq $id}selected="selected"{/if}>{$label}</option>
                {/foreach}
            </select></td>
    </tr>
    {foreach from=$dataFields item=field key=fieldName}
        <tr>
            <td>{$field.label}</td>
            <td>{include file='../VT7/ConfigGenerator.tpl' config=$field key=$fieldName value=$task.data[$fieldName]}</td>
        </tr>
    {/foreach}
</table>