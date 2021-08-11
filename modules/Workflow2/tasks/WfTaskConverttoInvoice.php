<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/**
 * vt6 ready 2014/04/27
 */
class WfTaskConverttoInvoice extends \Workflow\Task {
    protected $_javascriptFile = "WfTaskSetter.js";
    protected $_envSettings = array("new_record_id");

    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;

    public function init() {
        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => $this->getModuleName(),
            'toModule' => 'Invoice',
            'refFields' => false
        ));
    }

    /**
     * @param $context \Workflow\VTEntity|\Workflow\VTInventoryEntity
     */
    public function handleTask(&$context) {
        $referenceId = $context->getId();

        Workflow2::$enableError = true;

        $parentRecordModel = Inventory_Record_Model::getInstanceById($referenceId);
        $currencyInfo = $parentRecordModel->getCurrencyInfo();
        $taxes = $parentRecordModel->getProductTaxes();
        $shippingTaxes = $parentRecordModel->getShippingTaxes();
        $relatedProducts = $parentRecordModel->getProducts();

        $recordModel = Vtiger_Record_Model::getCleanInstance('Invoice');
        $recordModel->setRecordFieldValues($parentRecordModel);
        $recordModel->save();

        $newId = $recordModel->getId();

        $contextRecord = $context;

        /**
         * @var $newObj \Workflow\VTEntity|\Workflow\VTInventoryEntity
         */
        $newObj = \Workflow\VTEntity::getForId($newId);
        $newObj->set('hdnTaxType', $parentRecordModel->get('hdnTaxType'));

        $newObj->importProductsFromRecord($relatedProducts, true);

        $setterMap = $this->get("setter");

        $this->fieldSetter->apply($newObj, $setterMap, $contextRecord, $this);

        $newObj->save();

        $context->setEnvironment("new_record_id", $newObj->getId(), $this);

        if($this->get("redirectAfter") == "1") {
            $this->getWorkflow()->setSuccessRedirection("index.php?module=".$newObj->getModuleName()."&view=Detail&record=".$newObj->getId(), 'same');
        }

        if($this->get("exec_workflow") !== "" && $this->get("exec_workflow") != -1) {
            $newContext = VTEntity::getForId($newObj->getId(), $newObj->getModuleName());
            $newContext->loadEnvironment($context->getEnvironment());

            $objWorkflow = new \Workflow\Main($this->get("exec_workflow"), false, $context->getUser());
            $objWorkflow->isSubWorkflow(true);
            $objWorkflow->setContext($newContext);

            $objWorkflow->start();
        }

        Workflow2::$enableError = true;

        return 'yes';
    }

    public function beforeGetTaskform($viewer) {
        global $adb;

        $new_module = "Invoice";


        $viewer->assign("new_module", $new_module);

        $workflows = Workflow2::getWorkflowsForModule($new_module, 1);
        $viewer->assign("extern_workflows", $workflows);

        $sql = "SELECT * FROM vtiger_tab WHERE presence = 0 AND isentitytype = 1 ORDER BY name";
        $result = $adb->query($sql);

        $module = array();
        while($row = $adb->fetch_array($result)) {
            if($row["name"] == "Calendar")
                continue;

            $module[$row["name"]] = getTranslatedString($row["tablabel"],$row["name"]);
        }

        asort($module);

        $viewer->assign("avail_module", $module);

    }

    public function beforeSave(&$data) {

    }

}