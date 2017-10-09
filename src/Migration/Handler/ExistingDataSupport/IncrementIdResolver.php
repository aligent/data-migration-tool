<?php
namespace Migration\Handler\ExistingDataSupport;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;
/**
 * Class ConverEavValue
 */
class IncrementIdResolver extends \Migration\Handler\AbstractHandler implements \Migration\Handler\HandlerInterface
{
    const MIGRATION_PREFIX = 'M1R_';

    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if($recordToHandle->getValue($this->field)) {
            $recordToHandle->setValue($this->field, self::MIGRATION_PREFIX.$recordToHandle->getValue($this->field));
        }
        else {
            $recordToHandle->setValue($this->field, $recordToHandle->getValue($this->field));
        }
    }
}
