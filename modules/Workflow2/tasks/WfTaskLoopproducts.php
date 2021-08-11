<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
require_once('WfTaskLoop.php');

class WfTaskLoopproducts extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;
    public function init()
    {

    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */
        //$EnvironmentalKey = '__Loop_'.$this->getBlockId();

        unset($_FILES);

        if($this->get('source') == 'crmid') {
            $crmid = $this->get('crmid');

            if(empty($crmid)) {
                $crmid = '$crmid';
            }

            $crmid = \Workflow\VTTemplate::parse($crmid, $context);
            $targetContext = \Workflow\VTEntity::getForId($crmid);

            if($targetContext->isInventory() == false) {
                throw new \Exception('Record you use in this block do not have an Inventory.');
            }

            $products = $targetContext->exportInventory();
            $products = $products['listitems'];
            foreach($products as $index => $dummy) {
                $products[$index]['inventoryid'] = $crmid;
            }
         }
        if($this->get('source') == 'collection') {
            $collectionid = $this->get('collectionid', $context);
            $environmentId = '__prodcol_'.$collectionid;
            $ids = $context->getEnvironment($environmentId);

            if(empty($ids)) {
                return 'yes';

            }
            $sql = 'SELECT * FROM vtiger_inventoryproductrel WHERE lineitem_id IN ('.implode(',', $ids).')';
            $result = \Workflow\VtUtils::query($sql);

            $products = array();
            while($row = \Workflow\VtUtils::fetchByAssoc($result)) {
                $row['unitprice'] = $row['listprice'];
                $row['sequence'] = $row['sequence_no'];
                $row['inventoryid'] = $row['id'];

                $products[] = $row;
            }
        }

        $loopSettings = $this->get('loop');

        foreach($products as $index => $product) {
            $productData = \Workflow\VTEntity::getForId($product['productid']);

            $product['data'] = $productData->getData();

            if(!isset($product['sequence'])) {
                $product['sequence'] = $index + 1;
            }

            $this->addStat('Run Loop with Product = ' . $product['productid'] . ' ID '.$product['inventoryid'].' Sequence ' . $product['sequence']);

            $logger = \Workflow\ExecutionLogger::getCurrentInstance();

            if (!empty($loopSettings['path'])) {

                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $context->setEnvironment('product', $product);
                $obj->setContext($context);
                $obj->isSubWorkflow(true);

                $nextTasks = $this->getNextTasks(array('loop'));

                $obj->handleTasks($nextTasks, $this->getBlockId(), 'loop');
            }

            if (!empty($loopSettings['expression'])) {
                $parser = new \Workflow\ExpressionParser($this->get('expression'), $context, false); # Last Parameter = DEBUG
                $context->setEnvironment('product', $product);

                try {
                    $parser->run();
                } catch (\Workflow\ExpressionException $exp) {
                    Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                }
            }

            if (!empty($loopSettings['workflow'])) {
                $obj = new \Workflow\Main($this->get('workflow'), false, $context->getUser());
                $obj->setExecutionTrigger(\Workflow\Main::MANUAL_START);
                $obj->setContext($context);
                $context->setEnvironment('product', $product);
                $obj->isSubWorkflow(true);

                $obj->start();
            }

            \Workflow\ExecutionLogger::setCurrentInstance($logger);
            $context->clearEnvironment('product');
        }

        return "next";
    }

    public function beforeGetTaskform($viewer) {

        $workflows = $workflows = Workflow2::getWorkflowsForModule($this->getModuleName(), 1, "", false);
        $viewer->assign("workflows", $workflows);
        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }

}
