<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\Ecs;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class EcsTask.
 *
 * @package Wunderio\GrumPHP\Task\Ecs
 */
class EcsTask extends ContextFileExternalTaskBase {

  /**
   * Name.
   *
   * @var string
   */
  public $name = 'ecs';

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
    'clear-cache' => [
      'defaults' => FALSE,
      'allowed_types' => ['bool'],
    ],
    'no-progress-bar' => [
      'defaults' => TRUE,
      'allowed_types' => ['bool'],
    ],
    'config' => [
      'defaults' => 'vendor/wunderio/code-quality/ecs.yml',
      'allowed_types' => ['null', 'string'],
    ],
    'level' => [
      'defaults' => NULL,
      'allowed_types' => ['null', 'string'],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    $arguments = $this->processBuilder->createArgumentsForCommand('ecs');
    $arguments->add('check');

    foreach ($files as $file) {
      $arguments->add($file);
    }

    $config = $this->getConfiguration();
    $arguments->addOptionalArgument('--config=%s', $config['config']);
    $arguments->addOptionalArgument('--level=%s', $config['level']);
    $arguments->addOptionalArgument('--clear-cache', $config['clear-cache']);
    $arguments->addOptionalArgument('--no-progress-bar', $config['no-progress-bar']);
    $arguments->addOptionalArgument('--ansi', TRUE);
    $arguments->addOptionalArgument('--no-interaction', TRUE);

    return $arguments;
  }

}
