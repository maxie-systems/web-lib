<?php

namespace MaxieSystems\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class for `composer test <FILE-OR-DIR>` commands.
 *
 * @author Max Antipin <max.v.antipin@gmail.com>
 */
class ComposerTestCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition([
            new InputOption('unit', 'u', InputOption::VALUE_NONE, 'Run unit tests only'),
            new InputOption(
                'fix-psr12',
                null,
                InputOption::VALUE_NONE,
                'Automatically correct coding standard violations with phpcbf before unit testing'
            ),
            new InputArgument(
                'paths',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Files and|or directories to test'
            ),
        ]);
    }

    protected function segmentStartsWith(string $path, string $s): bool
    {
        $len = strlen($s);
        foreach (explode('/', $path) as $segment) {
            if (0 === strncasecmp($segment, $s, $len)) {
                return true;
            }
        }
        return false;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = $this->getPaths($input->getArgument('paths'));
        $has_error = false;
        foreach ($this->getScripts($input, $output) as $i => $args) {
            if (0 === $i) {
                foreach ($paths[self::UNIT_TEST_DIR] as $test_path) {
                    $a = $args;
                    $a[] = $test_path;
                    if ($this->runProcess($output, ...$a)) {
                        $has_error = true;
                    }
                }
            } elseif ($this->runProcess($output, ...$args, ...array_merge(...array_values($paths)))) {
                $has_error = true;
            }
        }
        return $has_error ? self::FAILURE : self::SUCCESS;
    }

    private function runProcess(OutputInterface $output, string $script, ...$args): int
    {
        $process = new Process([$script, ...$args]);
        $output->write('> ');
        $output->writeln($process->getCommandLine());
        $process->run(function ($type, $buffer) use ($output): void {
            if (Process::ERR === $type) {
                fwrite(STDERR, $buffer);
            } else {
                $output->write($buffer);
            }
        });
        if ($exit_code = $process->getExitCode()) {
            $output->writeln(
                $this->formatErrMsg(
                    "Script $script handling the {$this->getName()} event returned with error code $exit_code"
                )
            );
        }
        return $exit_code;
    }

    private function getScripts(InputInterface $input, OutputInterface $output): array
    {
        $scripts = [
            0 => [self::BIN_DIR . 'phpunit', '--no-coverage', ]
        ];
        if ($input->getOption('unit')) {
            $output->writeln('Run unit tests only.');
        } else {
            if ($input->getOption('fix-psr12')) {
                # phpcbf runs BEFORE unit tests
                $scripts = [1 => [self::BIN_DIR . 'phpcbf']] + $scripts;
            } else {
                $scripts[1] = [self::BIN_DIR . 'phpcs'];
            }
            $scripts[1][] = '--standard=PSR12';
        }
        return $scripts;
    }

    private function getPaths(array $args): array
    {
        $pkeys = [self::SRC_DIR, self::UNIT_TEST_DIR];
        $paths = array_fill_keys($pkeys, []);
        $add = function (array &$p, string $s): void {
            foreach ($p as &$v) {
                $v .= $s;
            }
        };
        foreach ($args as $path) {
            $p = array_combine($pkeys, $pkeys);
            if ('/' !== $path[0]) {
                $add($p, '/');
            }
            $add($p, $path);
            if (!str_ends_with($path, '/')) {
                if (str_ends_with($path, '.php')) {
                    $p[self::UNIT_TEST_DIR] = substr_replace($p[self::UNIT_TEST_DIR], 'Test', -4, 0);
                } else {
                    $p[self::UNIT_TEST_DIR] .= 'Test';
                    $add($p, '.php');
                }
            }
            array_walk($paths, fn (array &$paths, string $key, array $p) => $paths[] = $p[$key], $p);
        }
        return $paths;
    }

    private function formatErrMsg(string $msg): string
    {
        return "<error>$msg</error>";
    }

    private const BIN_DIR = './vendor/bin/';
    private const SRC_DIR = 'src';
    private const UNIT_TEST_DIR = 'tests/unit';
}
