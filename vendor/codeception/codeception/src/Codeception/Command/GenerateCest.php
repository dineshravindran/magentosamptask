<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Lib\Generator\Cest as CestGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function file_exists;

/**
 * Generates Cest (scenario-driven object-oriented test) file:
 *
 * * `codecept generate:cest suite Login`
 * * `codecept g:cest suite subdir/subdir/testnameCest.php`
 * * `codecept g:cest suite LoginCest -c path/to/project`
 * * `codecept g:cest "App\Login"`
 *
 */
#[AsCommand(
    name: 'generate:cest',
    description: 'Generates empty Cest file in suite'
)]
class GenerateCest extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put')
            ->addArgument('class', InputArgument::REQUIRED, 'test name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite);
        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($config['path'], $class);

        $filename = $this->completeSuffix($className, 'Cest');
        $filename = $path . $filename;

        if (file_exists($filename)) {
            $output->writeln("<error>Test {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $cest = new CestGenerator($class, $config);
        $res = $this->createFile($filename, $cest->produce());
        if (!$res) {
            $output->writeln("<error>Test {$filename} already exists</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Test was created in {$filename}</info>");
        return Command::SUCCESS;
    }
}
