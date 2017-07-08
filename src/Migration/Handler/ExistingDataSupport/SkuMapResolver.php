<?php
namespace Migration\Handler\ExistingDataSupport;

use Migration\Handler\Placeholder;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;

class SkuMapResolver extends \Migration\Handler\AbstractHandler implements \Migration\Handler\HandlerInterface
{
    protected $dir;

    const DIRECTORY = 'migration-maps';
    const SKU_MAP = 'sku-map.json';
    const EID_SKU_MAP = 'eid-sku-map.json';
    const SKU_EID_MAP = 'sku-eid-map.json';

    public function __construct(\Magento\Framework\App\Filesystem\DirectoryList $dir)
    {
        $this->dir = $dir;
    }

    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if($recordToHandle->getValue($this->field)) {
            $skuMap = $this->getSkuMap();
            if($this->field === 'sku') {
                $originalSku = $recordToHandle->getValue($this->field);
                if(isset($skuMap[$originalSku])) {
                    $recordToHandle->setValue($this->field, $skuMap[$originalSku]);
                    return true;
                }
            }
            else if($this->field === 'product_id') {
                $skuMap = $this->getSkuMap();
                $originalSku = $recordToHandle->getValue('sku');
                if(isset($skuMap[$originalSku])) {
                    $newSku = $skuMap[$originalSku];
                    unset($skuMap);
                    $skuToEntityIdMap = $this->getSkuEntityIdMap();
                    if(isset($skuToEntityIdMap[$newSku])) {
                        $recordToHandle->setValue($this->field, $skuToEntityIdMap[$newSku]);
                        return true;
                    }
                }
            }
            else if($this->field === 'parent_item_id' && ($parentId = $recordToHandle->getValue($this->field))) {
                $sku = isset($this->getEntityIdSkuMap()[$parentId])? $this->getEntityIdSkuMap()[$parentId] : null;
                if($sku) {
                    $migratedEid = isset($this->getSkuEntityIdMap()[$sku])? $this->getSkuEntityIdMap()[$sku] : null;
                    if($migratedEid) {
                        $recordToHandle->setValue($this->field, $migratedEid );
                    }
                }
            }
        }
        $recordToHandle->setValue($this->field, 0);
        return false;
    }

    protected function getSkuMap() {
        if(!isset($GLOBALS['sku_map'])) {
            $GLOBALS['sku_map'] = json_decode(file_get_contents($this->dir->getRoot().'/'.self::DIRECTORY.'/'.self::SKU_MAP), true);
        }
        return  $GLOBALS['sku_map'];
    }

    protected function getSkuEntityIdMap() {
        if(!isset($GLOBALS['sku_eid_map'])) {
            $GLOBALS['sku_eid_map'] = json_decode(file_get_contents($this->dir->getRoot().'/'.self::DIRECTORY.'/'.self::SKU_EID_MAP), true);
        }
        return  $GLOBALS['sku_eid_map'];
    }

    protected function getEntityIdSkuMap() {
        if(!isset($GLOBALS['eid_map'])) {
            $GLOBALS['eid_map'] = json_decode(file_get_contents($this->dir->getRoot().'/'.self::DIRECTORY.'/'.self::SKU_EID_MAP), true);
        }
        return  $GLOBALS['eid_map'];
    }
}
