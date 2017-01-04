<?php
namespace Marius2805\AgilityMeter\Tests\VersionControl;

use Marius2805\AgilityMeter\Src\VersionControl\Commit;
use Marius2805\AgilityMeter\Src\VersionControl\CommitRepository;
use Marius2805\AgilityMeter\Tests\General\RepositoryIntegrationTestCase;

/**
 * Class CommitRepositoryIntegrationTest
 * @package Marius2805\AgilityMeter\Tests\VersionControl
 */
class CommitRepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    const FIXTURE_REPOSITORY = 'git01';

    /**
     * @var CommitRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->prepareFixtures(self::FIXTURE_REPOSITORY);
        $this->repository = new CommitRepository($this->testFolder . '/' . self::FIXTURE_REPOSITORY);
    }

    public function test_getSince_hashesCorrect()
    {
        $commits = $this->repository->getSince('f846b6b6e659a869fc4db84cac0b8610d5e73866');

        self::assertCount(2, $commits);
        self::assertInstanceOf(Commit::class, $commits[0]);
        self::assertEquals('f846b6b6e659a869fc4db84cac0b8610d5e73866', $commits[0]->getHash());
        self::assertEquals('eef2eb8357378a217495cf1e781f412feaf03b0f', $commits[1]->getHash());
    }

    public function test_getSince_datesCorrect()
    {
        $commits = $this->repository->getSince('f846b6b6e659a869fc4db84cac0b8610d5e73866');

        self::assertCount(2, $commits);
        self::assertInstanceOf(Commit::class, $commits[0]);
        self::assertEquals('2016-12-28 22:43:19', $commits[0]->getDate()->format('Y-m-d H:i:s'));
        self::assertEquals('2016-12-28 22:43:56', $commits[1]->getDate()->format('Y-m-d H:i:s'));
    }

    public function test_get_hashCorrect()
    {
        $commit = $this->repository->get('f846b6b6e659a869fc4db84cac0b8610d5e73866');

        self::assertInstanceOf(Commit::class, $commit);
        self::assertEquals('f846b6b6e659a869fc4db84cac0b8610d5e73866', $commit->getHash());
    }

    public function test_get_dateCorrect()
    {
        $commit = $this->repository->get('f846b6b6e659a869fc4db84cac0b8610d5e73866');

        self::assertInstanceOf(Commit::class, $commit);
        self::assertEquals('2016-12-28 22:43:19', $commit->getDate()->format('Y-m-d H:i:s'));
    }
}