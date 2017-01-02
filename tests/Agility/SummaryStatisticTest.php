<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;

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
}