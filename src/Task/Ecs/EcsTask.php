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
    'clear-cache' => [
      self::DEFAULTS => FALSE,
      self::ALLOWED_TYPES => ['bool'],
    ],
    'no-progress-bar' => [
      self::DEFAULTS => TRUE,
      self::ALLOWED_TYPES => ['bool'],
    ],
    'config' => [
      self::DEFAULTS => 'vendor/wunderio/code-quality/config/ecs.yml',
      self::ALLOWED_TYPES => ['null', self::TYPE_STRING],
    ],
    'level' => [
      self::DEFAULTS => NULL,
      self::ALLOWED_TYPES => ['null', self::TYPE_STRING],
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
