<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskStyledinventory extends \Workflow\Task
{
    private function formatNumber($value) {
        if(empty($value)) $value = 0;
        return number_format($value, $this->get('decimalnumbers'), $this->get('decimalseparator'), $this->get('thousendsseparator'));
    }
    /**
     * @param \Workflow\VTInventoryEntity $context
     * @return string
     */
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
        $content = $this->get('content', $context);

        $content = preg_replace('/(\$[A-Za-z0-9]+)\$/', '$1', $content);

        $firstPos = strpos($content, '#PRODUCTBLOC_START#');
        $trStartPos = strrpos(substr($content, 0, $firstPos), '<tr>');


        $firstPos = strpos($content, '#PRODUCTBLOC_END#');

        $trLastpos = strpos($content, '</tr>', $firstPos) + 5;

        $ReplaceThis = trim(substr($content, $trStartPos,  $trLastpos - $trStartPos));

        $trStartNextPos = strpos($ReplaceThis, '</tr>');
        $trLaststartPos = strrpos($ReplaceThis, '<tr>');


        $WITHINTEXT = substr($ReplaceThis, $trStartNextPos + 5, $trLaststartPos - $trStartNextPos - 5);

        $items = $context->exportInventory();

        $ProductVars = array();
        $RecordVars = array(
            'DISCOUNTTOTAL' => 0,
            'TAXTOTAL' => 0,
            'NETTOTAL' => 0,
            'TOTALWITHOUTVAT' => 0,
            'FINALDISCOUNT' => 0,
            'FINALDISCOUNTPERCENT' => 0
        );
        $taxes = getAllTaxes('all');

        foreach($items['listitems'] as $POSITION => $item) {
            $productContext = \Workflow\VTEntity::getForId($item['productid']);

            $PRODUCTTOTAL = floatval($item['unitprice']) * floatval($item['quantity']);

            $PRODUCTSUBTOTAL = $PRODUCTTOTAL;
            $PRODUCTDISCOUNT =  $item['discount_amount'];
            if(!empty($item['discount_percent'])) {
                $PRODUCTDISCOUNT += $PRODUCTTOTAL * ($item['discount_percent'] / 100);
            }

            $PRODUCTSTOTALAFTERDISCOUNTSUM = $PRODUCTTOTAL - $PRODUCTDISCOUNT;

            $PRODUCTVATPERCENT = 0;

            if($context->get('hdnTaxType') == 'individual') {
                foreach($taxes as $tax) {
                    if(!empty($item[$tax['taxname']])) {
                        $PRODUCTVATPERCENT += floatval($item[$tax['taxname']]);
                    }
                }
            }

            $PRODUCTVATSUM = $PRODUCTSTOTALAFTERDISCOUNTSUM * ($PRODUCTVATPERCENT / 100);
            $PRODUCTTOTALSUM = $PRODUCTTOTAL + $PRODUCTVATSUM;

            $ProductVars[$POSITION] = array(
                'PRODUCTNAME' => $productContext->get('productname'),
                'PRODUCTTITLE' => $productContext->get('productname'),
                'PRODUCTDESCRIPTION' => $productContext->get('description'),
                'PRODUCTEDITDESCRIPTION' => $productContext->get('description'),
                'PRODUCTLISTPRICE' => $this->formatNumber($item['unitprice']),
                'PRODUCTTOTAL' => $this->formatNumber($PRODUCTTOTAL),
                'PRODUCTQUANTITY' => $this->formatNumber($item['quantity']),
                'PRODUCTQINSTOCK' => $this->formatNumber($productContext->get('qtyinstock')),
                'PRODUCTPRICE' => $this->formatNumber($productContext->get('unit_price')),
                'PRODUCTPOSITION' => $POSITION + 1,
                'PRODUCTQTYPERUNIT' => $this->formatNumber($productContext->get('qty_per_unit')),
                'PRODUCTUSAGEUNIT' => $productContext->get('usageunit'),
                'PRODUCTDISCOUNT' => $this->formatNumber($PRODUCTDISCOUNT),
                "PRODUCTDISCOUNTPERCENT" => $this->formatNumber($item['discount_percent']),
                'PRODUCTSTOTALAFTERDISCOUNTSUM' => $PRODUCTSTOTALAFTERDISCOUNTSUM,
                'PRODUCTSTOTALAFTERDISCOUNT' => $this->formatNumber($PRODUCTSTOTALAFTERDISCOUNTSUM),
                'PRODUCTTOTALSUM' => $this->formatNumber($PRODUCTTOTALSUM),
                'PRODUCTVATSUM' => $this->formatNumber($PRODUCTVATSUM),
                'PRODUCTVATPERCENT' => $PRODUCTVATPERCENT
            );

            $RecordVars['NETTOTAL'] += $PRODUCTSUBTOTAL;
            $RecordVars['TOTALWITHOUTVAT'] += $PRODUCTSTOTALAFTERDISCOUNTSUM;

            $RecordVars['FINALDISCOUNT'] += $PRODUCTDISCOUNT;
        }

        $discountPercent = $context->get('hdnDiscountPercent');
        $discountAmount = $context->get('hdnDiscountAmount');
        if(!empty($discountPercent)) {
            $discountAmount = $RecordVars['TOTALWITHOUTVAT'] * ($discountPercent / 100);
        }
        if(empty($discountAmount)) $discountAmount = 0;

        if($context->get('hdnTaxType') == 'group') {
            $GROUPTAXPERCENT = 0;
            foreach($items['groupTax'] as $groupTax) {
                $GROUPTAXPERCENT += floatval($groupTax);
            }
            $RecordVars['TAXTOTALPERCENT'] = $RecordVars['VATPERCENT'] = $this->formatNumber($GROUPTAXPERCENT);

            foreach($ProductVars as $POSITION => $PRODUCTDATA) {
                $ProductVars[$POSITION]['PRODUCTVATPERCENT'] = $this->formatNumber($GROUPTAXPERCENT);

                $ProductVars[$POSITION]['PRODUCTVAT'] = $this->formatNumber($PRODUCTDATA["PRODUCTSTOTALAFTERDISCOUNTSUM"] * ($GROUPTAXPERCENT / 100));

                $ProductVars[$POSITION]['PRODUCTVATSUM'] = $this->formatNumber($PRODUCTDATA["PRODUCTSTOTALAFTERDISCOUNTSUM"] * ($GROUPTAXPERCENT / 100));
                $ProductVars[$POSITION]['PRODUCTTOTALSUM'] = $this->formatNumber($PRODUCTDATA["PRODUCTSTOTALAFTERDISCOUNTSUM"] + $PRODUCTDATA['PRODUCTVAT']);
            }
        }

        $RecordVars['FINALDISCOUNTSUM'] = $discountAmount;
        $RecordVars['FINALDISCOUNT'] = $RecordVars['TOTALDISCOUNT'] = $this->formatNumber($discountAmount);

        $RecordVars['TAXTOTAL'] = ($RecordVars['TOTALWITHOUTVAT'] - $RecordVars['FINALDISCOUNTSUM']) * ($GROUPTAXPERCENT / 100);

        $RecordVars['TAXTOTALSUM'] = $RecordVars['TAXTOTAL'];
        $RecordVars['TAXTOTAL'] = $RecordVars['VAT'] = $this->formatNumber($RecordVars['TAXTOTAL']);


        $RecordVars['FINALDISCOUNTPERCENT'] = $RecordVars['TOTALDISCOUNTPERCENT'] = $this->formatNumber($discountPercent);
        $RecordVars['TOTALAFTERDISCOUNT'] = $this->formatNumber($RecordVars['TOTALWITHOUTVAT'] - $discountAmount);
        $RecordVars['TOTALWITHOUTVATSUM'] = $RecordVars['TOTALWITHOUTVAT'] ;
        $RecordVars['TOTALWITHOUTVAT'] = $this->formatNumber( $RecordVars['TOTALWITHOUTVAT'] );
        $RecordVars['NETTOTAL'] = $this->formatNumber( $RecordVars['NETTOTAL'] );

        $RecordVars['TOTALWITHVAT'] = $this->formatNumber( ($RecordVars['TOTALWITHOUTVATSUM'] - $RecordVars['FINALDISCOUNTSUM']) + $RecordVars['TAXTOTALSUM']);

        $RecordVars['SHTAXAMOUNTSUM'] = $items['shippingCost'];
        $RecordVars['SHTAXAMOUNT'] = $this->formatNumber($items['shippingCost']);

        $shPercent = 0;
        foreach($items['shipTaxes'] as $taxId => $taxPercent) {
            $shPercent += floatval($taxPercent);
        }

        $RecordVars['SHTAXPERCENT'] = $this->formatNumber($shPercent);
        $RecordVars['SHTAXTOTAL'] = $this->formatNumber($RecordVars['SHTAXAMOUNTSUM'] * ($shPercent / 100));

        $RecordVars['ADJUSTMENTSUM'] = $context->get('txtAdjustment');
        $RecordVars['ADJUSTMENT'] = $this->formatNumber($RecordVars['ADJUSTMENTSUM']);

        $RecordVars['TOTAL'] = $this->formatNumber($context->get('hdnGrandTotal'));
        $RecordVars['SUBTOTAL'] = $this->formatNumber($context->get('hdnSubTotal'));

        $currency = getInventoryCurrencyInfo($context->getModuleName(), $context->getId());
        $RecordVars['CURRENCYCODE'] = $currency['currency_code'];
        $RecordVars['CURRENCYSYMBOL'] = $currency['currency_symbol'];

        if(empty($RecordVars['CURRENCYCODE'])) $RecordVars['CURRENCYCODE'] = '';
        if(empty($RecordVars['CURRENCYSYMBOL'])) $RecordVars['CURRENCYSYMBOL'] = '';

        $RecordVars['CURRENCYNAME'] = getTranslatedCurrencyString($currency["currency_name"]);

        $finalHTML = '';
        foreach($ProductVars as $POSITION => $Vars) {
            $dummy = \Workflow\VTEntity::getDummy();
            $dummy->initData($Vars);

            $finalHTML .= \Workflow\VTTemplate::parse($WITHINTEXT, $dummy);
        }

        $CONTENT = str_replace($ReplaceThis, $finalHTML, $content);

        $dummy = \Workflow\VTEntity::getDummy();
        $dummy->initData($RecordVars);

        $CONTENT = \Workflow\VTTemplate::parse($CONTENT, $dummy);

        $envid = $this->get('envid');
        if(empty($envid) || $envid == -1) {
            return 'yes';
        }

        $context->setEnvironment($envid, $CONTENT);

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        if(!$this->notEmpty('decimalnumbers')) {
            $this->set('decimalnumbers', 2);
        }
        if(!$this->notEmpty('decimalseparator')) {
            $this->set('decimalseparator', ',');
        }
        if(!$this->notEmpty('thousendsseparator')) {
            $this->set('thousendsseparator', '.');
        }
        if(!$this->notEmpty('content')) {
            $this->set('content', '<table border="1" cellpadding="3" cellspacing="0" style="font-size:10px;" width="100%">
	<thead>
		<tr bgcolor="#c0c0c0">
			<td style="TEXT-ALIGN: center"><span><strong>Pos</strong></span></td>
			<td colspan="2" style="TEXT-ALIGN: center"><span><strong>%G_Qty%</strong></span></td>
			<td style="TEXT-ALIGN: center"><span><span style="font-weight: bold;">Text</span></span></td>
			<td style="TEXT-ALIGN: center"><span><strong>%G_LBL_LIST_PRICE%</strong></span></td>
			<td style="text-align: center;"><strong>%G_Subtotal%</strong></td>
			<td style="TEXT-ALIGN: center"><span><strong>%G_Discount%</strong></span></td>
			<td style="TEXT-ALIGN: center">Mwst</td>
			<td style="TEXT-ALIGN: center"><span><strong>%M_Total%</strong></span></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="9">#PRODUCTBLOC_START#</td>
		</tr>
		<tr>
			<td style="text-align: center; vertical-align: top;">$PRODUCTPOSITION$</td>
			<td align="right" valign="top">$PRODUCTQUANTITY$</td>
			<td align="left" style="TEXT-ALIGN: center" valign="top">$PRODUCTUSAGEUNIT$</td>
			<td align="left" valign="top">$PRODUCTNAME$</td>
			<td align="right" style="text-align: right;" valign="top">$PRODUCTLISTPRICE$</td>
			<td align="right" style="TEXT-ALIGN: right" valign="top">$PRODUCTTOTAL$</td>
			<td align="right" style="TEXT-ALIGN: right" valign="top">$PRODUCTDISCOUNT$</td>
			<td align="right" style="TEXT-ALIGN: right" valign="top"><span style="font-size: 10px; text-align: right;">$PRODUCTVATPERCENT$</span><br style="font-size: 10px; text-align: right;" />
			<span style="font-size: 10px; text-align: right;">$PRODUCTVATSUM$</span></td>
			<td align="right" style="text-align: right;" valign="top">$PRODUCTSTOTALAFTERDISCOUNT$</td>
		</tr>
		<tr>
			<td colspan="9">#PRODUCTBLOC_END#</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left"><span>%G_LBL_NET_PRICE% without TAX</span></td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td style="TEXT-ALIGN: right">$TOTALWITHOUTVAT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left">%G_Discount%</td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td style="TEXT-ALIGN: right">$TOTALDISCOUNT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left">Total without TAX</td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td style="TEXT-ALIGN: right">$TOTALAFTERDISCOUNT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="text-align: left;">%G_Tax% $VATPERCENT$ % %G_LBL_LIST_OF% $TOTALAFTERDISCOUNT$</td>
			<td style="text-align: left;">&nbsp;</td>
			<td style="text-align: right;">$VAT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="text-align: left;">Total with TAX</td>
			<td style="text-align: left;">&nbsp;</td>
			<td style="text-align: right;">$TOTALWITHVAT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="text-align: left;">%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>
			<td style="text-align: left;">&nbsp;</td>
			<td style="text-align: right;">$SHTAXAMOUNT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left">%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td style="TEXT-ALIGN: right">$SHTAXTOTAL$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left">%G_Adjustment%</td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td style="TEXT-ALIGN: right">$ADJUSTMENT$ $CURRENCYSYMBOL$</td>
		</tr>
		<tr>
			<td colspan="7" style="TEXT-ALIGN: left"><span style="font-weight: bold;">%G_LBL_GRAND_TOTAL% </span><strong>($CURRENCYCODE$)</strong></td>
			<td style="TEXT-ALIGN: left">&nbsp;</td>
			<td nowrap="nowrap" style="TEXT-ALIGN: right"><strong>$TOTAL$ $CURRENCYSYMBOL$ </strong></td>
		</tr>
	</tbody>
</table>
');
        }
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
