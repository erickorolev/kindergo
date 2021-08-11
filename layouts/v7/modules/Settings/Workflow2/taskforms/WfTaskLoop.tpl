<p>
    <strong>This is an advanced task. Please only use, if you fully understand, what you are doing! Read the documentation!</strong><br/>
    This task works ONLY with Expressions / Scripts. Don't use any static texts!
</p>
<div></div>
<div>
    {$mainconfig}
</div>
<h3>Loop options</h3>
<table class="table table">
    <tr>
        <td width="60"><input type="checkbox" class="rcSwitch doInit"  {if !empty($task.loop) && $task.loop.path == '1'}checked="checked"{/if} name="task[loop][path]" value="1" /></td>
        <td> <h4 style="margin:0;line-height:30px;">Loop path output</h4></td>
    </tr>
    <tr>
        <td><input type="checkbox" class="rcSwitch doInit" id="EnableExpression" {if !empty($task.loop) && $task.loop.expression == '1'}checked="checked"{/if} name="task[loop][expression]" value="1" /></td>
        <td> <h4 style="margin:0;line-height:30px;">Execute Expression each time</h4>
            <div class="ShowOnExpression" style="display:none;">
                <br/>
            <div class="insertTextarea" data-name="task[expression]" data-mode="expression" data-id="idExpression" data-options='{ldelim}"width":"100%"{rdelim}'>{$task.expression|html_entity_decode}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td><input type="checkbox" class="rcSwitch doInit" id="EnableWorkflow" {if !empty($task.loop) && $task.loop.workflow == '1'}checked="checked"{/if} name="task[loop][workflow]" value="1" /></td>
        <td> <h4 style="margin:0;line-height:30px;">Execute Workflow each time</h4><br/>
            <div class="ShowOnWorkflow" style="display:none;">
                <select class="select2" name="task[workflow]" data-placeholder="{vtranslate('choose a Workflow', 'Settings:Workflow2')}" style="width:100%;">
                    <option value=""></option>
                    {foreach from=$workflows item=workflow}
                        <option value="{$workflow.id}" {if $task.workflow eq $workflow.id}selected="selected"{/if}>{$workflow.title}</option>
                    {/foreach}
                </select>
            </div>
        </td>
    </tr>
</table>