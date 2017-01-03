<?php
namespace Marius2805\AgilityMeter\Tests\Export;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;
use Marius2805\AgilityMeter\Src\Export\JsonExportService;
use Marius2805\AgilityMeter\Tests\General\RepositoryIntegrationTestCase;

/**
 * Class JsonExportServiceIntegrationTest
 *
 * @package Marius2805\AgilityMeter\Tests\Export
 */
class JsonExportServiceIntegrationTest extends RepositoryIntegrationTestCase
{
    /**
     * @var JsonExportService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new JsonExportService($this->testFolder);
    }

    public function test_export_fileCorrect()
    {
        $statistic = new SummaryStatistic(100, 200, [
            new TimeFrame('test1', Carbon::today(), Carbon::tomorrow()),
            new TimeFrame('test2', Carbon::today(), Carbon::tomorrow())
        ]);
        $this->service->export($statistic);

        $result = file_get_contents($this->testFolder . '/SummaryStatistic.json');
        self::assertEquals(json_encode($statistic->jsonSerialize(), JSON_PRETTY_PRINT), $result);
    }
}