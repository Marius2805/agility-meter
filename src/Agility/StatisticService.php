<?php
namespace Marius2805\AgilityMeter\Src\Agility;

use Assert\Assertion;
use Marius2805\AgilityMeter\Src\SourceCode\LinesOfCodeService;
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
     * @var TimeFrameFactory
     */
    private $timeFrameFactory;

    /**
     * StatisticService constructor.
     * @param CommitRepository $commitRepository
     * @param CommitDifferenceRepository $differenceRepository
     * @param LinesOfCodeService $linesOfCodeService
     * @param TimeFrameFactory $timeFrameFactory
     */
    public function __construct(CommitRepository $commitRepository, CommitDifferenceRepository $differenceRepository, LinesOfCodeService $linesOfCodeService, TimeFrameFactory $timeFrameFactory = null)
    {
        $this->commitRepository = $commitRepository;
        $this->differenceRepository = $differenceRepository;
        $this->linesOfCodeService = $linesOfCodeService;
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

        $startLoc = $this->linesOfCodeService->getLinesOfCode($commits[0]);
        $endLoc = $this->linesOfCodeService->getLinesOfCode(end($commits));
        return new SummaryStatistic($startLoc, $endLoc, $timeFrames);
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
}