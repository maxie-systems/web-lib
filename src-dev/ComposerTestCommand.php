<?php

namespace MaxieSystems\Dev;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ComposerTestCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition([
            new InputOption('unit', 'u', InputOption::VALUE_NONE, 'Run unit tests only'),
            new InputOption('fix-psr12', null, InputOption::VALUE_NONE, 'Use phpcbf instead of phpcs'),
            new InputArgument(
                'paths',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Files and directories to test',
                null,
                function (CompletionInput $input): array {
                    $project_root = dirname(__DIR__) . '/src';
                    // the value the user already typed, e.g. when typing "app:greet Fa" before
                    // pressing Tab, this will contain "Fa"
                    $value = $input->getCompletionValue();
                    $directory = new \RecursiveDirectoryIterator(
                        $project_root,
                        \FilesystemIterator::KEY_AS_FILENAME | \FilesystemIterator::CURRENT_AS_SELF
                        | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS
                    );
                    $filter = new \RecursiveCallbackFilterIterator(
                        $directory,
                        function (\RecursiveDirectoryIterator $current, string $filename) use ($value): bool {
                            if ($filename[0] === '.') {
                                return false;
                            }
                            return $this->segmentStartsWith($current->getSubPathname(), $value);
                        }
                    );
                    $iterator = new \RecursiveIteratorIterator($filter);
                    $values = [];
                    foreach ($iterator as $current) {
                        /** @var \RecursiveDirectoryIterator $current */
                        $values[] = $current->getSubPathname();
                    }
                    return $values;
                }
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
        $paths = ['src' => [], 'tests' => []];
        $add = function (array &$p, string $s): void {
            foreach ($p as &$v) {
                $v .= $s;
            }
        };
        foreach ($input->getArgument('paths') as $path) {
            $p = array_keys($paths);
            $p = array_combine($p, $p);
            if ('/' !== $path[0]) {
                $add($p, '/');
            }
            $add($p, $path);
            if (!str_ends_with($path, '/')) {
                if (str_ends_with($path, '.php')) {
                    $p['tests'] = substr_replace($p['tests'], 'Test', -4, 0);
                } else {
                    $p['tests'] .= 'Test';
                    $add($p, '.php');
                }
            }
            array_walk($paths, fn (array &$paths, string $key, array $p) => $paths[] = $p[$key], $p);
        }
        $scripts = [];
        $scripts[0] = ['./vendor/bin/phpunit', ];
        if ($input->getOption('unit')) {
            $output->writeln('Run unit tests only.');
        } else {
            $script = './vendor/bin/';
            if ($input->getOption('fix-psr12')) {
                $script .= 'phpcbf';
            } else {
                $script .= 'phpcs';
            }
            foreach ([1 => 'tests', 2 => 'src'] as $i => $key) {
                $scripts[$i] = [$script, '--standard=PSR12', ...$paths[$key]];
            }
        }
        $has_error = false;
        foreach ($scripts as $i => $args) {
            if (0 === $i) {
                foreach ($paths['tests'] as $test_path) {
                    if ($this->runProcess($output, ...array_merge($args, [$test_path]))) {
                        $has_error = true;
                    }
                }
            } else {
                if ($this->runProcess($output, ...$args)) {
                    $has_error = true;
                }
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

    private function formatErrMsg(string $msg): string
    {
        return "<error>$msg</error>";
    }
}
