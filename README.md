# Code Quality

[![CircleCI](https://circleci.com/gh/wunderio/code-quality.svg?style=svg)](https://circleci.com/gh/wunderio/code-quality)

This composer package will provide some basic code quality checks before committing
code by using https://github.com/phpro/grumphp.

**It checks only modified files or new files on git commit, but check on all configured
paths can be executed running `vendor/bin/grumphp run`**

This tool only extends [GrumPHP](https://github.com/phpro/grumphp) please read 
its [documentation](https://github.com/phpro/grumphp/blob/master/README.md#configuration) on how to configure tool itself.

## Checks performed

This repository currently has following checks:

* Shell script exec bits - [check_file_permissions](src/Task/CheckFilePermissions/README.md)
* PHP Drupal CS and PHP Code security  - [phpcs](src/Task/Phpcs/README.md)
* PHP 7.3 Compatibility - [php_compatibility](src/Task/PhpCompatibility/README.md)
* PHP syntax - [php_check_syntax](src/Task/PhpCheckSyntax/README.md)
* Cognitive complexity and other ecs sniffs - [ecs](src/Task/Ecs/README.md)
* Yaml syntax - [yaml_lint](src/Task/YamlLint/README.md)
* Json syntax - [json_lint](src/Task/JsonLint/README.md)

## Pre-requisites

* Composer
* PHP >= 7.1

## Installation

This needs to be done only once either while creating a project or enabling code checks in existing project.

```
composer require wunderio/code-quality --dev
cp vendor/wunderio/code-quality/config/grumphp.yml ./grumphp.yml
```

The commit hook for GrumPHP is automatically installed on composer require.

## Customization

### Configuration

Details of the configuration are broken down into the following sections.

- [Parameters](https://github.com/phpro/grumphp/blob/master/doc/parameters.md) &ndash; Configuration settings for GrumPHP itself.
- [Tasks](https://github.com/phpro/grumphp/blob/master/doc/tasks.md) &ndash; External tasks performing code validation and their respective configurations.
- [TestSuites](https://github.com/phpro/grumphp/blob/master/doc/testsuites.md)
- [Extensions](https://github.com/phpro/grumphp/blob/master/doc/extensions.md)
- [Events](https://github.com/phpro/grumphp/blob/master/doc/events.md)
- [Conventions checker](https://github.com/phpro/grumphp/blob/master/doc/conventions.md)

### Task parameters
If you need to customize the rules for PHP CodeSniffer then drop in phpcs.xml in the same
folder as composer.json and configure grumphp.yml:
````yml
parameters:
  tasks:
    phpcs:
      standard:
        - phpcs.xml
````

Same applies to any task that uses other configuration file (easy-coding-standards).

**Each code quality tool allows you to define at least 3 things:**
- `run_on` - Multiple paths that will be checked and files staged must be from same path
- `ignore_patterns` - path parts that will exclude files from check
- `extensions` - file extensions of files that should be checked

Please see individual task documentation for more information on what are other configurable options.



## Commands

Since GrumPHP is just a CLI tool, these commands can be triggered:

- [configure](https://github.com/phpro/grumphp/blob/master/doc/commands.md#installation)
- [git:init](https://github.com/phpro/grumphp/blob/master/doc/commands.md#installation)
- [git:deinit](https://github.com/phpro/grumphp/blob/master/doc/commands.md#installation)
- [git:pre-commit](https://github.com/phpro/grumphp/blob/master/doc/commands.md#git-hooks)
- [git:commit-msg](https://github.com/phpro/grumphp/blob/master/doc/commands.md#git-hooks)
- [run](https://github.com/phpro/grumphp/blob/master/doc/commands.md#run)

## Usage

The pre-commit hook will be automatically run upon executing `git commit`.

The code scanning can be avoided by `git commit --no-verify` or `git commit -n`.

You can run the checks manually with: `./vendor/bin/grumphp run`

## Usage in Continuous Integration
You can easily use the code quality checkers on your CI (CircleCi/Jenkins/GitLab CI) by adding this line:

```
./vendor/bin/grumphp run --no-ansi --no-interaction
```
