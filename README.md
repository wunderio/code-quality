# Code Quality

[![CircleCI](https://circleci.com/gh/wunderio/code-quality.svg?style=svg)](https://circleci.com/gh/wunderio/code-quality)

This composer package will provide some basic code quality checks before commiting code by using
https://github.com/phpro/grumphp.

It checks only modified files or new files.

## Checks performed

This repository currently has following checks:

* PHP Drupal Coding Standards
* PHP 7.3 Compatibility
* PHP syntax
* Shell script exec bits
* PHP Code security
* Cognitive complexity
* Yaml syntax
* Json syntax

## Pre-requisites

* Composer

## Installation

This needs to be done only once either while creating a project or enabling code checks in existing project.

```
composer require wunderio/code-quality --dev
cp vendor/wunderio/code-quality/config/grumphp.yml ./grumphp.yml
```

The commit hook for GrumPHP is automatically installed on composer require.

## Custom PHP CodeSniffer rules

If you need to customize the rules for PHP CodeSniffer then drop in phpcs.xml in the same
folder as composer.json and configure grumphp.yml:
````yml
parameters:
  tasks:
    phpcs:
      standard:
        - phpcs.xml
````

## Usage

The pre-commit hook will be automatically run upon executing `git commit`.

The code scanning can be avoided by `git commit --no-verify`.

You can run the checks manually with: `./bin/grumphp run`

## Usage in Continuous Integration
You can easily use the code quality checkers on your CI (Jenkins/GitLab CI) by adding this line:

```
./bin/grumphp run --no-ansi --no-interaction
```
