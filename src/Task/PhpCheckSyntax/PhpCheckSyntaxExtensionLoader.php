<?php

namespace Wunderio\GrumPHP\Task\PhpCheckSyntax;

use Wunderio\GrumPHP\Task\ExternalExtensionLoaderBase;

/**
 * Class PhpCheckSyntaxExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCheckSyntaxTask
 */
class PhpCheckSyntaxExtensionLoader extends ExternalExtensionLoaderBase {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [
    'name' => 'php_check_syntax',
    'class' => PhpCheckSyntaxTask::class,
    'arguments' => ['config', 'process_builder', 'formatter.raw_process'],
  ];

}
