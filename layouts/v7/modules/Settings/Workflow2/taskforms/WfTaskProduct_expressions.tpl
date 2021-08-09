<h4>{vtranslate('execute expression on products, that match', 'Settings:Workflow2')}</h4>

<div class="row">
    <label class="col-xs-4">Products AND/OR Services</label>
    <div class="col-xs-8">
        <select class="form-control select2" name="task[modulefilter]">
            <option value="all">All (Products + Services)</option>
            <option value="Products" {if $task.modulefilter eq 'Products'}selected="selected"{/if}>only Products</option>
            <option value="Services" {if $task.modulefilter eq 'Services'}selected="selected"{/if}>only Services</option>
        </select>
    </div>
</div>
<hr/>
{$conditionalContent}
<br/>
<h4>{vtranslate('execute this expression', 'Settings:Workflow2')}</h4>
<hr/>
<textarea name="task[expression]" id="custom_expression" rows="6" style="width:100%;" class='customFunction textfield'>{$task.expression}</textarea>
<br/><input type="button" class='btn btn-primary'  onclick="insertTemplateField('custom_expression', '[source]->[module]->[destination]', false, false,  {ldelim}module: 'Products'{rdelim});" value="{vtranslate('LBL_INSERT_TEMPLATE_VARIABLE', 'Settings:Workflow2')}"/>
<span> {vtranslate('read documentation for more information', 'Workflow2')}</span>
