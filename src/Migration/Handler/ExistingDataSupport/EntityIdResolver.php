<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\ExistingDataSupport;

use Migration\Handler\Placeholder;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;

/**
 * Class ConverEavValue
 */
class EntityIdResolver extends \Migration\Handler\AbstractHandler implements \Migration\Handler\HandlerInterface
{
    const CURRENT = PHP_INT_MAX;

    protected $map;
    /**
     * @var Source
     */
    protected $source;

    //TODO MAKE DI FOR REUSE
    protected $incrementMap = [
        'customer_entity.entity_id' => 3000000,
        'customer_address_entity.entity_id' => 3000000,
        'newsletter_subscriber.subscriber_id' => 3000000,
        'sales_flat_order.entity_id' => 30000000,
        'sales_flat_order_address.entity_id' => 30000000,
        'sales_flat_order_payment.entity_id' => 30000000,
        'sales_flat_order_item.item_id' => 30000000,
        'sales_flat_invoice.entity_id' => 30000000,
        'sales_flat_invoice_item.entity_id' => 30000000
    ];

    protected $versionedIncrementMap = [
        'customer_entity.entity_id' => [
            '0' => 2000000,
            self::CURRENT => 3000000
        ],
        'customer_address_entity.entity_id' => [
            '0' => 2000000,
            self::CURRENT  => 3000000
        ]
    ];

    protected $fkMap = [
        'customer_entity.default_billing' => 'customer_address_entity.entity_id',
        'customer_entity.default_shipping' => 'customer_address_entity.entity_id',
        'customer_address_entity.parent_id' => 'customer_entity.entity_id'
    ];

    protected $relatedKey;
    
    public function __construct(Config $config, Source $source, $relatedKey = null)
    {
        $this->relatedKey = $relatedKey;
    }

    public function getReverseIncrement($table, $field)
    {
        return -$this->getIncrement($table, $field);
    }

    public function getIncrement($table, $field)
    {
        $key = $table.'.'.$field;
        if(isset($this->fkMap[$key])) {
            $key = $this->fkMap[$key];
        }
        if(isset($this->incrementMap[$key])) {
            return $this->incrementMap[$key];
        }
        return 0;
    }

    /**
     * @inheritdoc
     * todo refactor to use increment map instead of parameter.
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $incrementBy = 0;

        if($recordToHandle->getValue($this->field)) {
            // if is a related key, lookup what the relative key has been implemented by
            if ($this->relatedKey && isset($this->versionedIncrementMap[$this->relatedKey])) {
                foreach ($this->versionedIncrementMap[$this->relatedKey] as $incVersion => $incValue) {
                    if($recordToHandle->getValue($this->field) < $incVersion) {
                        $incrementBy = $incValue;
                        break;
                    }
                }
            }
            elseif ($this->relatedKey && isset($this->incrementMap[$this->relatedKey])) {
                $incrementBy = $this->incrementMap[$this->relatedKey];
            }
            // No related key given, and therefore is incrementing self
            elseif (!$this->relatedKey && isset($this->incrementMap[$recordToHandle->getDocument()->getName() . '.' . $this->field])) {
                $incrementBy = $this->incrementMap[$recordToHandle->getDocument()->getName() . '.' . $this->field];
            }
        }
        //$oppositeRecord->setValue($this->field, $recordToHandle->getValue($this->field) + $incrementBy);
        $recordToHandle->setValue($this->field, $recordToHandle->getValue($this->field) + $incrementBy);
    }
}
