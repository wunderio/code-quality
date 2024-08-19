# Code Quality

[![CircleCI](https://circleci.com/gh/wunderio/code-quality.svg?style=svg)](https://circleci.com/gh/wunderio/code-quality)

This composer package will provide some basic code quality checks before committing
code by using https://github.com/phpro/grumphp.

**It checks only modified files or new files on git commit, but check on all configured
paths can be executed running `vendor/bin/grumphp run`**

This tool only extends [GrumPHP](https://github.com/phpro/grumphp). Please read
its [documentation](https://github.com/phpro/grumphp/blob/master/README.md#configuration) on how to configure tool itself.

## Checks performed

This repository currently has the following checks:

* Shell script exec bits - [check_file_permissions](src/Task/CheckFilePermissions/README.md)
* PHP Drupal CS and PHP Code security  - [phpcs](src/Task/Phpcs/README.md)
* PHP 8.1 Compatibility - [php_compatibility](src/Task/PhpCompatibility/README.md)
* PHP syntax - [php_check_syntax](src/Task/PhpCheckSyntax/README.md)
* Cognitive complexity and other ecs sniffs - [ecs](src/Task/Ecs/README.md)
* Yaml syntax - [yaml_lint](src/Task/YamlLint/README.md)
* Json syntax - [json_lint](src/Task/JsonLint/README.md)
* Deprecation testing -  [php_stan](src/Task/PhpStan/README.md)

## Pre-requisites

* Composer
* PHP >= 8.1

## Installation

This needs to be done only once either while creating a project or enabling code checks in existing project.

```
composer require wunderio/code-quality --dev
cp vendor/wunderio/code-quality/config/grumphp.yml ./grumphp.yml
cp vendor/wunderio/code-quality/config/phpstan.neon ./phpstan.neon
cp vendor/wunderio/code-quality/config/psalm.xml ./psalm.xml
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

The code scanning can be avoided by `git commit --no-verify` or `git commit -n` but is only meant for rare occasions.

You can run the checks manually with: `./vendor/bin/grumphp run`

To run specific task from the defined tasks in grumphp.yml, you can define it with `--tasks` parameter. Example:

    ./vendor/bin/grumphp run --tasks=phpcs

## Usage in Continuous Integration
You can easily use the code quality checkers on your CI (CircleCi/Jenkins/GitLab CI) by adding this line:

```
./vendor/bin/grumphp run --no-ansi --no-interaction
```

## IDE Integration (optional)

### Prerequisites

Minimally [Qude Quality 2.2.1](https://github.com/wunderio/code-quality/releases/tag/2.2.1) is needed because it contains the WunderAll ruleset that groups all Wunder rulesets.

#### Ubuntu
To install the necessary PHP components without Apache:
```
sudo apt install php-cli php-tokenizer
```

#### macOS
1. Install Homebrew if you don't have it installed already (see instructions on https://brew.sh/)
2. Install PHP with Homebrew
```
brew install php
```

### Configuration

#### Visual Studio Code

1. Install the [Drupal extension](https://marketplace.visualstudio.com/items?itemName=Stanislav.vscode-drupal).
2. Open the Drupal extension configuration.
3. Find "Drupal > Phpcs: Args" and "Drupal > Phpcbf: Args".
4. Click "Edit in settings.json" and add:

```json
{
    "drupal.phpcs.args": [
        "--standard=WunderAll"
    ],
    "drupal.phpcbf.args": [
        "--standard=WunderAll"
    ]
}
```

PHPCS usage Example in Visual Studio Code:
![PHPCS Usage Example in VSC](https://user-images.githubusercontent.com/11972062/221161739-cabcd4b5-800d-4d5b-8071-9324bf2bcc08.gif)

#### PhpStorm

Open settings and look for PHP_Codesniffer. Make sure these settings are the same:
![PhpStorm settings PHPCS 1/2](https://www.upload.ee/image/16969201/2024-08-14_15-59.png)

Check the paths and validate:
![PhpStorm settings PHPCS 2/2](https://www.upload.ee/image/16969203/2024-08-14_16-01.png)

Warnings are underlined and you can choose to fix them by right clicking:
![How to fix 1/2](https://www.upload.ee/image/16969207/2024-08-14_16-03.png)

Choose the "PHP Code Beautifier and Fixer: fix the whole file"
![How to fix 2/2](https://www.upload.ee/image/16969210/2024-08-14_16-04.png)
