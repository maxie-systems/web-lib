<?php
function runCmd(string $cmd, callable $callback = null, ...$args): array {
    $output = $result = null;
    exec($cmd, $output, $result);
    if (0 === $result) {
        if (null !== $callback) {
            foreach ($output as $str) {
                $callback($str, ...$args);
            }
        }
        return $output;
    }
    exit($result);
}
function fnmatches(array $patterns, string $file_name): bool {
    foreach ($patterns as $pattern) {
        if (fnmatch($pattern, $file_name)) {
            return true;
        }
    }
    return false;
}
function toStdOut(string $value, string ...$values): void {
    foreach ([$value, ...$values] as $text) {
        echo $text, PHP_EOL;
    }
}
function toStdErr(string $value, string ...$values): void {
    foreach ([$value, ...$values] as $text) {
        fwrite(STDERR, $text . PHP_EOL);
    }
}
$patternsToIgnore = ['.*', 'Tests/*', '*/Tests/*', '*.dist', '*.dist.*'];
$filesToIgnore = [];
$exportedFiles = runCmd(
    'git archive --format=tar --worktree-attributes HEAD | tar -t',
    static function (string $file_name, array $patterns) use (&$filesToIgnore): void {
        if (fnmatches($patterns, $file_name)) {
            $filesToIgnore[] = $file_name;
        }
    },
    $patternsToIgnore
);
$ignoredFiles = array_diff(runCmd('git ls-files'), $exportedFiles);
if ($ignoredFiles) {
    toStdOut('', 'Files ignored:');
    toStdOut(...$ignoredFiles);
}
if ($filesToIgnore) {
    toStdOut('', 'Files to ignore:');
    toStdOut(...$filesToIgnore);
    exit(1);
}
