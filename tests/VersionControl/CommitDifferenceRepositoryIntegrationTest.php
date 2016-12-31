<?php
namespace Marius2805\AgilityMeter\Tests\VersionControl;

use Marius2805\AgilityMeter\Src\VersionControl\CommitDifference;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifferenceRepository;
use Marius2805\AgilityMeter\Src\VersionControl\CommitRepository;
use Marius2805\AgilityMeter\Src\VersionControl\FileChange;
use Marius2805\AgilityMeter\Tests\General\RepositoryIntegrationTestCase;

/**
 * Class CodeDifferenceRepositoryIntegrationTest
 * @package Marius2805\AgilityMeter\Tests\VersionControl
 */
class CommitDifferenceRepositoryIntegrationTest extends RepositoryIntegrationTestCase
{
    const FIXTURE_REPOSITORY = 'git02';
    const BASE_COMMIT = '2576d2dfabd9e67fb4b535bb86097abfd49ffaf6';

    /**
     * @var CommitRepository
     */
    private $commitRepository;

    /**
     * @var CommitDifferenceRepository
     */
    private $differenceRepository;

    public function setUp()
    {
        parent::setUp();

        $this->prepareFixtures(self::FIXTURE_REPOSITORY);
        $this->commitRepository = new CommitRepository($this->testFolder . '/' . self::FIXTURE_REPOSITORY);
        $this->differenceRepository = new CommitDifferenceRepository($this->testFolder . '/' . self::FIXTURE_REPOSITORY);
    }

    public function test_getDifference_fileAdded()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[0];
        $current = $commits[1];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertInstanceOf(CommitDifference::class, $difference);
        self::assertSame($previous, $difference->getPreviousCommit());
        self::assertSame($current, $difference->getCurrentCommit());
        self::assertCount(1, $difference->getFileChanges());
        self::assertInstanceOf(FileChange::class, $difference->getFileChanges()[0]);
        self::assertEquals('file01.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals(2, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(0, $difference->getFileChanges()[0]->getRemovedRows());
    }

    public function test_getDifference_linesAdded()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[1];
        $current = $commits[2];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(1, $difference->getFileChanges());
        self::assertEquals('file01.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals(3, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(0, $difference->getFileChanges()[0]->getRemovedRows());
    }

    public function test_getDifference_linesRemoved()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[2];
        $current = $commits[3];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(1, $difference->getFileChanges());
        self::assertEquals('file01.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals(0, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(1, $difference->getFileChanges()[0]->getRemovedRows());
    }

    public function test_getDifference_linesChanged()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[3];
        $current = $commits[4];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(1, $difference->getFileChanges());
        self::assertEquals('file01.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals(1, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(1, $difference->getFileChanges()[0]->getRemovedRows());
    }

    public function test_getDifference_fileRemoved()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[4];
        $current = $commits[5];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(1, $difference->getFileChanges());
        self::assertEquals('file01.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals(0, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(4, $difference->getFileChanges()[0]->getRemovedRows());
    }

    public function test_getDifference_fileRenamed()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[6];
        $current = $commits[7];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(2, $difference->getFileChanges());
        self::assertEquals('file02.txt', $difference->getFileChanges()[0]->getFileName());
        self::assertEquals('file03.txt', $difference->getFileChanges()[1]->getFileName());
        self::assertEquals(0, $difference->getFileChanges()[0]->getAddedRows());
        self::assertEquals(4, $difference->getFileChanges()[0]->getRemovedRows());
        self::assertEquals(4, $difference->getFileChanges()[1]->getAddedRows());
        self::assertEquals(0, $difference->getFileChanges()[1]->getRemovedRows());
    }

    public function test_getDifference_binaryFileChange()
    {
        $commits = $this->commitRepository->getSince(self::BASE_COMMIT);
        $previous = $commits[7];
        $current = $commits[8];

        $difference = $this->differenceRepository->getDifference($previous, $current);
        self::assertCount(0, $difference->getFileChanges());
    }
}