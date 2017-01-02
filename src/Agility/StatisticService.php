<?php
namespace Marius2805\AgilityMeter\Src\Agility;

use Assert\Assertion;
use Marius2805\AgilityMeter\Src\SourceCode\LinesOfCodeService;
use Marius2805\AgilityMeter\Src\SourceCode\TestStatisticsService;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifferenceRepository;
use Marius2805\AgilityMeter\Src\VersionControl\CommitRepository;

/**
 * Class StatisticService
 * @package Marius2805\AgilityMeter\Src\Agility
 */
class StatisticService
{
    /**
     * @var CommitRepository
     */
    private $commitRepository;

    /**
     * @var CommitDifferenceRepository
     */
    private $differenceRepository;

    /**
     * @var LinesOfCodeService
     */
    private $linesOfCodeService;

    /**
     * @var TestStatisticsService
     */
    private $testStatisticsService;

    /**
     * @var TimeFrameFactory
     */
    private $timeFrameFactory;

    /**
     * StatisticService constructor.
     * @param CommitRepository $commitRepository
     * @param CommitDifferenceRepository $differenceRepository
     * @param LinesOfCodeService $linesOfCodeService
     * @param TestStatisticsService $testStatisticsService
     * @internal param TimeFrameFactory $timeFrameFactory
     */
    public function __construct(CommitRepository $commitRepository, CommitDifferenceRepository $differenceRepository, LinesOfCodeService $linesOfCodeService, TestStatisticsService $testStatisticsService, TimeFrameFactory $timeFrameFactory = null)
    {
        $this->commitRepository = $commitRepository;
        $this->differenceRepository = $differenceRepository;
        $this->linesOfCodeService = $linesOfCodeService;
        $this->testStatisticsService = $testStatisticsService;
        $this->timeFrameFactory = $timeFrameFactory ?: new TimeFrameFactory();
    }

    /**
     * @param Commit $baseCommit
     * @param string $interval
     * @return SummaryStatistic
     */
    public function getStatistic(Commit $baseCommit, string $interval) : SummaryStatistic
    {
        Assertion::inArray($interval, [TimeFrameInterval::CALENDAR_MONTH], 'Invalid time frame interval given');

        $timeFrames = $this->timeFrameFactory->getMonthInterval($baseCommit);
        $commits = $this->commitRepository->getSince($baseCommit->getHash());

        if (empty($commits)) {
            throw new \RuntimeException("No commits found for base Commit " . $baseCommit->getHash());
        }

        $this->distributeCommits($timeFrames, $commits);
        $this->determineNumberOfTests($timeFrames);

        $startLoc = $this->linesOfCodeService->getLinesOfCode($commits[0]);
        $endLoc = $this->linesOfCodeService->getLinesOfCode(end($commits));
        $summaryStatistic =  new SummaryStatistic($startLoc, $endLoc, $timeFrames);

        $this->calculateCodeGrowth($summaryStatistic, $commits);

        return $summaryStatistic;
    }

    /**
     * @param TimeFrame[] $timeFrames
     * @param Commit[] $commits
     */
    private function distributeCommits(array $timeFrames, array $commits)
    {
        $currentFrame = $timeFrames[0];
        $currentIndex = 0;

        for ($a = 1; $a < count($commits); $a++) {
            $diff = $this->differenceRepository->getDifference($commits[$a - 1], $commits[$a]);

            for ($b = $currentIndex; $diff->getCurrentCommit()->getDate() > $currentFrame->getEnd(); $b++) {
                $currentFrame = $timeFrames[$b];
            }

            $currentFrame->addCommitDiff($diff);
        }
    }

    /**
     * @param TimeFrame[] $timeFrames
     */
    private function determineNumberOfTests(array $timeFrames)
    {
        foreach ($timeFrames as $i => $timeFrame) {
            if (!empty($timeFrame->getCommitDiffs())) {
                $commitDiffs = $timeFrame->getCommitDiffs();
                $lastCommit = end($commitDiffs)->getCurrentCommit();
                $numberOfTests = $this->testStatisticsService->getNumberOfTests($lastCommit);
                $timeFrame->setNumberOfTests($numberOfTests);
            } elseif ($i > 0) {
                $lastNumberOfTests = $timeFrames[$i - 1]->getNumberOfTests();
                $timeFrame->setNumberOfTests($lastNumberOfTests);
            }
        }
    }

    /**
     * @param SummaryStatistic $statistic
     * @param Commit[] $commits
     */
    private function calculateCodeGrowth(SummaryStatistic $statistic, array $commits)
    {
        $totalDifference = $statistic->getTotalGrowth();
        $totalDayDelta = $commits[0]->getDate()->diffInDays(end($commits)->getDate());

        if ($totalDayDelta > 0) {
            $growthPerDay = $totalDifference / $totalDayDelta;

            foreach ($statistic->getTimeFrames() as $timeFrame) {
                $frameDayDelta = $timeFrame->getStart()->diffInDays($timeFrame->getEnd()) + 1;
                $timeFrame->setCodeGrowth($frameDayDelta * $growthPerDay);
            }
        }
    }
}