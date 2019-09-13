<?php

namespace Wunderio\GrumPHP\Task\Phpcs;

use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpcsExtensionLoader extends ExternalExtensionLoaderBase {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [
    'name' => 'phpcs',
    'class' => PhpcsTask::class,
    'arguments' => ['config', 'process_builder', 'formatter.phpcs'],
  ];

}
