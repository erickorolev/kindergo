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

class ProductCollection implements \Workflow\Interfaces\IInventoryLoader {

    public function getAvailableLoader()
    {
        return array(
            'productcollection' => array(               // LOADERKEY
                'label' => 'Add Products from Product Collection',   // LOADERLABEL
                'config' => array(             // LOADERCONFIG
                    'collectionid' => array(
                        'type' => 'template',
                        'label' => 'Product Collection ID:',
                        'description' => 'Collect Products/Services in Product Collection to work with multiple Items at once.'
                    )
                )
            ),
        );
    }

    public function getItems($config, VTEntity $context)
    {
        $collectionid = $config['collectionid'];
        $envKey = '__prodcol_'.$collectionid;;

        $itemIds = $context->getEnvironment($envKey);

        if(empty($itemIds)) array();

        $sql = 'SELECT * FROM vtiger_inventoryproductrel WHERE lineitem_id IN ('.implode(',', $itemIds).')';
        $result = \Workflow\VtUtils::query($sql);

        $availableTaxes = getAllTaxes();
        $products = array();
        while($row = \Workflow\VtUtils::fetchByAssoc($result)) {
            $tmp = array(
                'module' => \Vtiger_Functions::getCRMRecordType($row['productid']),
                'productlabel' => \Vtiger_Functions::getCRMRecordLabel($row['productid']),
                'productid' => $row['productid'],
                'comment' => $row['comment'],
                'quantity' => $row['quantity'],
                'listprice' => $row['listprice'],
                'discount_amount' => $row['discount_amount'],
                'discount_percent' => $row['discount_percent'],
                'taxes' => array()
            );

            foreach($availableTaxes as $tax) {
                if(!empty($row[$tax['taxname']])) {
                    $tmp['taxes'][$tax['taxid']] = $row[$tax['taxname']];
                }
            }

            $products[] = $row;
        }

        return $products;
    }
}

InventoryLoader::register(__NAMESPACE__.'\\ProductCollection');