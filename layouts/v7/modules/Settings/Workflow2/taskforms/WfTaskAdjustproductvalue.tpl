<p>Adjust the values of the following product inside the Inventory record</p>

<h3>Which Products should be modified:</h3>
<table width="100%" cellspacing="0" cellpadding="5" class="newTable">
    <tr>
        <td class="dvtCellLabel" align="right" width="10%"><input type="radio" name="task[productsrc]" value="all" {if $task.productsrc eq 'all'}checked="true"{/if} /></td>
        <td class="dvtCellInfo" align="left">
            All Products
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="right" valign="top" width="10%"><input type="radio" name="task[productsrc]" value="single" {if $task.productsrc eq 'single' || empty($task.productsrc)}checked="true"{/if} /></td>
        <td class="dvtCellInfo" align="left">
            Single Products:
            <input type='hidden' class='productSelect' value='{$task.products}' onchange='' style='' name='task[products]' id='products'>
            <p class="alert alert-info">{vtranslate('Will be applied to every item, equal to one of the selected products.','Settings:Workflow2')}</p>
        </td>
    </tr>
</table>


<script type="text/javascript">
    var productCache = {$productCache|@json_encode};

    jQuery(function() {
        jQuery(".productSelect").select2({
            placeholder: "search for a Product/Service",
            width:'50%',
            minimumInputLength: 1,
            multiple:true,
            initSelection: function (element, callback) {
                var parts = jQuery(element).val().split(',');
                var data = [];

                jQuery.each(parts, function(index, id) {
                    data.push({
                        id: id,
                        text: productCache[id]['label']
                    });
                });

                callback(data);
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

</script>
<h3>Which values should be modified:</h3>
<table width="100%" cellspacing="0" cellpadding="5" class="table table-condensed">
    <tr>
        <td class="dvtCellLabel" align="left" width="15%">
            <input type="checkbox" name="task[adjustquantity]" value="yes" {if $task.adjustquantity eq 'yes'}checked="true"{/if} /> Quantity</td>
        <td class="dvtCellInfo" align="left">
            <div class="insertTextfield" data-name="task[quantity]" data-id="quantity">{$task.quantity}</div>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="left" width="15%">
            <input type="checkbox" name="task[adjustunitprice]" value="yes" {if $task.adjustunitprice eq 'yes'}checked="true"{/if} /> Unit Price</td>
        <td class="dvtCellInfo" align="left">
            <div class="insertTextfield" data-name="task[unitprice]" data-id="unitprice">{$task.unitprice}</div>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="left" width="15%">
            <input type="checkbox" name="task[adjustdiscount]" value="yes" {if $task.adjustdiscount eq 'yes'}checked="true"{/if} /> Discount</td>
        <td class="dvtCellInfo" align="left">
            <table>
                <tr>
                    <td><input type="radio" alt="% of Price" title="% of Price" value="none" name="task[discount]" {if $task['discount'] eq 'none'}checked="checked"{/if}/></td>
                    <td width="100">No Discount</td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="radio" alt="% of Price" title="% of Price" value="percent" name="task[discount]" {if $task['discount'] eq 'percent'}checked="checked"{/if}/></td>
                    <td>% of Price</td>
                    <td><div class="insertTextfield" data-name="task[discountvalue][percent]" data-id="Percent_value">{$task['discountvalue']['percent']}</div></td>
                </tr>
                <tr>
                    <td><input type="radio" alt="% of Price" title="Fixed Amount" value="amount" name="task[discount]" {if $task['discount'] eq 'amount'}checked="checked"{/if}/></td>
                    <td>Fixed Amount</td>
                    <td><div class="insertTextfield" data-name="task[discountvalue][amount]" data-id="Amount_value">{$task['discountvalue']['amount']}</div></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="left" width="15%">
            <input type="checkbox" name="task[adjusttax]" value="yes" {if $task.adjusttax eq 'yes'}checked="true"{/if} /> Tax</td>
        <td class="dvtCellInfo" align="left">
            <table>
            {foreach from=$availTaxes item=tax}
                <tr>
                    <td><input type="checkbox" alt="Enable {$tax['taxlabel']}" title="Enable {$tax['taxlabel']}" value="1" name="task[tax][{$tax['taxname']}][enable]" {if $task['tax'][$tax['taxname']]['enable'] eq '1'}checked="checked"{/if}/></td>
                    <td width="100">{$tax['taxlabel']}</td>
                    <td><div class="insertTextfield" data-name="task[tax][{$tax['taxname']}][value]" data-id="{$tax['taxname']}_value" data-placeholder="Default: {$tax['percentage']|floatval}">{$task['tax'][$tax['taxname']]['value']}</div></td>
                </tr>
            {/foreach}
            </table>
            <p class="alert alert-info">
                {vtranslate('The individual tax will be set in every tax configuration, but will only be applied to item price if you enable "individual tax" in Record.', 'Settings:Workflow2')}
            </p>

        </td>
    </tr>
    <tr>
        <td class="dvtCellLabel" align="left" width="15%">
            <input type="checkbox" name="task[adjustdescription]" value="yes" {if $task.adjustdescription eq 'yes'}checked="true"{/if} /> Description</td>
        <td class="dvtCellInfo" align="left">
            <div class="insertTextarea" data-name="task[description]" data-id="description">{$task.description}</div>
        </td>
    </tr>
</table>