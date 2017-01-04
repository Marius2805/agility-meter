<?php
use Marius2805\AgilityMeter\Src\Commands\AnalyseCommand;
use Symfony\Component\Console\Application;

include_once (__DIR__ . '/vendor/autoload.php');

$application = new Application('OrmCleaner');
$application->add(new AnalyseCommand());
$application->run();