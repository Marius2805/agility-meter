<?php
namespace Marius2805\AgilityMeter\Tests\Agility;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\Agility\TimeFrame;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifference;
use Marius2805\AgilityMeter\Src\VersionControl\FileChange;

/**
 * Class TimeFrameTest
 * @package Marius2805\AgilityMeter\Tests\Agility
 */
class TimeFrameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TimeFrame
     */
    private $timeFrame;

    /**
     * @var CommitDifference
     */
    private $dummyCommitDiff;

    protected function setUp()
    {
        $this->timeFrame = new TimeFrame('test', Carbon::today(), Carbon::tomorrow());
        $this->dummyCommitDiff = new CommitDifference(
            new Commit('a1', new Carbon()),
            new Commit('b1', new Carbon()), [
                new FileChange('test1.php', 5, 10),
                new FileChange('test2.php', 5, 10)
            ]);
    }

    public function test_getAbsoluteTotalChanges_sumCorrect()
    {
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);

        self::assertEquals(60, $this->timeFrame->getAbsoluteTotalChanges());
    }

    public function test_getAbsoluteTotalChanges_cacheResetWhenDiffAdded()
    {
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->getAbsoluteTotalChanges();
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);

        self::assertEquals(90, $this->timeFrame->getAbsoluteTotalChanges());
    }

    public function test_getNormalizedChanges_sumCorrect()
    {
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->setCodeGrowth(10);

        self::assertEquals(50, $this->timeFrame->getNormalizedTotalChanges());
    }

    public function test_jsonSerialize_correct()
    {
        $expected = [
            'label'             => 'test',
            'start'             => Carbon::today()->format('d.m.Y'),
            'end'               => Carbon::tomorrow()->format('d.m.Y'),
            'codeGrowth'        => 10,
            'numberOfTests'     => 150,
            'absoluteChanges'   => 60,
            'normalizedChanges' => 50
        ];

        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->addCommitDiff($this->dummyCommitDiff);
        $this->timeFrame->setCodeGrowth(10);
        $this->timeFrame->setNumberOfTests(150);

        self::assertEquals($expected, $this->timeFrame->jsonSerialize());
    }
}