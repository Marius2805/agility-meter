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

    /**
     * @param int $absoluteChanges
     * @return TimeFrame|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTimeFrame(int $absoluteChanges) : TimeFrame
    {
        $timeFrame = $this->getMockBuilder(TimeFrame::class)->disableOriginalConstructor()->getMock();
        $timeFrame->method('getAbsoluteTotalChanges')->willReturn($absoluteChanges);

        return $timeFrame;
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
}