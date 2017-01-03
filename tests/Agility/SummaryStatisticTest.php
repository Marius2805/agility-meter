<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;

/**
 * Class SummaryStatisticTest
 * @package Marius2805\AgilityMeter\Tests\Agility
 */
class SummaryStatisticTest extends \PHPUnit_Framework_TestCase
{
    public function test_getTotalGrowth_correctPositiveDelta()
    {
        $statistic = new SummaryStatistic(100, 300, []);
        self::assertEquals(200, $statistic->getTotalGrowth());
    }

    public function test_getTotalGrowth_correctNegativeDelta()
    {
        $statistic = new SummaryStatistic(1000, 900, []);
        self::assertEquals(-100, $statistic->getTotalGrowth());
    }

    public function test_getAbsoluteChanges_sumCorrect()
    {
        $statistic = new SummaryStatistic(0, 0, [
            $this->getTimeFrame(100),
            $this->getTimeFrame(200)
        ]);

        self::assertEquals(300, $statistic->getAbsoluteChanges());
    }

    public function test_getNormalizedChanges_sumCorrect()
    {
        $statistic = new SummaryStatistic(100, 300, [
            $this->getTimeFrame(1000),
            $this->getTimeFrame(2000)
        ]);

        self::assertEquals(2800, $statistic->getNormalizedChanges());
    }

    public function test_getChangeRatio_valueCorrect()
    {
        $statistic = new SummaryStatistic(39000, 40000, [
            $this->getTimeFrame(1000),
            $this->getTimeFrame(2000)
        ]);

        self::assertEquals(0.05, $statistic->getChangeRatio());
    }

    public function test_getTestCoverageRatio_valueCorrect()
    {
        $statistic = new SummaryStatistic(39000, 40000, [
            $this->getTimeFrame(1000, 100),
            $this->getTimeFrame(2000, 1000)
        ]);

        self::assertEquals(0.025, $statistic->getTestCoverageRatio());
    }

    /**
     * @param int $absoluteChanges
     * @param int $numberOfTests
     * @return TimeFrame|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTimeFrame(int $absoluteChanges, int $numberOfTests = 0) : TimeFrame
    {
        $timeFrame = $this->getMockBuilder(TimeFrame::class)->disableOriginalConstructor()->getMock();
        $timeFrame->method('getAbsoluteTotalChanges')->willReturn($absoluteChanges);
        $timeFrame->method('getNumberOfTests')->willReturn($numberOfTests);

        return $timeFrame;
    }
}