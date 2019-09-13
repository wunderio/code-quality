<?php

declare(strict_types = 1);

namespace Wunderio\GrumPHP\Task;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
abstract class ExternalExtensionLoaderBase implements ExtensionInterface {

  /**
   * Task info.
   *
   * @var array
   */
  public $taskInfo = [];

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container): Definition {
    $task = $container->register('task.' . $this->taskInfo['name'], $this->taskInfo['class']);
    $task->addTag('grumphp.task', ['config' => $this->taskInfo['name']]);
    if (empty($this->taskInfo['arguments'])) {
      return $task;
    }
    foreach ($this->taskInfo['arguments'] as $argument) {
      $task->addArgument(new Reference($argument));
    }


    return $task;
  }

}
