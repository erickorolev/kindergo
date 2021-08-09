<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskAdjustProductValue extends \Workflow\Task
{
    /**
     * @param \Workflow\VTInventoryEntity $context
     * @return string
     * @throws Exception
     */
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$adb = \PearDatabase::getInstance();

        $productSrc = $this->get('productsrc');
        if(empty($productSrc) || $productSrc == -1) {
            $productSrc = 'single';
        }

        $filterProduct = array();
        $tmp = explode(',',$this->get('products'));
        foreach($tmp as $productId) {
            $filterProduct[$productId] = true;
        }

        $inventory = $context->exportInventory();

        foreach($inventory['listitems'] as $index => $product) {
            if($productSrc == 'all' || isset($filterProduct[$product['productid']])) {
                $item = $inventory['listitems'][$index];

                $productObj = \Workflow\VTEntity::getForId($product['productid']);
                $context->setEnvironment('item', $productObj->getData());

                if($this->get('adjustquantity') == 'yes') {
                    $item['quantity'] = $this->get('quantity', $context);
                }

                if($this->get('adjustunitprice') == 'yes') {
                    $item['unitprice'] = $this->get('unitprice', $context);
                }

                if($this->get('adjustdescription') == 'yes') {
                    $item['comment'] = $this->get('description', $context);
                }

                if($this->get('adjusttax') == 'yes') {
                    $taxes = getAllTaxes("available");
                    $taxConfig = $this->get('tax', $context);

                    foreach($taxes as $tax) {
                        if(isset($taxConfig[$tax['taxname']])) {
                            if(empty($taxConfig[$tax['taxname']]['enable'])) {
                                $item[$tax['taxname']] = 0;
                            } else {
                                $item[$tax['taxname']] = $taxConfig[$tax['taxname']]['value'];
                            }
                        }
                    }
                }

                if($this->get('adjustdiscount') == 'yes') {
                    $value = $this->get('discountvalue', $context);

                    switch($this->get('discount')) {
                        default:
                        case 'none':
                            $item['discount_percent'] = 0;
                            $item['discount_amount'] = 0;
                            break;
                        case 'percent':
                            $item['discount_percent'] = $value['percent'];
                            $item['discount_amount'] = 0;
                            break;
                        case 'amount':
                            $item['discount_percent'] = 0;
                            $item['discount_amount'] = $value['amount'];
                            break;
                    }
                }

                //var_dump($item);exit();
                $inventory['listitems'][$index] = $item;
            }
        }
        $context->setEnvironment('item', array());

        $context->importInventory($inventory);
        $context->save();
		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {

        $product = $this->get('products');
        if(!empty($product)) {
            //$dataObj = \Vtiger_Record_Model::getInstanceById($product);
            $parts = explode(',', $product);
            $productCache = array();
            foreach($parts as $id) {
                $productCache[$id] = array(
                    'label' => \Vtiger_Functions::getCRMRecordLabel($id),
                );
            }
            $viewer->assign('productCache', $productCache);
        } else {
            $viewer->assign('productCache', array());
        }

        $viewer->assign("availTaxes", getAllTaxes("available"));
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
