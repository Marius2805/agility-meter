<?php
namespace Marius2805\AgilityMeter\Tests\SourceCode;

use Carbon\Carbon;
use Marius2805\AgilityMeter\Src\SourceCode\TestStatisticsService;
use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Tests\General\RepositoryIntegrationTestCase;

/**
 * Class TestStatisticsServiceIntegrationTest
 * @package Marius2805\AgilityMeter\Tests\SourceCode
 */
class TestStatisticsServiceIntegrationTest extends RepositoryIntegrationTestCase
{
    const FIXTURE_REPOSITORY = 'git04';
    /**
     * @var TestStatisticsService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();

        $this->prepareFixtures(self::FIXTURE_REPOSITORY);
        $this->service = new TestStatisticsService($this->testFolder . '/' . self::FIXTURE_REPOSITORY);
    }

    public function test_getNumberOfTests_normalTestDirInRoot()
    {
        $commit = new Commit('30ec4302fe3a1ff21d7b3c162775512d83dfb6f6', new Carbon());
        $numberOfTests = $this->service->getNumberOfTests($commit);

        self::assertEquals(5, $numberOfTests);
    }

    public function test_getNumberOfTests_mixedCapitalLettersInTestDirectoryName()
    {
        $commit = new Commit('bc0596092a68379186b183165715cde31bcd1626', new Carbon());
        $numberOfTests = $this->service->getNumberOfTests($commit);

        self::assertEquals(5, $numberOfTests);
    }

    public function test_getNumberOfTests_singularNamedTestDirectory()
    {
        $commit = new Commit('7b49b521be26f0d12f06ed3e3e5175665b5b5b32', new Carbon());
        $numberOfTests = $this->service->getNumberOfTests($commit);

        self::assertEquals(5, $numberOfTests);
    }

    public function test_getNumberOfTests_testFolderInSubdirectory()
    {
        $commit = new Commit('f8a3eb7d7ac04698f7e73c3edfa11b6340c0579f', new Carbon());
        $numberOfTests = $this->service->getNumberOfTests($commit);

        self::assertEquals(5, $numberOfTests);
    }

    public function test_getNumberOfTests_multipleTestFolderInSubdirectory()
    {
        $commit = new Commit('7508777ab391f6b35494383f2cce09a07ea06d53', new Carbon());
        $numberOfTests = $this->service->getNumberOfTests($commit);

        self::assertEquals(15, $numberOfTests);
    }
}