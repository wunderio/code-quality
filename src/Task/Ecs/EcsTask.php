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
    'clear-cache' => [
      self::DEF => FALSE,
      self::ALLOWED_TYPES => ['bool'],
    ],
    'no-progress-bar' => [
      self::DEF => TRUE,
      self::ALLOWED_TYPES => ['bool'],
    ],
    'config' => [
      self::DEF => 'vendor/wunderio/code-quality/config/ecs.yml',
      self::ALLOWED_TYPES => ['null', 'string'],
    ],
    'level' => [
      self::DEF => NULL,
      self::ALLOWED_TYPES => ['null', 'string'],
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
