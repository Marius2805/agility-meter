<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;

use Carbon\Carbon;
use Symfony\Component\Process\Process;

/**
 * Class CommitRepository
 * @package Marius2805\AgilityMeter\VersionControl
 */
class CommitRepository
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
     * @param string $commitHash
     *
     * @return Commit
     */
    public function get(string $commitHash) : Commit
    {
        $lines = $this->getGitLog();

        for ($i = 0; $i < count($lines); $i = $i + 2) {
            $hash = $lines[$i + 1];

            if ($hash == $commitHash) {
                $date = new Carbon($lines[$i]);
                return new Commit($hash, $date);
            }
        }

        throw new \InvalidArgumentException('Commit with hash ' . $commitHash . ' not found!');
    }

    /**
     * @param string $baseHash
     * @return Commit[]
     */
    public function getSince(string $baseHash) : array
    {
        $lines = $this->getGitLog();
        return $this->parseGitLog($lines, $baseHash);
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return array
     */
    private function getGitLog() : array
    {
        $process = new Process('cd ' . $this->directory . ' && git log --all | grep -i \'^commit\|^Date\' | sed s/\'commit \'//g | sed s/\'Date: *\'//g');
        $process->run();

        $lines = explode("\n", $process->getOutput());
        $lines = array_filter($lines);
        $lines = array_reverse($lines);

        return $lines;
    }

    /**
     * @param array $lines
     * @param string $baseHash
     * @return Commit[]
     */
    private function parseGitLog(array $lines, string $baseHash) : array
    {
        $commits = [];

        $indexBaseHash = null;
        for ($i = 0; $i < count($lines); $i = $i + 2) {
            $hash = $lines[$i + 1];

            if ($hash == $baseHash) {
                $indexBaseHash = $i;
            }

            if (!is_null($indexBaseHash) && $i >= $indexBaseHash) {
                $date = new Carbon($lines[$i]);
                $commits[] = new Commit($hash, $date);
            }
        }

        return $commits;
    }
}