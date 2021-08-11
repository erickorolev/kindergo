<?php
/**
 * Created by Stefan Warnat
 * User: Stefan
 * Date: 28.09.2016
 * Time: 18:59
 */
namespace Workflow\Plugin\InventoryLoader;

use Workflow\InventoryLoader;
use Workflow\VTEntity;

class MergeItems implements \Workflow\Interfaces\IInventoryLoader {

    public function getAvailableLoader()
    {
        return array(
            'loaderkey' => array(               // LOADERKEY
                'label' => 'Merge items of other Inventory Records',   // LOADERLABEL
                'config' => array(             // LOADERCONFIG
                    'recordids' => array(
                        'type' => 'template',
                        'label' => 'Record IDs to merge:',
                        'description' => 'List all Record IDs you want to merge. Separated by comma.<br/>It does not matter if they are within different modules.'
                    )
                )
            ),
        );
    }

    public function getItems($config, VTEntity $context)
    {
        $recordIds = $config['recordids'];
        if(empty($recordIds)) return array();

        $recordIds = \Workflow\VTTemplate::parse($recordIds, $context);
        $split = explode(',', $recordIds);

        $products = array();

        $availableTaxes = getAllTaxes();
        foreach($split as $id) {
            $context = VTEntity::getForId($id);
            $productExport = $context->exportInventory();

            $listItems = $productExport['listitems'];

            foreach($listItems as $item) {
                $productContext = VTEntity::getForId($item['productid']);

                $tmp = array(
                    'module' => $productContext->getModuleName(),
                    'productlabel' => $productContext->getCRMRecordLabel(),
                    'productid' => $item['productid'],
                    'comment' => $item['comment'],
                    'quantity' => $item['quantity'],
                    'listprice' => $item['unitprice'],
                    'discount_amount' => $item['discount_amount'],
                    'discount_percent' => $item['discount_percent'],
                    'taxes' => array()
                );

                foreach($availableTaxes as $tax) {
                    if(!empty($item[$tax['taxname']])) {
                        $tmp['taxes'][$tax['taxid']] = $item[$tax['taxname']];
                    }
                }

                $products[] = $tmp;
            }

        }

        return $products;
    }
}

InventoryLoader::register(__NAMESPACE__.'\\MergeItems');