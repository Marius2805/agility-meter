<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;
use Symfony\Component\Process\Process;

/**
 * Class CodeDifferenceRepository
 * @package Marius2805\AgilityMeter\Src\VersionControl
 */
class CommitDifferenceRepository
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * CommitRepository constructor.
     * @param string $directory
     * @param string $fileExtension
     */
    public function __construct(string $directory, string $fileExtension = 'php')
    {
        $this->directory = $directory;
        $this->fileExtension = strtolower($fileExtension);
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
        $fileChanges = $this->filterFileChanges($fileChanges);

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
            $changeStats = trim($cells[1]);

            if (strpos($changeStats, ' ') !== false) {
                $stats = explode(' ', $changeStats)[1];
                $addedRows = substr_count($stats, '+');
                $removedRows = substr_count($stats, '-');
            } else {
                // Empty file was added
                $addedRows = 0;
                $removedRows = 0;
            }

            $fileChanges[] = new FileChange($fileName, $addedRows, $removedRows);
        }

        return $fileChanges;
    }

    /**
     * @param FileChange[] $fileChanges
     * @return array
     */
    private function filterFileChanges(array $fileChanges) : array
    {
        return array_filter($fileChanges, function (FileChange $fileChange) {
            $fileExtension = '.' . strtolower(substr($fileChange->getFileName(), -(strlen($this->fileExtension))));
            return $fileExtension == ('.' . $this->fileExtension);
        });
    }
}