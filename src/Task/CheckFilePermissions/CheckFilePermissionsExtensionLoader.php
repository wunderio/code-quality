<?php

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;

/**
 * Class CheckFilePermissionsExtensionLoader.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsExtensionLoader extends ExternalExtensionLoaderBase {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [
    'name' => 'check_file_permissions',
    'class' => CheckFilePermissionsTask::class,
    'arguments' => ['config', 'process_builder', 'formatter.raw_process'],
  ];

}
