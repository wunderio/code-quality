<?php

namespace Wunderio\GrumPHP\Task\Phpcs;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Wunderio\GrumPHP\Task\PhpCompatibility\PhpcsTask;

/**
 * Class PhpCompatibilityExtensionLoader.
 *
 * @package Wunderio\GrumPHP\Task\PhpCompatibilityTask
 */
class PhpcsExtensionLoader implements ExtensionInterface
{
  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container)
  {
    return $container->register('task.phpcs', PhpcsTask::class)
      ->addArgument(new Reference('config'))
      ->addArgument(new Reference('process_builder'))
      ->addArgument(new Reference('formatter.raw_process'))
      ->addTag('grumphp.task', ['config' => 'phpcs']);
  }
}
