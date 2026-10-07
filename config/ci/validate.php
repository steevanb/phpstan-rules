<?php

declare(strict_types=1);

use Steevanb\ParallelProcess\{
    Console\Application\ParallelProcessesApplication,
    Console\Output\ConsoleBufferedOutput,
    Process\Process
};
use Symfony\Component\Console\Input\ArgvInput;

$projectPath = dirname(__DIR__, 2);

/** @var string $composerHome */
$composerHome = $_SERVER['COMPOSER_HOME'];
require $composerHome . '/vendor/autoload.php';

new ParallelProcessesApplication()
    ->addProcess(
        new Process(['python', 'bin/ci/phpunit.py'], $projectPath)
            ->setName('phpunit')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/phpstan.py'], $projectPath)
            ->setName('phpstan')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/phpcs.py'], $projectPath)
            ->setName('phpcs')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/composer-require-checker.py'], $projectPath)
            ->setName('composer-require-checker')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/composer-normalize.py'], $projectPath)
            ->setName('composer-normalize')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/composer-validate.py'], $projectPath)
            ->setName('composer-validate')
    )
    ->addProcess(
        new Process(['python', 'bin/ci/phpdd.py'], $projectPath)
            ->setName('phpdd')
    )
    ->setMaximumParallelProcesses((int) new Process(['nproc'])->mustRun()->getOutput())
    ->setRefreshInterval(100000)
    ->run(new ArgvInput($argv ?? []), new ConsoleBufferedOutput(ConsoleBufferedOutput::VERBOSITY_NORMAL, true));
