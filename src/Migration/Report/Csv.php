<?php
namespace Migration\Report;

use Magento\Framework\Filesystem\Driver\File;

class Csv extends \Magento\Framework\File\Csv
{
    protected $dir;

    public function __construct(File $file,
                                \Magento\Framework\App\Filesystem\DirectoryList $dir)
    {
        parent::__construct($file);
        $this->dir = $dir;
    }

    /**
     * Saving data row array into file
     *
     * @param   string $file
     * @param   array $data
     * @return  $this
     */
    public function saveData($file, $data, $mode='a')
    {
        if(!in_array($mode, ['a', 'w'])) {
            return $this;
        }
        $filePath = $this->dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR).'/'.$file;
        $fh = fopen($filePath, $mode);
        foreach ($data as $dataRow) {
            $this->file->filePutCsv($fh, $dataRow, $this->_delimiter, $this->_enclosure);
        }
        fclose($fh);
        return $this;
    }

    /**
     * @param string $file
     * @param string[] $data
     */
    public function stageData($file, $data) {
        if(!isset($this->stagedData[$file])) {
            $this->stagedData[$file] = [];
        }
        $this->stagedData[$file][] = $data;
    }

    /**
     * @param string $file
     */
    public function commitStaged($file) {
        if(isset($this->stagedData[$file])) {
            $toWrite = $this->stagedData[$file];
            unset($this->stagedData[$file]);
            $this->saveData($file, $toWrite, 'a');
        }
    }
}
