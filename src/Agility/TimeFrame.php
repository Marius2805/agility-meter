<?php
namespace Marius2805\AgilityMeter\Src\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifference;

/**
 * Class TimeFrame
 * @package Marius2805\AgilityMeter\Src\Agility
 */
class TimeFrame implements \JsonSerializable
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
    private $commitDiffs = [];

    /**
     * @var int
     */
    private $numberOfTests = 0;

    /**
     * @var int
     */
    private $codeGrowth = 0;

    /**
     * @var int
     */
    private $absoluteTotalChanges;

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
        $this->absoluteTotalChanges = null;
        $this->commitDiffs[] = $commitDifference;
    }

    /**
     * @param int $numberOfTests
     */
    public function setNumberOfTests(int $numberOfTests)
    {
        $this->numberOfTests = $numberOfTests;
    }

    /**
     * @return int
     */
    public function getNumberOfTests(): int
    {
        return $this->numberOfTests;
    }

    /**
     * Get proportionately average code growth
     *
     * @return int
     */
    public function getCodeGrowth(): int
    {
        return $this->codeGrowth;
    }

    /**
     * @param int $codeGrowth
     */
    public function setCodeGrowth(int $codeGrowth)
    {
        $this->codeGrowth = $codeGrowth;
    }

    /**
     * @return int
     */
    public function getAbsoluteTotalChanges(): int
    {
        if ($this->absoluteTotalChanges == null) {
            $this->absoluteTotalChanges = 0;

            foreach ($this->commitDiffs as $commitDiff) {
                foreach ($commitDiff->getFileChanges() as $fileChange) {
                    $this->absoluteTotalChanges += $fileChange->getAddedRows();
                    $this->absoluteTotalChanges += $fileChange->getRemovedRows();
                }
            }
        }

        return $this->absoluteTotalChanges;
    }

    /**
     * @return int
     */
    public function getNormalizedTotalChanges(): int
    {
        return $this->getAbsoluteTotalChanges() - $this->codeGrowth;
    }

    /**
     * @inheritdoc
     */
    function jsonSerialize()
    {
        return [
            'label'             => $this->label,
            'start'             => $this->start->format('d.m.Y'),
            'end'               => $this->end->format('d.m.Y'),
            'codeGrowth'        => $this->getCodeGrowth(),
            'numberOfTests'     => $this->numberOfTests,
            'absoluteChanges'   => $this->getAbsoluteTotalChanges(),
            'normalizedChanges' => $this->getNormalizedTotalChanges()
        ];
    }
}