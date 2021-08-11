<?php
/**
This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
 **/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once('WfTaskCustomCreator.php');
/* vt6 ready 2014/04/14 */
class WfTaskCreateInventory extends WfTaskCustomCreator
{
    protected $_javascriptFile = array("WfTaskSetter.js");
    protected $_fields = array();
    protected $_customModule = "Invoice";
    protected $_hiddenValues = array();

    /**
     * @var \Workflow\Preset\ProductChooser
     */
    private $_productchooser = null;
    /**
     * @var \Workflow\Preset\InventoryLoader
     */
    private $_inventoryChooser = null;

    public function init() {
        $this->_inventoryChooser = $this->addPreset("InventoryLoader", "inventoryloader");

        if(!empty($_POST["task"]["new_module_setter"])) {

            $new_module = $_POST["task"]["new_module_setter"];
        } else {
            $new_module = $this->get("new_module");
        }
        $this->_customModule = $new_module;

        $this->_productchooser = $this->addPreset("ProductChooser", "product", array(
            'module' => !empty($this->_customModule)?$this->_customModule:$this->getModuleName()
        ));

        parent::init();
    }

    public function handleTask(&$context) {
        $setterMap = $this->get("setter");
        $globalMap = $this->get("global");
        $products = $this->get("product");
        $shippingCost = 0;

        $newModule = $this->get("new_module");

        if(empty($newModule)) {
            $this->addStat("NO Configuration set");
            return "yes";
        }

        /**
         * @var $newObj \Workflow\VTInventoryEntity
         */
        $newObj = \Workflow\VTEntity::create($newModule);

        try {
            foreach($setterMap as $setter) {
                if(!empty($setter["field"]) && $setter["field"] == "currency_id") {
                    $setter["value"] = vtws_getWebserviceEntityId("Currency", $setter["value"]);
                }

                if($setter["mode"] == "function") {
                    $parser = new VTWfExpressionParser($setter["value"], $context, false); # Last Parameter = DEBUG

                    try {
                        $parser->run();
                    } catch(\Workflow\ExpressionException $exp) {
                        Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                    }

                    $newValue = $parser->getReturn();
                } else {
                    $setter["value"] = \Workflow\VTTemplate::parse($setter["value"], $context);

                    $newValue = $setter["value"];
                }

                $this->addStat("`".$setter["field"]."` = '".$newValue."'");

                if($setter["field"] == "hdnS_H_Amount") {
                    $shippingCost = $newValue;
                }

                #            var_dump($setter["field"], $newValue);
                $newObj->set($setter["field"], $newValue);
            }
        } catch (Exception $e) {
            var_dump($e);// ONLY ERROR
        }
        /*
                try {
                    $newObj->save();
                } catch(WebServiceException $exp) {
                    // Somethink is wrong with the values. missing mandatory fields?
                }
        */

        $groupTaxes = array();
        $shipTaxes = array();
        foreach($globalMap as $globalKey => $globalValue) {
            $globalValue = \Workflow\VTTemplate::parse($globalValue, $context);

            if(strpos($globalKey, "_group_percentage") !== false) {
                $groupTaxes[$globalKey] = $globalValue;
            }
            if(strpos($globalKey, "_sh_percent") !== false) {
                $shipTaxes[$globalKey] = $globalValue;
            }

            $this->addStat("`".$globalKey."` = '".$globalValue."'");
        }
        $newObj->setGroupTaxes($groupTaxes);
        $newObj->setShipTaxes($shipTaxes);

        $newObj->setShippingCost($shippingCost);

//        $newObj->save();

        if($this->notEmpty('inventoryloader')) {
            $loader = $this->get('inventoryloader');
            if(!empty($loader['select'])) {
                $this->_inventoryChooser->addProducts($newObj, $context);
            }
        }

        $newObj = $this->_productchooser->addProducts2Entity($this->get('product'), $context, $newObj);
        $newObj->save();

        $context->setEnvironment("new_record_id", $newObj->getId(), $this);

        if($this->get("redirectAfter") == "1") {
            $this->getWorkflow()->setSuccessRedirection($newObj->getDetailUrl(), 'same');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = \Workflow\VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());
            $newContext->loadEnvironment($context->getEnvironment());

            $objWorkflow->setContext($newContext);
            $objWorkflow->isSubWorkflow(true);

            $objWorkflow->start();
        }

        Workflow2::$enableError = true;

        return 'yes';
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $new_module = $this->get("new_module");

        if(!empty($_POST["task"]["new_module_setter"])) {

            $new_module = $_POST["task"]["new_module_setter"];
#            $viewer->assign("module_name", $_POST["task"]["new_module_setter"]);

            $mandatoryFields = VtUtils::getMandatoryFields(getTabId($_POST["task"]["new_module_setter"]));
            $startFields = array();
            $counter = 1;
            foreach($mandatoryFields as $field) {
                if('productid' != $field['fieldname']) {
                    $startFields["".$counter] = array("field" => $field["fieldname"], "mode" => "value", "value" => "", "fixed" => true);
                    $counter++;
                }
            }

            $startFields["".$counter++] = array("field" => "currency_id", "mode" => "value", "value" => "", "fixed" => true);
            $startFields["".$counter++] = array("field" => "hdnTaxType", "mode" => "value", "value" => "", "fixed" => true);
            $startFields["".$counter++] = array("field" => "hdnS_H_Amount", "mode" => "value", "value" => "", "fixed" => true);

            $this->set("setter", $startFields);
            $this->set("global", array());

        }

        if(!empty($new_module) && $new_module != -1) {
            $field = new StdClass();
            $field->name = "hdnS_H_Amount";
            $field->label = getTranslatedString("Shipping & Handling Charges", $_POST["task"]["new_module_setter"]);
            $additionalFields = array($field);

            $viewer->assign("new_module", $new_module);
        }

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $entityModules = VtUtils::getEntityModules();
        $module = array();
        foreach($entityModules as $moduleName) {
            $moduleObj = \Vtiger_Module_Model::getInstance($moduleName[0]);

            if($moduleObj instanceof \Inventory_Module_Model) {
                $module[$moduleName[0]] = $moduleName[1];
            }
        }


        /*

        $module["Quotes"] = getTranslatedString("Quotes",     "Quotes");
        $module["PurchaseOrder"] = getTranslatedString("PurchaseOrder","PurchaseOrder");
        $module["SalesOrder"] = getTranslatedString("SalesOrder","SalesOrder");
*/
        asort($module);

        $viewer->assign("avail_module", $module);
        $viewer->assign("orig_module_name", $this->getModuleName());
        $viewer->assign("availCurrency", getAllCurrencies());
        $viewer->assign("availTaxes", getAllTaxes("available"));

    }

}
