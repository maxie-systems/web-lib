<?php

namespace MaxieSystems\Dev;

use Symfony\Component\Console\Command\Command;
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
            new InputOption('fix-psr12', null, InputOption::VALUE_NONE, 'Use phpcbf instead of phpcs'),
            //new InputOption('bar', 'b', InputOption::VALUE_REQUIRED),
            //new InputOption('cat', 'c', InputOption::VALUE_OPTIONAL),
            new InputArgument('paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Files and directories to test'),
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $test_paths = $src_paths = [];
        foreach ($input->getArgument('paths') as $path) {
            $src_path = 'src';
            $test_path = 'tests';
            if ('/' !== $path[0]) {
                $src_path .= '/';
                $test_path .= '/';
            }
            $src_path .= $path;
            $test_path .= $path;
            if (!str_ends_with($path, '/')) {
                $src_path .= '.php';
                $test_path .= 'Test.php';
            }
            $src_paths[] = $src_path;
            $test_paths[] = $test_path;
        }
        $scripts = [
            ['./vendor/bin/phpunit', ],
            ['./vendor/bin/phpcs', '--standard=PSR12', ...$test_paths],
            ['./vendor/bin/phpcs', '--standard=PSR12', ...$src_paths],
        ];
        if ($input->getOption('fix-psr12')) {
            $scripts[1][0] = './vendor/bin/phpcbf';
            $scripts[2][0] = './vendor/bin/phpcbf';
        }
        $has_error = false;
        foreach ($scripts as $i => $args) {
            if (0 === $i) {
                foreach ($test_paths as $test_path) {
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
        return $has_error ? Command::FAILURE : Command::SUCCESS;
    }

    private function runProcess(OutputInterface $output, string $script_name, ...$args): int
    {
        $process = new Process([$script_name, ...$args]);
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
            $output->writeln("<error>Script $script_name handling the {$this->getName()} event returned with error code $exit_code</error>");
        }
        return $exit_code;
    }
}
