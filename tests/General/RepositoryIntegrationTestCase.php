<?php
namespace Marius2805\AgilityMeter\Tests\General;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RepositoryIntegrationTestCase
 * @package Marius2805\AgilityMeter\Tests\General
 */
abstract class RepositoryIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testFolder;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    protected function setUp()
    {
        $this->fileSystem = new Filesystem();
        $this->testFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Test-' . uniqid();
        $this->fileSystem->mkdir($this->testFolder);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->testFolder);
    }

    /**
     * @param string $name
     */
    protected function prepareFixtures(string $name)
    {
        $archive = new \ZipArchive();
        $archive->open(__DIR__ . '/../Fixtures/' . $name . '.zip');
        $archive->extractTo($this->testFolder);
        $archive->close();
    }
}