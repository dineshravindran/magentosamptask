<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function codecept_data_dir;
use function codecept_output_dir;
use function codecept_root_dir;

/**
 * Validates and prints Codeception config.
 * Use it do debug Yaml configs
 *
 * Check config:
 *
 * * `codecept config`: check global config
 * * `codecept config unit`: check suite config
 *
 * Load config:
 *
 * * `codecept config:validate -c path/to/another/config`: from another dir
 * * `codecept config:validate -c another_config.yml`: from another config file
 *
 * Check overriding config values (like in `run` command)
 *
 * * `codecept config:validate -o "settings: shuffle: true"`: enable shuffle
 * * `codecept config:validate -o "settings: lint: false"`: disable linting
 * * `codecept config:validate -o "reporters: report: \Custom\Reporter" --report`: use custom reporter
 *
 */
#[AsCommand(
    name: 'config:validate',
    description: 'Validates and prints Codeception config'
)]
class ConfigValidate extends Command
{
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::OPTIONAL, 'To show suite configuration')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config')
            ->addOption('override', 'o', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Override config values');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);

        if ($suite = $input->getArgument('suite')) {
            $output->write("Validating <bold>{$suite}</bold> config... ");
            $config = $this->getSuiteConfig($suite);
            $output->writeln("Ok");
            $output->writeln("------------------------------\n");
            $output->writeln("<info>{$suite} Suite Config</info>:\n");
            $output->writeln($this->formatOutput($config));
            return Command::SUCCESS;
        }

        $output->write("Validating global config... ");
        $config = $this->getGlobalConfig();
        $output->writeln($input->getOption('override'));
        if (!empty($input->getOption('override'))) {
            $config = $this->overrideConfig($input->getOption('override'));
        }

        $output->writeln("Ok");
        $suites = Configuration::suites();

        $output->writeln("------------------------------\n");
        $output->writeln("<info>Codeception Config</info>:\n");
        $output->writeln($this->formatOutput($config));

        $output->writeln('<info>Directories</info>:');
        $output->writeln("<comment>codecept_root_dir()</comment>   " . codecept_root_dir());
        $output->writeln("<comment>codecept_output_dir()</comment> " . codecept_output_dir());
        $output->writeln("<comment>codecept_data_dir()</comment>   " . codecept_data_dir());
        $output->writeln('');

        $output->writeln("<info>Available suites</info>: " . implode(', ', $suites));

        foreach ($suites as $suite) {
            $output->write("Validating suite <bold>{$suite}</bold>... ");
            $this->getSuiteConfig($suite);
            $output->writeln('Ok');
        }

        $output->writeln("Execute <info>codecept config:validate [<suite>]</info> to see config for a suite");

        return Command::SUCCESS;
    }

    protected function formatOutput($config): ?string
    {
        $output = print_r($config, true);
        return preg_replace('#\[(.*?)] =>#', "<fg=yellow>$1</fg=yellow> =>", $output);
    }
}
