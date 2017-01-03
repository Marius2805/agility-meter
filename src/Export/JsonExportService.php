<?php
namespace Marius2805\AgilityMeter\Src\Export;

use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class JsonExportService
 *
 * @package Marius2805\AgilityMeter\Src\Export
 */
class JsonExportService
{
    /**
     * @var string
     */
    private $exportDirectory;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * JsonExportService constructor.
     *
     * @param string $exportDirectory
     */
    public function __construct(string $exportDirectory)
    {
        $this->exportDirectory = $exportDirectory;
        $this->fileSystem = new Filesystem();
    }

    /**
     * @param SummaryStatistic $statistic
     */
    public function export(SummaryStatistic $statistic)
    {
        $exportFile = $this->exportDirectory . DIRECTORY_SEPARATOR . 'SummaryStatistic.json';
        $this->fileSystem->dumpFile($exportFile, json_encode($statistic, JSON_PRETTY_PRINT));
    }
}