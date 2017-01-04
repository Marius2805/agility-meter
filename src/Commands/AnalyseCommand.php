<?php
namespace Marius2805\AgilityMeter\Src\Commands;

use Marius2805\AgilityMeter\Src\Agility\StatisticService;
use Marius2805\AgilityMeter\Src\Agility\TimeFrameInterval;
use Marius2805\AgilityMeter\Src\Export\JsonExportService;
use Marius2805\AgilityMeter\Src\SourceCode\LinesOfCodeService;
use Marius2805\AgilityMeter\Src\SourceCode\TestStatisticsService;
use Marius2805\AgilityMeter\Src\VersionControl\CommitDifferenceRepository;
use Marius2805\AgilityMeter\Src\VersionControl\CommitRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnalyseCommand
 *
 * @package Marius2805\AgilityMeter\Src\Commands
 */
class AnalyseCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('analyse');
        $this->setDescription('Collects agility statistics.');

        $this->addOption('projectDirectory', 'd', InputArgument::OPTIONAL, 'Code project directory');
        $this->addOption('baseCommit', 'b', InputArgument::OPTIONAL, 'Base commit to start analysing');
        $this->addOption('exportDirectory', 'e', InputArgument::OPTIONAL, 'Base commit to start analysing');
        $this->addOption('fileExtension', 'f', InputArgument::OPTIONAL, 'Source code file extension', 'php');
        $this->addOption('timeFrameInterval', 't', InputArgument::OPTIONAL, 'Time frame interval of statistics', TimeFrameInterval::CALENDAR_MONTH);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commitRepository = new CommitRepository($input->getOption('projectDirectory'));
        $commitDifferenceRepository = new CommitDifferenceRepository($input->getOption('projectDirectory'), $input->getOption('fileExtension'));
        $linesOfCodeService = new LinesOfCodeService($input->getOption('fileExtension'), $input->getOption('projectDirectory'));
        $testStatisticsService = new TestStatisticsService($input->getOption('projectDirectory'));
        $statisticService = new StatisticService($commitRepository, $commitDifferenceRepository, $linesOfCodeService, $testStatisticsService);

        $baseCommit = $commitRepository->get($input->getOption('baseCommit'));
        $summaryStatistic = $statisticService->getStatistic($baseCommit, $input->getOption('timeFrameInterval'));

        $exportService = new JsonExportService($input->getOption('exportDirectory'));
        $exportService->export($summaryStatistic);
    }
}