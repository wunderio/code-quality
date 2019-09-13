<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Wunderio\GrumPHP\Task\ContextFileExternalTaskBase;

/**
 * Class CheckFilePermissionsTask.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsTask extends ContextFileExternalTaskBase {

  /**
   * Name.
   *
   * @var string
   */
  public $name = 'check_file_permissions';

  /**
   * File separation.
   *
   * @var bool
   */
  public $isFileSpecific = TRUE;

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
      self::DEF => ['sh'],
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
    self::D_RUN => [
      self::DEF => ['.'],
      self::ALLOWED_TYPES => [self::TYPE_ARRAY],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function buildArguments(iterable $files): ProcessArgumentsCollection {
    /** @var \GrumPHP\Collection\FilesCollection $files */
    $arguments = $this->processBuilder->createArgumentsForCommand('check_perms');
    $arguments->addFiles($files);

    return $arguments;
  }

}
