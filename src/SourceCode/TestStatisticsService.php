<?php
namespace Marius2805\AgilityMeter\Src\SourceCode;

use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\GitInteraction;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Class TestStatisticsService
 * @package Marius2805\AgilityMeter\Src\SourceCode
 */
class TestStatisticsService extends GitInteraction
{
    /**
     * @param Commit $commit
     * @return int
     */
    public function getNumberOfTests(Commit $commit) : int
    {
        $this->checkout($commit->getHash());

        $testDirectories = $this->getAllTestDirectories();

        $process = new Process('phploc --count-tests ' . implode(' ', $testDirectories) . ' | tail -n1 | sed s/\'.* \'//g');
        $process->run();

        $this->checkout('master');
        return (int) trim($process->getOutput());
    }

    /**
     * @return array
     */
    private function getAllTestDirectories() : array
    {
        $directories = [];

        $finder = new Finder();
        $finder->directories()->in($this->directory)
            ->name('/test/i')
            ->name('/tests/i');

        foreach ($finder->getIterator() as $fileInfo) {
            $directories[] = $fileInfo->getRealPath();
        }

        return $directories;
    }
}