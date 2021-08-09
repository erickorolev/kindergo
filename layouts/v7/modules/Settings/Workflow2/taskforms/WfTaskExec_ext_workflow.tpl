<div>
    <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_SEARCH_IN_MODULE}</td>
            <td class="dvtCellInfo" align="left">
                    <select class="chzn-select" name='task[search_module]' style="width:350px;" onchange="jQuery('#search_module_hidden').val(jQuery(this).val());document.forms['hidden_search_form'].submit();">
                        <option {if $related_tabid == 0}selected='selected'{/if} value="0">{$MOD.LBL_CHOOSE}</option>
                    {foreach from=$related_modules item=module key=tabid}
                        <option {if $related_tabid == $tabid}selected='selected'{/if} value="{$module.0}#~#{$tabid}">{$module.1}</option>
                    {/foreach}
                    </select>
            </td>
        </tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.EXEC_FOLLOWING_WORKFLOW}</td>
       		<td class='dvtCellInfo'>
       			<select name="task[workflow_id]" class="chzn-select" style="width:350px;">
       				<option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                       {foreach from=$workflows item=item key=key}
                           <option value="{$item.id}" {if $item.id eq $task.workflow_id}selected='selected'{/if}>{$item.title}</option>
                       {/foreach}
       			</select>
       		</td>
       	</tr>
        <tr>
            <td class="dvtCellLabel" align="right" width="15%">{$MOD.LBL_EXEC_FOR_THIS_NUM_ROWS}:</td>
            <td class="dvtCellInfo" align="left">
                <input type='text' name='task[found_rows]' class="defaultTextfield" id='found_rows' value="{$task.found_rows}" style="width:50px;"> ({$MOD.LBL_EMPTY_ALL_RECORDS})
            </td>
        </tr>
        <tr>
       		<td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$MOD.SORT_RESULTS_WITH}</td>
       		<td class='dvtCellInfo'>
       			<select name="task[sort_field]" class="chzn-select" style="width:350px;">
       				<option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                       {foreach from=$sort_fields item=block key=blockLabel}
                       <optgroup label="{$blockLabel}">
                            {foreach from=$block item=field key=fieldLabel}
                                <option value="{$field->name}" {if $field->name eq $task.sort_field}selected='selected'{/if}>{$field->label}</option>
                            {/foreach}
                       </optgroup>
                       {/foreach}


       			</select>
               <select class="chzn-select-nosearch" style="width:100px;"name="task[sortDirection]"><option value="ASC"{if $task.sortDirection eq "ASC"}selected='selected'{/if}>ASC</option><option value="DESC"{if $task.sortDirection eq "DESC"}selected='selected'{/if}>DESC</option></select>
       		</td>
       	</tr>
    </table>
</div>

{if !empty($related_tabid)}
{$recordsources}
<h4><input type="radio" name="task[recordsource]" class="recordSourceSelection" value="condition" {if empty($task.recordsource) || $task.recordsource eq 'condition'}checked="true"{/if} />{vtranslate('get Records by Condition','Settings:Workflow2')}</h4>
<hr/>
<div class="recordSource recordSource_condition">
{$conditionalContent}
</div>
<br/>
<h4><input type="radio" name="task[recordsource]" class="recordSourceSelection" value="customview" {if $task.recordsource eq 'customview'}checked="true"{/if} /> {vtranslate('get Records from CustomView','Settings:Workflow2')}</h4>
<hr/>
<div class="recordSource recordSource_customview">
    <p>{vtranslate('Use this view to get records. (Admin Permissions will be used to bypass user based restrictions)')}</p>
    <select class="select2" name="task[customviewsource]" data-placeholder="{vtranslate('select customview', 'Settings:Workflow2')}" style="width:300px;">
        <option value=""></option>
        {foreach from=$customviews key=cvid item=viewname}
        <option value="{$cvid}" {if $task.customviewsource eq $cvid}selected="selected"{/if}>{$viewname}</option>
        {/foreach}
    </select>
</div>

{if $searchByProduct}
<br/>
<br/>
<br/>
<h4><input type="checkbox" name="task[filterbyproduct]" value="yes" {if $task.filterbyproduct eq 'yes'}checked="true"{/if} /> {vtranslate('filter Records also by included Product','Settings:Workflow2')}</h4>
<hr/>
<p>{vtranslate('Search Inventory Records, which contain the following product')}</p>
<input type='hidden' class='productSelect span8' value='{$task.products}' onchange='' style='' name='task[products]' id='products'>
{else}
<input type='hidden' value=''  name='task[products]'>
{/if}
<script type="text/javascript">
    var deposit = document.getElementById("found_rows");
    var productCache = {$productCache|@json_encode};

    deposit.onkeyup = function() {ldelim}
        var PATTERN = /\d$/;

        if (!deposit.value.match(PATTERN)) {ldelim}
            deposit.value = deposit.value.replace(deposit.value.slice(-1), "");
        {rdelim}
    {rdelim}

    jQuery(function() {
        checkVisibility(true);

        jQuery('.recordSourceSelection').on('click', function() { checkVisibility(false); });

        console.log(jQuery(".productSelect"));
        jQuery(".productSelect").select2({
            placeholder: "search for a Product/Service",
            minimumInputLength: 1,
            initSelection: function (element, callback) {
                callback({
                    id: jQuery(element).val(),
                    text: productCache[jQuery(element).val()]['label']
                });
            },
            query: function (query) {

                var data = {
                    query: query.term,
                    page: query.page,
                    pageLimit: 25
                };

                jQuery.post("index.php?module=Workflow2&action=ProductChooser", data, function (results) {
                    query.callback(results);
                }, 'json');

            }
        });
    });
    function checkVisibility(init) {
        var source= jQuery('.recordSourceSelection:checked').val();
        if(init == true) {
            jQuery('.recordSource').hide();
            jQuery('.recordSource_' + source).show();

        } else {
            jQuery('.recordSource').slideUp();
            jQuery('.recordSource_' + source).slideDown();

        }
    }



</script>

{/if}
</form>

<form method="POST" name="hidden_search_form" action="#">
    <input type="hidden" name="task[search_module]" id='search_module_hidden' value=''>
</form>