<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\ResourceModel\Source;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @param Source $source
     */
    public function __construct(
        Source $source
    ) {
        $this->source = $source;
    }

    /**
     * @param string $eavAttribute
     * @return mixed
     */
    public function getSourceAttributes($eavAttribute)
    {
        $select = $this->getEavAttributeSelect($eavAttribute);
        return $this->source->getAdapter()->loadDataFromSelect($select);
    }

    /**
     * @param string $eavAttribute
     * @return \Magento\Framework\DB\Select
     */
    protected function getEavAttributeSelect($eavAttribute)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $tables = [];
        foreach (array_keys($this->getDocumentList()) as $sourceDocument) {
            $tables[] = $this->source->addDocumentPrefix($sourceDocument);
        }
        $select->from($tables)->where($eavAttribute . ' is not null');
        return $select;
    }

    /**
     * @return array
     */
    public function getEavAttributes()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function getDocumentList()
    {
        return [
                'sales_flat_order' => 'sales_order',
                'sales_flat_order_address' => 'sales_order_address',
                'sales_flat_order_item' => 'sales_order_item',
                'sales_flat_order_payment' => 'sales_order_payment',
                'sales_flat_invoice' => 'sales_invoice',
                'sales_flat_invoice_item' => 'sales_invoice_item'
        ];
    }

    /**
     * @return string
     */
    public function getDestEavDocument()
    {
        return 'eav_entity_int';
    }
}
