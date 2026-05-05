#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

RUN_MATRIX=false
if [[ "${1:-}" == "--matrix" ]]; then
  RUN_MATRIX=true
fi

echo "==> [1/4] composer install"
composer install --no-interaction --prefer-dist

echo "==> [2/4] pint"
./vendor/bin/pint --test

echo "==> [3/4] phpstan"
./vendor/bin/phpstan analyse --error-format=table

echo "==> [4/4] tests"
./vendor/bin/pest --ci

if [[ "$RUN_MATRIX" == true ]]; then
  echo "==> Running matrix spot-check: Laravel 12 + Testbench 10"
  composer require "laravel/framework:12.*" "orchestra/testbench:^10.0" --no-interaction --no-update
  composer update --prefer-dist --no-interaction
  ./vendor/bin/pest --ci

  echo "==> Running matrix spot-check: Laravel 13 + Testbench 11"
  composer require "laravel/framework:13.*" "orchestra/testbench:^11.0" --no-interaction --no-update
  composer update --prefer-dist --no-interaction
  ./vendor/bin/pest --ci
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Release gate failed: working tree is not clean after checks."
  echo "Commit or discard changes before creating a release tag."
  exit 1
fi

echo "Release gate passed: lint, static analysis, tests, and clean working tree."
