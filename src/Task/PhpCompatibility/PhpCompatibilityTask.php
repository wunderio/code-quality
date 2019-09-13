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
    'testVersion' => [
      'defaults' => '7.3',
      'allowed_types' => 'string',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
    $arguments = $this->addArgumentsFromConfig($arguments, $this->getConfiguration());
    $arguments->add('--standard=vendor/wunderio/code-quality/php-compatibility.xml');

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
  protected function addArgumentsFromConfig(ProcessArgumentsCollection $arguments, array $config): ProcessArgumentsCollection {
    $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config['extensions']);
    $arguments->addSeparatedArgumentArray('--runtime-set', ['testVersion', (string) $config['testVersion']]);
    return $arguments;
  }

}
