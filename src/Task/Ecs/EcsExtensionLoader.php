<?php

namespace Wunderio\GrumPHP\Task\Ecs;

use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;

/**
 * Class EcsExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\Ecs
 */
class EcsExtensionLoader extends ExternalExtensionLoaderBase {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [
    'name' => 'ecs',
    'class' => EcsTask::class,
    'arguments' => ['config', 'process_builder', 'formatter.raw_process'],
  ];

}
