<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskProductselector extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    /**
     * @var \Workflow\Preset\Condition
     */
    private $ProductsCondition = null;
    /**
     * @var \Workflow\Preset\Condition
     */
    private $ServicesCondition = null;

    public function init() {
        $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
            //'module' => VtUtils::getModuleName($parts[1]),
            'default' => 'condition',
            'moduleselect' => 'inventory',
        ));

        $targetModule = $this->RecordSources->getTargetModule();

        $this->ProductsCondition = $this->addPreset("Condition", "productcondition", array(
            'templatefield' => 'productcondition',
            'container' => 'productcondition',
            'environment' => true,
            'fromModule' => $this->getModuleName(),
            'toModule' => 'Products'
        ));
        $this->ServicesCondition = $this->addPreset("Condition", "servicecondition", array(
            'templatefield' => 'servicecondition',
            'container' => 'servicecondition',
            'environment' => true,
            'fromModule' => $this->getModuleName(),
            'toModule' => 'Services'
        ));

        /*if($this->RecordSources->isModuleChanged()) {
            $this->RecordSources->clea();
        }*/


    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$recordQuery = $this->RecordSources->getQuery($context);


        $productQueries = array();
        if($this->notEmpty('productcondition')) {
            $productQuery = $this->ProductsCondition->getConditionQuery($context);
            $productQueries[] = 'productid IN ('.$productQuery.')';
        }
        if($this->notEmpty('servicecondition')) {
            $serviceQuery = $this->ServicesCondition->getConditionQuery($context);
            $productQueries[] = 'productid IN ('.$serviceQuery.')';
        }

        $mainQuery = 'SELECT vtiger_inventoryproductrel.lineitem_id FROM vtiger_inventoryproductrel WHERE id IN ('.$recordQuery.')';
        if(!empty($productQueries)) {
            $mainQuery .= ' AND ('.implode(' OR ', $productQueries).')';
        }

        $adb = \PearDatabase::getInstance();
        $result = $adb->query($mainQuery);

        $inventoryIds = array();
        while($row = $adb->fetchByAssoc($result)) {
            $inventoryIds[] = $row['lineitem_id'];
        }

        $this->addStat($mainQuery);
        $this->addStat('Inventory IDs:');
        $this->addStat($inventoryIds);

        $collectionid = $this->get('collectionid');
        $environmentId = '__prodcol_'.$collectionid;

        $context->setEnvironment($environmentId, $inventoryIds);

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
