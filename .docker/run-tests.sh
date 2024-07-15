#!/bin/sh
set -u

echo 'Run phpcs'
php ./vendor/bin/phpcs

echo 'Run phpunit'
if [ "$1" = 'no-coverage' ]; then
  php ./vendor/bin/phpunit --no-coverage
elif [ "$1" = 'coverage' ]; then
  php ./vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text
else
  echo "Invalid argument: $1"
  exit 1
fi

echo 'Run phpstan'
php ./vendor/bin/phpstan analyse