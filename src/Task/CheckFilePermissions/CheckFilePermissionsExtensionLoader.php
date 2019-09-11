<?php

namespace Wunderio\GrumPHP\Task\CheckFilePermissions;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CheckFilePermissionsExtensionLoader.
 *
 * @package Wunderio\GrumPHP\CheckFilePermissions
 */
class CheckFilePermissionsExtensionLoader implements ExtensionInterface
{
  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container)
  {
  return $container->register('task.check_file_permissions', CheckFilePermissionsTask::class)
    ->addArgument(new Reference('config'))
    ->addArgument(new Reference('process_builder'))
    ->addArgument(new Reference('formatter.raw_process'))
    ->addTag('grumphp.task', ['config' => 'check_file_permissions']);
  }
}
