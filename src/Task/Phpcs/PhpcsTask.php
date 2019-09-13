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
      self::DEF => self::IGNORE_PATTERNS,
      self::ALLOWED_TYPES => ['array'],
    ],
    'extensions' => [
      self::DEF => self::EXTENSIONS,
      self::ALLOWED_TYPES => ['array'],
    ],
    'run_on' => [
      self::DEF => self::RUN_ON,
      self::ALLOWED_TYPES => ['array'],
    ],
    'standard' => [
      self::DEF => ['vendor/wunderio/code-quality/config/phpcs.xml', 'vendor/wunderio/code-quality/config/phpcs-security.xml'],
      self::ALLOWED_TYPES => ['array', 'string'],
    ],
    'tab_width' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'int'],
    ],
    'encoding' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'string'],
    ],
    'sniffs' => [
      self::DEF => [],
      self::ALLOWED_TYPES => ['array'],
    ],
    'severity' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'int'],
    ],
    'error_severity' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'int'],
    ],
    'warning_severity' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'int'],
    ],
    'report' => [
      self::DEF => 'full',
      self::ALLOWED_TYPES => ['null', 'string'],
    ],
    'report_width' => [
      self::DEF => 120,
      self::ALLOWED_TYPES => ['null', 'int'],
    ],
    'exclude' => [
      self::DEF => [],
      self::ALLOWED_TYPES => ['array'],
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
  public function addArgumentsFromConfig(
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
