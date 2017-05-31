<?php
namespace Migration\Handler;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;

/**
 * Class WebsiteFromStore
 */
class CrossFieldConvert extends AbstractHandler
{
    /**
     * Map data
     *
     * @var array
     */
    protected $map;
    /**
     * @var Source
     */
    protected $source;

    protected $storeToWebsiteMap;

    protected $crossField;

    public function __construct(Config $config, Source $source, $map = '', $crossField)
    {
        $this->crossField = $crossField;
        $map = rtrim($map, ']');
        $map = ltrim($map, '[');
        $map = explode(';', $map);
        $resultMap = [];
        foreach ($map as $mapRecord) {
            $explodedRecord = explode(':', trim($mapRecord));
            if (count($explodedRecord) != 2) {
                throw new Exception('Invalid map provided to convert handler');
            }
            list($key, $value) = $explodedRecord;
            $resultMap[$key] = $value;
        }
        $this->storeToWebsiteMap = $resultMap;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $store = $recordToHandle->getValue($this->crossField);
        if(isset($this->storeToWebsiteMap[$store])) {
            $recordToHandle->setValue($this->field, $this->storeToWebsiteMap[$store]);
        }
    }
}
