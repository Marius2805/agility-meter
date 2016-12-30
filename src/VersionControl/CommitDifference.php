<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;

/**
 * Class CodeDifference
 * @package Marius2805\AgilityMeter\Src\VersionControl
 */
class CommitDifference
{
    /**
     * @var Commit
     */
    private $previousCommit;

    /**
     * @var Commit
     */
    private $currentCommit;

    /**
     * @var FileChange[]
     */
    private $fileChanges;

    /**
     * CodeDifference constructor.
     * @param Commit $previousCommit
     * @param Commit $currentCommit
     * @param array $fileChanges
     */
    public function __construct(Commit $previousCommit, Commit $currentCommit, array $fileChanges)
    {
        $this->previousCommit = $previousCommit;
        $this->currentCommit = $currentCommit;
        $this->fileChanges = $fileChanges;
    }

    /**
     * @return Commit
     */
    public function getPreviousCommit(): Commit
    {
        return $this->previousCommit;
    }

    /**
     * @return Commit
     */
    public function getCurrentCommit(): Commit
    {
        return $this->currentCommit;
    }

    /**
     * @return FileChange[]
     */
    public function getFileChanges(): array
    {
        return $this->fileChanges;
    }
}