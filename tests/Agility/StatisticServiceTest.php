<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\Agility\StatisticService;
use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameFactory;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameInterval;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifference;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifferenceRepository;
use Marius2805\AgilityMeter\Src\VersionControl\CommitRepository;

/**
 * Class StatisticServiceTest
 * @package Marius2805\AgilityMeter\Tests\Agility
 */
class StatisticServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StatisticService
     */
    private $service;

    /**
     * @var CommitRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commitRepository;

    /**
     * @var CommitDifferenceRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commitDifferenceRepository;

    protected function setUp()
    {
        $this->commitRepository = $this->getMockBuilder(CommitRepository::class)->disableOriginalConstructor()->getMock();
        $this->commitDifferenceRepository = $this->getMockBuilder(CommitDifferenceRepository::class)->disableOriginalConstructor()->getMock();

        $this->commitDifferenceRepository->method('getDifference')->will(self::returnCallback(function (Commit $previous, Commit $current) : CommitDifference {
            return new CommitDifference($previous, $current, []);
        }));

        $this->service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid time frame interval given
     */
    public function test_getStatistic_wrongTimeFrameInterval_exception()
    {
        $this->service->getStatistic(new Commit('123', new Carbon()), 'wrong');
    }

    public function test_getStatistic_wrongTimeFrameInterval_timeFrameFactoryCalled()
    {
        $baseCommit = new Commit('123', Carbon::today());

        $timeFrameFactory = $this->getMockBuilder(TimeFrameFactory::class)->disableOriginalConstructor()->getMock();
        $timeFrameFactory->expects(self::once())
            ->method('getMonthInterval')
            ->with($baseCommit)
            ->willReturn([new TimeFrame('test', Carbon::today(), Carbon::today())]);

        $this->commitRepository->method('getSince')->willReturn([]);

        $service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $timeFrameFactory);
        $service->getStatistic($baseCommit, TimeFrameInterval::CALENDAR_MONTH);
    }

    public function test_getStatistic_correctDiffs_sameMonth()
    {
        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) : array {
            return [
                new Commit('a1', Carbon::today()->day(5)->subMonths(1)),
                new Commit('b2', Carbon::today()->day(10)->subMonths(1)),
                new Commit('c3', Carbon::today()->day(15)->subMonths(1))
            ];
        }));

        $statistic = $this->service->getStatistic(new Commit('123', Carbon::today()->day(5)->subMonths(1)), TimeFrameInterval::CALENDAR_MONTH);
        self::assertInstanceOf(SummaryStatistic::class, $statistic);
        self::assertCount(2, $statistic->getTimeFrames());
        self::assertCount(2, $statistic->getTimeFrames()[0]->getCommitDiffs());
        self::assertEquals('a1', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getPreviousCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getCurrentCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[0]->getCommitDiffs()[1]->getPreviousCommit()->getHash());
        self::assertEquals('c3', $statistic->getTimeFrames()[0]->getCommitDiffs()[1]->getCurrentCommit()->getHash());
    }

    public function test_getStatistic_correctDiffs_nextMonth()
    {
        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) : array {
            return [
                new Commit('a1', Carbon::today()->day(5)->subMonths(1)),
                new Commit('b2', Carbon::today()->day(10)->subMonths(1)),
                new Commit('c3', Carbon::today()->day(10))
            ];
        }));

        $statistic = $this->service->getStatistic(new Commit('123', Carbon::today()->day(5)->subMonths(1)), TimeFrameInterval::CALENDAR_MONTH);
        self::assertInstanceOf(SummaryStatistic::class, $statistic);
        self::assertCount(2, $statistic->getTimeFrames());
        self::assertCount(1, $statistic->getTimeFrames()[0]->getCommitDiffs());
        self::assertCount(1, $statistic->getTimeFrames()[1]->getCommitDiffs());
        self::assertEquals('a1', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getPreviousCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getCurrentCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[1]->getCommitDiffs()[0]->getPreviousCommit()->getHash());
        self::assertEquals('c3', $statistic->getTimeFrames()[1]->getCommitDiffs()[0]->getCurrentCommit()->getHash());
    }

    public function test_getStatistic_correctDiffs_monthAfterNextMonth()
    {
        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) : array {
            return [
                new Commit('a1', Carbon::today()->day(5)->subMonths(2)),
                new Commit('b2', Carbon::today()->day(10)->subMonths(2)),
                new Commit('c3', Carbon::today()->day(10)),
                new Commit('d4', Carbon::today()->day(15))
            ];
        }));

        $statistic = $this->service->getStatistic(new Commit('123', Carbon::today()->day(5)->subMonths(2)), TimeFrameInterval::CALENDAR_MONTH);
        self::assertInstanceOf(SummaryStatistic::class, $statistic);
        self::assertCount(3, $statistic->getTimeFrames());
        self::assertCount(1, $statistic->getTimeFrames()[0]->getCommitDiffs());
        self::assertCount(0, $statistic->getTimeFrames()[1]->getCommitDiffs());
        self::assertCount(2, $statistic->getTimeFrames()[2]->getCommitDiffs());
        self::assertEquals('a1', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getPreviousCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[0]->getCommitDiffs()[0]->getCurrentCommit()->getHash());
        self::assertEquals('b2', $statistic->getTimeFrames()[2]->getCommitDiffs()[0]->getPreviousCommit()->getHash());
        self::assertEquals('c3', $statistic->getTimeFrames()[2]->getCommitDiffs()[0]->getCurrentCommit()->getHash());
        self::assertEquals('c3', $statistic->getTimeFrames()[2]->getCommitDiffs()[1]->getPreviousCommit()->getHash());
        self::assertEquals('d4', $statistic->getTimeFrames()[2]->getCommitDiffs()[1]->getCurrentCommit()->getHash());
    }
}