<?php
namespace Marius2805\AgilityMeter\Src\VersionControl;

use Symfony\Component\Process\Process;

/**
 * Class GitInteraction
 * @package Marius2805\AgilityMeter\Src\VersionControl
 */
abstract class GitInteraction
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * GitInteraction constructor.
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param string $ref
     */
    protected function checkout(string $ref)
    {
        $gitCheckout = new Process('cd ' . $this->directory . ' && git checkout ' . $ref);
        $gitCheckout->run();
    }
}