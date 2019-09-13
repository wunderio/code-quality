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
      self::DEF => self::IGNORE_PATTERNS,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_EXT => [
      self::DEF => self::EXTENSIONS,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_RUN => [
      self::DEF => self::RUN_ON,
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    'testVersion' => [
      self::DEF => '7.3',
      self::ALLOWED_TYPES => 'string',
    ],
    'standard' => [
      self::DEF => 'vendor/wunderio/code-quality/config/php-compatibility.xml',
      self::ALLOWED_TYPES => 'string',
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
    $arguments->addSeparatedArgumentArray('--runtime-set', ['testVersion', (string) $config['testVersion']]);
    return $arguments;
  }

}
