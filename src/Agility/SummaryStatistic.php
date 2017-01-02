<?php
namespace Marius2805\AgilityMeter\Src\Agility;

/**
 * Class SummaryStatistic
 * @package Marius2805\AgilityMeter\Src\Agility
 */
class SummaryStatistic
{
    /**
     * @var int
     */
    private $startLinesOfCode;

    /**
     * @var int
     */
    private $endLinesOfCode;

    /**
     * @var TimeFrame[]
     */
    private $timeFrames;

    /**
     * @var int
     */
    private $absoluteChanges;

    /**
     * @var int
     */
    private $normalizedChanges;

    /**
     * SummaryStatistic constructor.
     * @param int $startLinesOfCode
     * @param int $endLinesOfCode
     * @param TimeFrame[] $timeFrames
     */
    public function __construct(int $startLinesOfCode, int $endLinesOfCode, array $timeFrames)
    {
        $this->startLinesOfCode = $startLinesOfCode;
        $this->endLinesOfCode = $endLinesOfCode;
        $this->timeFrames = $timeFrames;
    }

    /**
     * @return int
     */
    public function getStartLinesOfCode(): int
    {
        return $this->startLinesOfCode;
    }

    /**
     * @return int
     */
    public function getEndLinesOfCode(): int
    {
        return $this->endLinesOfCode;
    }

    /**
     * @return TimeFrame[]
     */
    public function getTimeFrames(): array
    {
        return $this->timeFrames;
    }

    /**
     * Get lines of codes delta between start and end
     *
     * @return int
     */
    public function getTotalGrowth() : int
    {
        return $this->endLinesOfCode - $this->startLinesOfCode;
    }

    /**
     * @return int
     */
    public function getAbsoluteChanges(): int
    {
        if ($this->absoluteChanges == null) {
            $this->absoluteChanges = 0;

            foreach ($this->timeFrames as $timeFrame) {
                $this->absoluteChanges += $timeFrame->getAbsoluteTotalChanges();
            }
        }

        return $this->absoluteChanges;
    }

    /**
     * @return int
     */
    public function getNormalizedChanges(): int
    {
        if ($this->normalizedChanges == null) {
            $this->normalizedChanges = $this->getAbsoluteChanges() - $this->getTotalGrowth();
        }

        return $this->normalizedChanges;
    }
}