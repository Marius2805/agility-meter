<?php
namespace Marius2805\AgilityMeter\Src\SourceCode;

use GitWrapper\GitWrapper;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Symfony\Component\Process\Process;

/**
 * Class LinesOfCodeService
 * @package Marius2805\AgilityMeter\Src\SourceCode
 */
class LinesOfCodeService
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
     * LinesOfCodeService constructor.
     * @param string $fileExtension
     * @param string $directory
     */
    public function __construct(string $fileExtension, string $directory)
    {
        $this->fileExtension = $fileExtension;
        $this->directory = $directory;
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
     * @param string $ref
     */
    private function checkout(string $ref)
    {
        $gitCheckout = new Process('cd ' . $this->directory . ' && git checkout ' . $ref);
        $gitCheckout->run();
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