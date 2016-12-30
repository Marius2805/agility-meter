<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;

/**
 * Class FileChange
 * @package Marius2805\AgilityMeter\Src\VersionControl
 */
class FileChange
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int
     */
    private $addedRows;

    /**
     * @var int
     */
    private $removedRows;

    /**
     * FileChange constructor.
     * @param string $fileName
     * @param int $addedRows
     * @param int $removedRows
     */
    public function __construct(string $fileName, int $addedRows, int $removedRows)
    {
        $this->fileName = $fileName;
        $this->addedRows = $addedRows;
        $this->removedRows = $removedRows;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return int
     */
    public function getAddedRows(): int
    {
        return $this->addedRows;
    }

    /**
     * @return int
     */
    public function getRemovedRows(): int
    {
        return $this->removedRows;
    }
}