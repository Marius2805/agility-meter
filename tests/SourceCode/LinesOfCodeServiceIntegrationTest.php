<?php
namespace Marius2805\AgilityMeter\Tests\SourceCode;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\SourceCode\LinesOfCodeService;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Tests\General\RepositoryIntegrationTestCase;

/**
 * Class LinesOfCodeServiceIntegrationTest
 * @package Marius2805\AgilityMeter\Tests\SourceCode
 */
class LinesOfCodeServiceIntegrationTest extends RepositoryIntegrationTestCase
{
    const FIXTURE_REPOSITORY = 'git03';

    /**
     * @var LinesOfCodeService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->prepareFixtures(self::FIXTURE_REPOSITORY);
        $this->service = new LinesOfCodeService('php', $this->testFolder . '/' . self::FIXTURE_REPOSITORY);
    }

    public function test_getLinesOfCode_onlyFileExtensionIncluded()
    {
        $commit = new Commit('4ae03c5bac8f5ae71cd2a4f2cef1ad7cc6c864fe', new Carbon());
        $linesOfCode = $this->service->getLinesOfCode($commit);

        self::assertEquals(5, $linesOfCode);
    }

    public function test_getLinesOfCode_subDirectoriesIncluded()
    {
        $commit = new Commit('2f5c741337c70e7e44eb4e481e1f18fd1b0cc677', new Carbon());
        $linesOfCode = $this->service->getLinesOfCode($commit);

        self::assertEquals(20, $linesOfCode);
    }
}