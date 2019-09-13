<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\Phpcs;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class PhpCompatibilityTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpcsTask extends ContextFileExternalTaskBase {

  /**
   * Name.
   *
   * @var string
   */
  public $name = 'phpcs';

  /**
   * Configurable options.
   *
   * @var array[]
   */
  public $configurableOptions = [
    'ignore_patterns' => [
      'defaults' => self::IGNORE_PATTERNS,
      'allowed_types' => ['array'],
    ],
    'extensions' => [
      'defaults' => self::EXTENSIONS,
      'allowed_types' => ['array'],
    ],
    'run_on' => [
      'defaults' => self::RUN_ON,
      'allowed_types' => ['array'],
    ],
    'standard' => [
      'defaults' => ['vendor/wunderio/code-quality/phpcs.xml', 'vendor/wunderio/code-quality/phpcs-security.xml'],
      'allowed_types' => ['array']['array'],
    ],
    'tab_width' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'int'],
    ],
    'encoding' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'string'],
    ],
    'sniffs' => [
      'defaults' => [],
      'allowed_types' => ['array'],
    ],
    'severity' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'int'],
    ],
    'error_severity' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'int'],
    ],
    'warning_severity' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'int'],
    ],
    'report' => [
      'defaults' => 'full',
      'allowed_types' => ['null', 'string'],
    ],
    'report_width' => [
      'defaults' => 120,
      'allowed_types' => ['null', 'int'],
    ],
    'exclude' => [
      'defaults' => [],
      'allowed_types' => ['array'],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $arguments = $this->addArgumentsFromConfig($arguments, $this->getConfiguration());
    $arguments->add('--report-json');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function addArgumentsFromConfig(
    ProcessArgumentsCollection $arguments,
    array $config
  ): ProcessArgumentsCollection {
    $arguments->addOptionalCommaSeparatedArgument('--standard=%s', (array) $config['standard']);
    $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
    $arguments->addOptionalArgument('--encoding=%s', $config['encoding']);
    $arguments->addOptionalArgument('--report=%s', $config['report']);
    $arguments->addOptionalIntegerArgument('--report-width=%s', $config['report_width']);
    $arguments->addOptionalIntegerArgument('--severity=%s', $config['severity']);
    $arguments->addOptionalIntegerArgument('--error-severity=%s', $config['error_severity']);
    $arguments->addOptionalIntegerArgument('--warning-severity=%s', $config['warning_severity']);
    $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
    $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
    $arguments->addOptionalCommaSeparatedArgument('--exclude=%s', $config['exclude']);

    return $arguments;
  }

}
