<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once('WfTaskCreateInventory.php');
/* vt6 read */
class WfTaskReverseInventory extends \Workflow\Task
{
    protected $_envSettings = array("new_record_id");
    protected $_javascriptFile = array("WfTaskSetter.js");
    protected $_fields = array();
    protected $_customModule = "Invoice";
    protected $_hiddenValues = array();

    protected $fieldSetter = false;

    public function init() {

        $this->_productchooser = $this->addPreset("ProductChooser", "product", array(
            'module' => $this->getModuleName()
        ));

        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => $this->getModuleName(),
            'toModule' => $this->getModuleName(),
            'refFields' => false
        ));
    }

    public function handleTask(&$context) {
        /** Copied from WfTaskDuplicateRecord */
        /**
         * @var $contextRecord VTInventoryEntity
         */
        $contextRecord = $context;

        $newObj = \Workflow\VTEntity::create($contextRecord->getModuleName());

        $oldData = $contextRecord->getData();
        //var_dump($oldData);
        foreach($oldData as $key => $value) {
            $newObj->set($key, $value);
        }
        //$newObj->set('hdnDiscountPercent', -1 * $oldData['hdnDiscountPercent']);
        $newObj->set('hdnDiscountAmount', -1 * $oldData['hdnDiscountAmount']);
        /** Copied from WfTaskDuplicateRecord ENDE */

        $products = $contextRecord->exportInventory();
        foreach($products["listitems"] as $index => $listitem) {
            $listitem["quantity"] *= -1;

            $listitem['discount_amount'] *= -1;
            $products["listitems"][$index] = $listitem;
        }
        $products["shippingCost"] *= -1;
        $newObj->importInventory($products);

        /* After this line, copy from Creator */
        $setterMap = $this->get("setter");

        $this->fieldSetter->apply($newObj, $setterMap, $contextRecord, $this);

        $products = $this->get("product");
        /* INSERT PRODUCT */
        if(is_array($products) && count($products) > 0) {
            $availTaxes = getAllTaxes("available");
            foreach($products as $index => $value) {
                if(!empty($value["productid_individual"])) {
                    $productid = VTTemplate::parse($value["productid_individual"], $context);
                } else {
                    $productid = $value["productid"];
                }

                if(strpos($productid, "x") !== false) {
                    $parts = explode("x", $productid);
                    $productid = $parts[1];
                }
                $crmProduct = CRMEntity::getInstance("Products");
                $crmProduct->id = $productid;
                $crmProduct->retrieve_entity_info($productid, "Products");

                $context->setEnvironment("product", $crmProduct->column_fields);

                foreach($value as $key => $template) {
                    $value[$key] = VTTemplate::parse($template, $context);
                }


                $tax = array();
                foreach($availTaxes as $aTax) {
                    if($value["tax".$aTax["taxid"]."_enable"] == 1) {
                        $tax[$aTax["taxid"]] = VTTemplate::parse($value["tax".$aTax["taxid"]], $context);
                    }
                }

                $this->addStat("AddProduct ".$value["quantity"]." x ".$productid." (".$value["unitprice"].")");
                $newObj->addProduct($productid, $value["description"], $value["comment"], $value["quantity"], $value["unitprice"], ($value["discount_mode"]=="percentage"?$value["discount_value"]:0), ($value["discount_mode"]=="amount"?$value["discount_value"]:0), $tax);
            }

            try {
                $newObj->save();
            } catch(WebServiceException $exp) {
                // Somethink is wrong with the values. missing mandatory fields?
            }
        }

        try {
            $newObj->save();
        } catch(WebServiceException $exp) {
            // Somethink is wrong with the values. missing mandatory fields?
        }

        $context->setEnvironment("new_record_id", $newObj->getId(), $this);

        if($this->get("redirectAfter") == "1") {
            $this->getWorkflow()->setSuccessRedirection($newObj->getDetailUrl(), 'same');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = \Workflow\VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());

            $objWorkflow->setContext($newContext);
            $objWorkflow->isSubWorkflow(true);

            $objWorkflow->start();
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $new_module = $this->getWorkflow()->getSettings();
        $new_module = $new_module["module_name"];

        if(!empty($new_module) && $new_module != -1) {

            $viewer->assign("new_module", $new_module);
        }

        $sql = "SELECT
                    vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.description,
                    vtiger_products.*,
                    vtiger_productcf.*
                FROM vtiger_products
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
                    INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
                    LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_products.vendor_id
                    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
                WHERE
                    vtiger_products.productid > 0 AND
                    vtiger_crmentity.deleted = 0 and
                    vtiger_products.discontinued <> 0 AND
                    (vtiger_products.productid NOT IN (
                        SELECT crmid FROM vtiger_seproductsrel WHERE vtiger_products.productid > 0 AND setype='Products'
                        )
                    )";
        $result = $adb->query($sql);
        $products = array();
        $taxes = array();
        while($row = $adb->fetchByAssoc($result)) {
            $products[$row["productid"]] = $row;
            $taxes[$row["productid"]] = getTaxDetailsForProduct($row["productid"], 'all');
            if(empty($taxes[$row["productid"]])) {
                $taxes[$row["productid"]] = array("a" => "b");
            }
        }
        $viewer->assign("taxlist", $taxes);
        $viewer->assign("productlist", $products);

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $module = array();
        $module["Invoice"] = getTranslatedString("Invoice","Invoice");
        $module["Quotes"] = getTranslatedString("Quotes",     "Quotes");
        $module["PurchaseOrder"] = getTranslatedString("PurchaseOrder","PurchaseOrder");
        $module["SalesOrder"] = getTranslatedString("SalesOrder","SalesOrder");

        asort($module);

        $viewer->assign("avail_module", $module);
        $viewer->assign("orig_module_name", $this->getModuleName());
        $viewer->assign("availCurrency", getAllCurrencies());
        $viewer->assign("availTaxes", getAllTaxes("available"));

    }

}
