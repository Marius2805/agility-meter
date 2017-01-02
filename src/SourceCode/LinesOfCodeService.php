<?php
namespace Marius2805\AgilityMeter\Src\SourceCode;

use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\GitInteraction;
use Symfony\Component\Process\Process;

/**
 * Class LinesOfCodeService
 * @package Marius2805\AgilityMeter\Src\SourceCode
 */
class LinesOfCodeService extends GitInteraction
{
    /**
     * @var string
     */
    private $fileExtension;

    /**
     * LinesOfCodeService constructor.
     * @param string $fileExtension
     * @param string $directory
     */
    public function __construct(string $fileExtension, string $directory)
    {
        parent::__construct($directory);
        $this->fileExtension = $fileExtension;
    }

    /**
     * @param Commit $commit
     * @return int
     */
    public function getLinesOfCode(Commit $commit) : int
    {
        $this->checkout($commit->getHash());

        $process = new Process('cd ' . $this->directory . ' && find . -name \'*.' .  $this->fileExtension. '\' | xargs wc -l | grep -v \'total$\' | sed s/\'^ *\'//g | sed s/\' .*\'//g');
        $process->run();

        $this->checkout('master');

        return $this->buildSum($process->getOutput());
    }

    /**
     * @param string $output
     * @return int
     */
    private function buildSum(string $output) : int
    {
        $lines = explode("\n", $output);
        $lines = array_filter($lines);

        return array_sum($lines);
    }
}