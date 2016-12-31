<?php
namespace Marius2805\AgilityMeter\Src\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifference;

/**
 * Class TimeFrame
 * @package Marius2805\AgilityMeter\Src\Agility
 */
class TimeFrame
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var Carbon
     */
    private $start;

    /**
     * @var Carbon
     */
    private $end;

    /**
     * @var CommitDifference[]
     */
    private $commitDiffs;

    /**
     * TimeFrame constructor.
     * @param string $label
     * @param Carbon $start
     * @param Carbon $end
     */
    public function __construct(string $label, Carbon $start, Carbon $end)
    {
        $this->label = $label;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return Carbon
     */
    public function getStart(): Carbon
    {
        return $this->start;
    }

    /**
     * @return Carbon
     */
    public function getEnd(): Carbon
    {
        return $this->end;
    }

    /**
     * @return CommitDifference[]
     */
    public function getCommitDiffs(): array
    {
        return $this->commitDiffs;
    }

    /**
     * @param CommitDifference $commitDifference
     */
    public function addCommitDiff(CommitDifference $commitDifference)
    {
        $this->commitDiffs[] = $commitDifference;
    }
}