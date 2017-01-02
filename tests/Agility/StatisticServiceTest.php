<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\Agility\StatisticService;
use Marius2805\AgilityMeter\Src\Agility\SummaryStatistic;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameFactory;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameInterval;
use Marius2805\AgilityMeter\Src\SourceCode\LinesOfCodeService;
use Marius2805\AgilityMeter\Src\SourceCode\TestStatisticsService;
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

    /**
     * @var LinesOfCodeService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linesOfCodeService;

    /**
     * @var TestStatisticsService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testStatisticsService;

    protected function setUp()
    {
        $this->commitRepository = $this->getMockBuilder(CommitRepository::class)->disableOriginalConstructor()->getMock();
        $this->commitDifferenceRepository = $this->getMockBuilder(CommitDifferenceRepository::class)->disableOriginalConstructor()->getMock();

        $this->commitDifferenceRepository->method('getDifference')->will(self::returnCallback(function (Commit $previous, Commit $current) : CommitDifference {
            return new CommitDifference($previous, $current, []);
        }));

        $this->linesOfCodeService = $this->getMockBuilder(LinesOfCodeService::class)->disableOriginalConstructor()->getMock();
        $this->linesOfCodeService->method('getLinesOfCode')->willReturn(0);

        $this->testStatisticsService = $this->getMockBuilder(TestStatisticsService::class)->disableOriginalConstructor()->getMock();
        $this->testStatisticsService->method('getNumberOfTests')->willReturn(0);

        $this->service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $this->linesOfCodeService, $this->testStatisticsService);
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

        $this->commitRepository->method('getSince')->willReturn([new Commit('dummy', new Carbon())]);

        $service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $this->linesOfCodeService, $this->testStatisticsService, $timeFrameFactory);
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

    public function test_getStatistic_correctLinesOfCode()
    {
        $this->linesOfCodeService = $this->getMockBuilder(LinesOfCodeService::class)->disableOriginalConstructor()->getMock();
        $this->linesOfCodeService->method('getLinesOfCode')->will(self::returnCallback(function (Commit $commit) : int {
            switch ($commit->getHash()) {
                case 'a1':
                    return 10;
                    break;
                case 'c3':
                    return 30;
                    break;
                default:
                    return 0;
            }
        }));

        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) : array {
            return [
                new Commit('a1', Carbon::today()->day(5)),
                new Commit('b2', Carbon::today()->day(10)),
                new Commit('c3', Carbon::today()->day(15))
            ];
        }));

        $this->service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $this->linesOfCodeService, $this->testStatisticsService);

        $statistic = $this->service->getStatistic(new Commit('123', Carbon::today()->day(1)), TimeFrameInterval::CALENDAR_MONTH);
        self::assertEquals(10, $statistic->getStartLinesOfCode());
        self::assertEquals(30, $statistic->getEndLinesOfCode());
    }

    public function test_getStatistic_correctNumberOfTests()
    {
        $this->testStatisticsService = $this->getMockBuilder(TestStatisticsService::class)->disableOriginalConstructor()->getMock();
        $this->testStatisticsService->method('getNumberOfTests')->will(self::returnCallback(function (Commit $commit) : int {
            switch ($commit->getHash()) {
                case 'b2':
                    return 100;
                    break;
                case 'd4':
                    return 300;
                    break;
                default:
                    return 0;
            }
        }));

        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) : array {
            return [
                new Commit('a1', Carbon::today()->day(5)->subMonths(2)),
                new Commit('b2', Carbon::today()->day(10)->subMonths(2)),
                new Commit('c3', Carbon::today()->day(10)),
                new Commit('d4', Carbon::today()->day(15))
            ];
        }));

        $this->service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $this->linesOfCodeService, $this->testStatisticsService);

        $statistic = $this->service->getStatistic(new Commit('123', Carbon::today()->day(5)->subMonths(2)), TimeFrameInterval::CALENDAR_MONTH);
        self::assertCount(3, $statistic->getTimeFrames());
        self::assertEquals(100, $statistic->getTimeFrames()[0]->getNumberOfTests());
        self::assertEquals(100, $statistic->getTimeFrames()[1]->getNumberOfTests());
        self::assertEquals(300, $statistic->getTimeFrames()[2]->getNumberOfTests());
    }

    public function test_getStatistic_correctAverageCodeGrowth()
    {
        /** @var Commit[] $commits */
        $commits = [
            new Commit('a1', new Carbon('15.01.2016')),
            new Commit('b2', new Carbon('20.01.2016')),
            new Commit('c3', new Carbon('25.02.2016')),
            new Commit('d4', new Carbon('05.03.2016'))
        ];

        $this->commitRepository->method('getSince')->will(self::returnCallback(function (string $baseHash) use ($commits) : array {
            return $commits;
        }));

        $this->linesOfCodeService = $this->getMockBuilder(LinesOfCodeService::class)->disableOriginalConstructor()->getMock();
        $this->linesOfCodeService->method('getLinesOfCode')->will(self::returnCallback(function (Commit $commit) : int {
            switch ($commit->getHash()) {
                case 'a1':
                    return 1000;
                    break;
                case 'd4':
                    return 2000;
                    break;
                default:
                    return 0;
            }
        }));

        $this->service = new StatisticService($this->commitRepository, $this->commitDifferenceRepository, $this->linesOfCodeService, $this->testStatisticsService);

        $statistic = $this->service->getStatistic($commits[0], TimeFrameInterval::CALENDAR_MONTH);
        self::assertInstanceOf(SummaryStatistic::class, $statistic);
        self::assertGreaterThan(3, $statistic->getTimeFrames());

        self::assertEquals(620, $statistic->getTimeFrames()[0]->getCodeGrowth());
        self::assertEquals(580, $statistic->getTimeFrames()[1]->getCodeGrowth());
        self::assertEquals(620, $statistic->getTimeFrames()[2]->getCodeGrowth());
    }
}