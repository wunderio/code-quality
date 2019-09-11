<?php

namespace Wunderio\GrumPHP\Task\Ecs;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EcsExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\Ecs
 */
class EcsExtensionLoader implements ExtensionInterface {

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container) {
    return $container->register('task.ecs', EcsTask::class)
      ->addArgument(new Reference('config'))
      ->addArgument(new Reference('process_builder'))
      ->addArgument(new Reference('formatter.raw_process'))
      ->addTag('grumphp.task', ['config' => 'ecs']);
  }

}
