<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;
use Symfony\Component\Process\Process;

/**
 * Class CodeDifferenceRepository
 * @package Marius2805\AgilityMeter\Src\VersionControl
 */
class CodeDifferenceRepository
{
    /**
     * @var string
     */
    private $directory;

    /**
     * CommitRepository constructor.
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Commit $previous
     * @param Commit $current
     * @return CommitDifference
     */
    public function getDifference(Commit $previous, Commit $current) : CommitDifference
    {
        $process = new Process('cd ' . $this->directory . ' && git diff --stat  ' . $previous->getHash() . ' ' . $current->getHash() . ' | grep \'|\' | grep -v \'| Bin\'');
        $process->run();
        $fileChanges = $this->parseGitDiff($process->getOutput());

        return new CommitDifference($previous, $current, $fileChanges);
    }

    /**
     * @param string $output
     * @return FileChange[]
     */
    private function parseGitDiff(string $output) : array
    {
        $fileChanges = [];
        $lines = explode("\n", $output);
        $lines = array_filter($lines);

        foreach ($lines as $line) {
            $cells = explode('|', $line);
            $fileName = trim($cells[0]);

            $stats = explode(' ', trim($cells[1]))[1];
            $addedRows = substr_count($stats, '+');
            $removedRows = substr_count($stats, '-');

            $fileChanges[] = new FileChange($fileName, $addedRows, $removedRows);
        }

        return $fileChanges;
    }
}