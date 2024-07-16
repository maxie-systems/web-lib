#!/bin/sh
set -u

if [[ $# -eq 0 ]]; then
  echo "No arguments specified!"
  exit 1
fi

for arg in "$@"
do
  if [[ $arg = 'phpcs' ]]; then
    echo 'Run phpcs'
    php ./vendor/bin/phpcs
  elif [[ $arg = 'phpstan' ]]; then
    echo 'Run phpstan'
    php ./vendor/bin/phpstan analyse
  elif [[ $arg = 'phpunit-no-coverage' ]]; then
    echo 'Run phpunit without coverage'
    php ./vendor/bin/phpunit --no-coverage
  elif [[ $arg = 'phpunit' ]]; then
    echo 'Run phpunit with coverage'
    php ./vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text
  else
    echo "Invalid argument: $arg"
    exit 1
  fi
done
