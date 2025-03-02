#!/bin/sh
set -eux

if [ -z "${1:-}" ]; then
  branch=$(git branch --show-current)
#  echo 'Select current branch'
#  exit 5
else
  branch=$1
fi

#echo $branch

#mkdir -v /tmp/release 2> /dev/null || rm -rfv /tmp/release/*
#git archive --format=tar --worktree-attributes dev | tar -x -C /tmp/release

# dry-run
git archive --format=tar --worktree-attributes $branch | tar -t

# check-names
#git archive --format=tar --worktree-attributes $branch | tar -t | something
# сюда можно не скрипт пытаться передать, а набор паттернов.