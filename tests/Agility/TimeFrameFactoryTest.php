<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameFactory;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;

/**
 * Class TimeFrameFactoryTest
 * @package Marius2805\AgilityMeter\Tests\Agility
 */
class TimeFrameFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TimeFrameFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new TimeFrameFactory();
    }

    public function test_getMonthInterval_startCorrect()
    {
        $commit = new Commit('123', ($this->getCommitDate()));
        $timeFrames = $this->factory->getMonthInterval($commit);

        self::assertCount(3, $timeFrames);
        self::assertInstanceOf(TimeFrame::class, $timeFrames[0]);
        self::assertEquals(Carbon::today()->day(1)->subMonths(2)->startOfMonth(), $timeFrames[0]->getStart());
        self::assertEquals(Carbon::today()->day(1)->subMonths(1)->startOfMonth(), $timeFrames[1]->getStart());
        self::assertEquals(Carbon::today()->day(1)->startOfMonth(), $timeFrames[2]->getStart());
    }

    public function test_getMonthInterval_endCorrect()
    {
        $commit = new Commit('123', ($this->getCommitDate()));
        $timeFrames = $this->factory->getMonthInterval($commit);

        self::assertCount(3, $timeFrames);
        self::assertEquals(Carbon::today()->day(1)->subMonths(2)->endOfMonth(), $timeFrames[0]->getEnd());
        self::assertEquals(Carbon::today()->day(1)->subMonths(1)->endOfMonth(), $timeFrames[1]->getEnd());
        self::assertEquals(Carbon::today()->day(1)->endOfMonth(), $timeFrames[2]->getEnd());
    }

    public function test_getMonthInterval_labelCorrect()
    {
        $commit = new Commit('123', (new Carbon('01.01.2016')));
        $timeFrames = $this->factory->getMonthInterval($commit);

        self::assertNotEmpty($timeFrames);
        self::assertEquals('Jan 16', $timeFrames[0]->getLabel());
    }

    /**
     * @return Carbon
     */
    private function getCommitDate() : Carbon
    {
        return Carbon::today()->day(5)->subMonths(2);
    }
}