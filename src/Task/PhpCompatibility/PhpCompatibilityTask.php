<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\PhpCompatibility;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class PhpCompatibilityTask.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpCompatibilityTask extends ContextFileExternalTaskBase {

  /**
   * Option Extensions.
   *
   * @var string
   */
  public const D_TVERS = 'testVersion';

  /**
   * Name.
   *
   * @var string
   */
  public $name = 'php_compatibility';

  /**
   * Configurable options.
   *
   * @var array[]
   */
  public $configurableOptions = [
    self::D_IGN => [
      self::DEFAULTS => self::IGNORE_PATTERNS,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_EXT => [
      self::DEFAULTS => self::EXTENSIONS,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_RUN => [
      self::DEFAULTS => self::RUN_ON,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_TVERS => [
      self::DEFAULTS => '7.3',
      self::ALLOWED_TYPES => self::TYPE_STRING,
    ],
    'standard' => [
      self::DEFAULTS => 'vendor/wunderio/code-quality/config/php-compatibility.xml',
      self::ALLOWED_TYPES => self::TYPE_STRING,
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $conf = $this->getConfiguration();
    $arguments = $this->addArgumentsFromConfig($arguments, $this->getConfiguration());
    $arguments->add('--standard=' . $conf['standard']);

    foreach ($files as $file) {
      $arguments->add($file);
    }

    return $arguments;
  }

  /**
   * Adds arguments from configuration.
   *
   * @param \GrumPHP\Collection\ProcessArgumentsCollection $arguments
   *   Current arguments.
   * @param array $config
   *   Configuration.
   *
   * @return \GrumPHP\Collection\ProcessArgumentsCollection
   *   Modified arguments.
   */
  public function addArgumentsFromConfig(ProcessArgumentsCollection $arguments, array $config): ProcessArgumentsCollection {
    $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config[self::D_EXT]);
    $arguments->addSeparatedArgumentArray('--runtime-set', [self::D_TVERS, (string) $config[self::D_TVERS]]);
    return $arguments;
  }

}
