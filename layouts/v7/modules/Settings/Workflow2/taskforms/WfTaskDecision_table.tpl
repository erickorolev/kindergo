<button class="pull-right btn btn-default AddRowBtn" type="button">
    <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;&nbsp;
    {vtranslate('add Row', "Settings:Workflow2")}
</button>
<button class="btn btn-info AddDecisionBtn" type="button">
    <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;&nbsp;
    {vtranslate('add Decision', "Settings:Workflow2")}
</button>
<button class="btn btn-success AddSetterBtn" type="button">
    <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;&nbsp;
    {vtranslate('add Output', "Settings:Workflow2")}
</button>

<table class="table table-condensed"  id="DecisionTable" style="margin-top:10px;">
    <thead>
        <tr>
            <th class="btn-info" style="border:none !important;"></th>
            <th id="th_decision" style="border:none !important;" class="btn-info">{vtranslate('Decision', 'Settings:Workflow2')} <i class="fa fa-plus-square pull-right AddDecisionBtn" style="margin-top:3px;cursor:pointer;margin-right:10px;" aria-hidden="true"></i>&nbsp;&nbsp;</th>
            <th id="th_setter" style="border:none !important;" class="btn-success">{vtranslate('Output', 'Settings:Workflow2')} <i class="fa fa-plus-square pull-right AddSetterBtn" style="margin-top:3px;cursor:pointer;margin-right:10px;" aria-hidden="true"></i>&nbsp;&nbsp;</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<button class="btn btn-default AddRowBtn" type="button"><i class="fa fa-plus-square" aria-hidden="true"></i></button>

<script type="text/javascript">
    jQuery(function() {
        Decisiontable.init({$task.structure|json_encode}, {$task.data|json_encode});
    });
    var ModuleFields = {$fields|json_encode};
</script>

<style type="text/css">
    .DecisionHead td {
        background-color:#cccccc;
    }
    .HeadAction {
        text-align:center;
    }
    .RowAction {
        vertical-align: middle !important;
        font-size:14px;
    }
    .TableRowTR:hover, .TableRowTR.Focused {
        background-color:#eee;
    }
</style>