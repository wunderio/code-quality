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
    'ignore_patterns' => [
      'defaults' => self::IGNORE_PATTERNS,
      'allowed_types' => ['array'],
    ],
    'extensions' => [
      'defaults' => ['sh'],
      'allowed_types' => ['array'],
    ],
    'run_on' => [
      'defaults' => ['.'],
      'allowed_types' => ['array'],
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
