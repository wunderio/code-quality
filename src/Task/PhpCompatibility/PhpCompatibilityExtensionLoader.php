<?php

namespace Wunderio\GrumPHP\Task\PhpCompatibility;

use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpCompatibilityExtensionLoader extends ExternalExtensionLoaderBase {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [
    'name' => 'php_compatibility',
    'class' => PhpCompatibilityTask::class,
    'arguments' => ['config', 'process_builder', 'formatter.raw_process'],
  ];

}
