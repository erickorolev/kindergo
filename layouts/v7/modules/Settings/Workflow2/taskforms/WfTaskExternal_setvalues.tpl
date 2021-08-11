<div class="alert alert-warning">
    {vtranslate('Please read documentation before you use this block!', 'Settings:Workflow2')}<br/>
    <input type="checkbox" name="task[agreeagb]" value="1" {if $task.agreeagb eq '1'}checked="checked"{/if}/>
    <strong>{vtranslate('You confirm to be carefully with this task and accept the risk of using this task, if you do not set configuration carefully.','Settings:Workflow2')}</strong>
</div>
{$recordsources}
<hr/>
<div class="alert alert-info">
    <input type="checkbox" name="task[dryrun]" value="1" {if $task.dryrun eq '1'}checked="checked"{/if}/>
    <strong>{vtranslate('Dry Run - Do not modify any records, but log matched record IDs in execution statistic.','Settings:Workflow2')}</strong>
</div>

<h4>Fields to set</h4>
{$setterContent}