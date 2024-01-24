<?php

namespace MaxieSystems\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
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
            new InputOption(
                'coverage-text',
                null,
                InputOption::VALUE_OPTIONAL,
                'Code coverage report file.'
            ),
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $min_coverage = (int)$input->getOption('minimum-coverage');
        if ($min_coverage < 50 || $min_coverage > 100) {
            throw new InvalidOptionException('Minimum coverage must be between 50 and 100');
        }
        if ($file = $input->getOption('coverage-text')) {
            $output->writeln('Got report file name from CLI');
        } else {
            $doc = new \DOMDocument();
            $doc->load('./phpunit.xml');
            $xpath = new \DOMXPath($doc);
            $items = $xpath->query('coverage/report/text');
            if (!$items->length) {
                throw new RuntimeException('Unable to find report file name');
            }
            $output->writeln('Got report file name from phpunit.xml');
            $file = $items[0]->getAttribute('outputFile');
        }
        $text = file_get_contents($file);
        $output->writeln('Code Coverage Report Summary:');
        $output->writeln(" Minimum allowed coverage is $min_coverage%");
        $rx = '\\s+(?P<rate>[0-9]{1,3}(\\.[0-9]+)?)% \((?P<covered>[0-9]+)\/(?P<valid>[0-9]+)\)';
        $below_threshold = false;
        foreach (['Classes', 'Methods', 'Paths', 'Branches', 'Lines'] as $label) {
            if (preg_match("/$label:$rx/", $text, $m)) {
                $s = self::TAB . $this->alignLabelsAndNumbers($label, $m['rate'], $m['covered'], $m['valid']);
                if ($m['rate'] < $min_coverage) {
                    $below_threshold = true;
                    $s = "<error>$s </error>";
                }
                $output->writeln($s);
            }
        }
        $output->writeln('');
        return $input->getOption('ignore-threshold') || !$below_threshold ? self::SUCCESS : self::FAILURE;
    }

    private function alignLabelsAndNumbers(string $label, string $percent, int $covered, int $valid): string
    {
        $width = 14;
        $spaces = $width - strlen($label) - strlen($percent);
        if ($spaces < 1) {
            $spaces = 1;
        }
        return $label . ':' . str_repeat(' ', $spaces) . $percent . "% ($covered/$valid)";
    }

    private const TAB = '  ';
}
