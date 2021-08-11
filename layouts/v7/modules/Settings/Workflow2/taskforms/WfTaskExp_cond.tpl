<div style="width:80%;margin:auto;border:1px solid #ccc;">
    <input type="button" class="btn btn-primary pull-right" style="margin-top:60px;" value="{vtranslate('insert Fieldcontent', 'Settings:Workflow2')}" id="btn_insert_variable"  onclick="insertTemplateField('task_condition', '[source]->[module]->[destination]', false);">
    <p style="text-align:left;padding:5px;margin-top:0;">{$MOD.LBL_EXPCOND_DESCRIPTION}</p>

    <div style="border:2px solid #576873;border-radius:3px;">
        <textarea name="task[condition]" id="task_condition" style="height:300px;width: 100%;font-family: 'Courier New';">{$task.condition}</textarea>
        <div class="alert alert-info" style="margin-bottom:0;">
            {vtranslate('You also get a list of variables and functions with autocomplete, if you press CTRL-SPACE during writing.','Settings:Workflow2')}
        </div>
    </div>


    <p>

    </p>

</div>
<script type="text/javascript">jQuery(function() { enable_customexpression("task_condition", false, false, workflowModuleName); });</script>
<script type="text/javascript">jQuery("#task_condition").on("insertText", function(e, text) {ldelim}
    customExpressionEditor["task_condition"].replaceSelection(text, "start");
{rdelim});
</script>
