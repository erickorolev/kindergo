<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'include/InventoryPDFController.php';

class Vtiger_InvoicePDFController extends Vtiger_InventoryPDFController{
	function buildHeaderModelTitle() {
		$singularModuleNameKey = 'SINGLE_'.$this->moduleName;
		$translatedSingularModuleLabel = getTranslatedString($singularModuleNameKey, $this->moduleName);
		if($translatedSingularModuleLabel == $singularModuleNameKey) {
			$translatedSingularModuleLabel = getTranslatedString($this->moduleName, $this->moduleName);
		}
		return sprintf("%s: %s", $translatedSingularModuleLabel, $this->focusColumnValue('invoice_no'));
	}

	function buildHeaderModelColumnCenter() {
		$customerName = $this->resolveReferenceLabel($this->focusColumnValue('account_id'), 'Accounts');
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');
		$purchaseOrder = $this->focusColumnValue('vtiger_purchaseorder');
		$salesOrder	= $this->resolveReferenceLabel($this->focusColumnValue('salesorder_id'));

		$customerNameLabel = getTranslatedString('Customer Name', $this->moduleName);
		$contactNameLabel = getTranslatedString('Contact Name', $this->moduleName);
		$purchaseOrderLabel = getTranslatedString('Purchase Order', $this->moduleName);
		$salesOrderLabel = getTranslatedString('Sales Order', $this->moduleName);

		$modelColumnCenter = array(
				$customerNameLabel	=>	$customerName,
				$purchaseOrderLabel =>	$purchaseOrder,
				$contactNameLabel	=>	$contactName,
				$salesOrderLabel	=>	$salesOrder
			);
		return $modelColumnCenter;
	}

	function buildHeaderModelColumnRight() {
		$issueDateLabel = getTranslatedString('Issued Date', $this->moduleName);
		$validDateLabel = getTranslatedString('Due Date', $this->moduleName);
		$billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);
		$shippingAddressLabel = getTranslatedString('Shipping Address', $this->moduleName);

		$modelColumnRight = array(
				'dates' => array(
					$issueDateLabel  => $this->formatDate(date("Y-m-d")),
					$validDateLabel => $this->formatDate($this->focusColumnValue('duedate')),
				),
				$billingAddressLabel  => $this->buildHeaderBillingAddress(),
				$shippingAddressLabel => $this->buildHeaderShippingAddress()
			);
		return $modelColumnRight;
	}

	function getWatermarkContent() {
		return $this->focusColumnValue('invoicestatus');
	}
}

//SalesPlatform.ru begin add SP PDF controller

include_once 'includes/SalesPlatform/PDF/ProductListPDFController.php';
require_once 'modules/SalesOrder/SalesOrder.php';
require_once 'modules/Accounts/Accounts.php';
require_once 'modules/Contacts/Contacts.php';

class SalesPlatform_InvoicePDFController extends SalesPlatform_PDF_ProductListDocumentPDFController{

    function buildDocumentModel() {
        global $app_strings;

        try {
            $model = parent::buildDocumentModel();

            $this->generateEntityModel($this->focus, 'Invoice', 'invoice_', $model);

            //SalesPaltform.ru begin
            $entity = CRMEntity::getInstance('SalesOrder');
            //$entity = new SalesOrder();
            //SalesPaltform.ru end
            if ($this->focusColumnValue('salesorder_id')) {
                $entity->retrieve_entity_info($this->focusColumnValue('salesorder_id'), 'SalesOrder');
            }
            $this->generateEntityModel($entity, 'SalesOrder', 'salesorder_', $model);

            //SalesPaltform.ru begin
            $entity = CRMEntity::getInstance('Contacts');
            //$entity = new Contacts();
            //SalesPaltform.ru end
            if ($this->focusColumnValue('contact_id')) {
                $entity->retrieve_entity_info($this->focusColumnValue('contact_id'), 'Contacts');
            }
            $this->generateEntityModel($entity, 'Contacts', 'contact_', $model);

            //SalesPaltform.ru begin
            $entity = CRMEntity::getInstance('Accounts');
            //$entity = new Accounts();
            //SalesPaltform.ru end
            if ($this->focusColumnValue('account_id')) {
                $entity->retrieve_entity_info($this->focusColumnValue('account_id'), 'Accounts');
            }
            $this->generateEntityModel($entity, 'Accounts', 'account_', $model);

            $this->generateUi10Models($model);
            $this->generateRelatedListModels($model);

            $model->set('invoice_no', $this->focusColumnValue('invoice_no'));

            return $model;

        } catch (Exception $e) {
            echo '<meta charset="utf-8" />';
            if($e->getMessage() == $app_strings['LBL_RECORD_DELETE']) {
                echo $app_strings['LBL_RECORD_INCORRECT'];
                echo '<br><br>';
            } else {
                echo $e->getMessage();
                echo '<br><br>';
            }
            return null;
        }
    }

    function getWatermarkContent() {
        return '';
    }

    function russianDate($date){
        $date=explode("-", $date);
        switch ($date[1]){
            case 1: $m='????????????'; break;
            case 2: $m='??????????????'; break;
            case 3: $m='??????????'; break;
            case 4: $m='????????????'; break;
            case 5: $m='??????'; break;
            case 6: $m='????????'; break;
            case 7: $m='????????'; break;
            case 8: $m='??????????????'; break;
            case 9: $m='????????????????'; break;
            case 10: $m='??????????????'; break;
            case 11: $m='????????????'; break;
            case 12: $m='??????????????'; break;
        }

        return $date[2].' '.$m.' '.$date[0].' ??.';
    }
}

//SalesPlatform.ru end
?>
