<?php

namespace MaxieSystems\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class for generating code coverage report.
 *
 * @author Max Antipin <max.v.antipin@gmail.com>
 */
class ComposerCodeCoverageCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition([
            new InputOption(
                'minimum-coverage',
                null,
                InputOption::VALUE_OPTIONAL,
                'The minimum allowed coverage percentage as an integer.',
                75
            ),
            new InputOption(
                'ignore-threshold',
                null,
                InputOption::VALUE_NONE,
                'Do not fail the action when the minimum coverage was not met.'
            ),
            new InputArgument(
                'path',
                InputArgument::REQUIRED,
                'The path to the report file.'
            ),
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $min_coverage = (int)$input->getOption('minimum-coverage');
        if ($min_coverage < 50 || $min_coverage > 100) {
            throw new InvalidOptionException('Minimum coverage must be between 50 and 100');
        }
        $below_threshold = false;
        $output->writeln('Code Coverage Report Summary:');
        $text = file_get_contents($input->getArgument('path'));
        $rx = '\\s+(?P<rate>[0-9]{1,3}(\\.[0-9]+)?)% \((?P<covered>[0-9]+)\/(?P<valid>[0-9]+)\)';
        foreach (['Classes', 'Methods', 'Paths', 'Branches', 'Lines'] as $label) {
            if (preg_match("/$label:$rx/", $text, $m)) {
                $output->writeln($this->alignLabelsAndNumbers($label, $m['rate'], $m['covered'], $m['valid']));
                if ($m['rate'] < $min_coverage) {
                    $below_threshold = true;
                }
            }
        }
        $output->writeln(" Minimum allowed coverage is $min_coverage%");
        if ($input->getOption('ignore-threshold')) {
            return self::SUCCESS;
        }
        return $below_threshold ? self::FAILURE : self::SUCCESS;
    }

    private function alignLabelsAndNumbers(string $label, string $percent, int $covered, int $valid): string
    {
        $width = 14;
        $spaces = $width - strlen($label) - strlen($percent);
        if ($spaces < 1) {
            $spaces = 1;
        }
        return self::TAB . $label . ':' . str_repeat(' ', $spaces) . $percent . "% ($covered/$valid)";
    }

    private const TAB = '  ';
}
